--
--	Change project_task to delete on removal of project
--
ALTER TABLE project_task DROP CONSTRAINT "project_task_group_project_id_f" RESTRICT;

ALTER TABLE project_task 
	ADD CONSTRAINT projecttask_groupprojectid_fk 
	REFERENCES project_group_list(group_project_id) ON DELETE CASCADE;

--
--	Add email address to send all task updates to
--
ALTER TABLE project_group_list ADD COLUMN send_all_posts_to text;

--
--	Add category_id
--
ALTER TABLE project_task ADD COLUMN category_id int REFERENCES project_category(category_id);

--
--	Convenience view required for ProjectTask object
--
CREATE VIEW project_task_vw AS 
SELECT project_task.*,project_category.category_name,project_status.status_name 
FROM project_task 
FULL JOIN project_category ON (project_category.category_id=project_task.category_id) 
NATURAL JOIN project_status;

--
--	Each task can be assigned a category
--
DROP TABLE project_category;
DROP SEQUENCE project_categor_category_id_seq;
CREATE TABLE project_category (
category_id serial,
group_project_id int 
	CONSTRAINT projcat_projgroupid_fk REFERENCES project_group_list(group_project_id) ON DELETE CASCADE,
category_name text);
CREATE INDEX projectcategory_groupprojectid ON project_category(group_project_id);
INSERT INTO project_category VALUES ('100','1','None');
SELECT SETVAL('project_categor_category_id_seq',100);

--
--	Each task can have multiple artifacts associated with it
--
DROP TABLE project_task_artifact;
CREATE TABLE project_task_artifact (
project_task_id int 
	CONSTRAINT projtaskartifact_projtaskid_fk REFERENCES project_task(project_task_id) ON DELETE CASCADE,
artifact_id int 
	CONSTRAINT projtaskartifact_artifactid_fk REFERENCES artifact(artifact_id) ON DELETE CASCADE);
CREATE INDEX projecttaskartifact_projecttaskid ON project_task_artifact (project_task_id);
CREATE INDEX projecttaskartifact_artifactid ON project_task_artifact (artifact_id);

--
--	Relation to forums dedicated to this project
--
DROP TABLE project_group_forum;
CREATE TABLE project_group_forum (
group_project_id int 
	CONSTRAINT projgroupforum_projgroupid_fk REFERENCES project_group_list(group_project_id) ON DELETE CASCADE,
group_forum_id int 
	CONSTRAINT projgroupforum_groupforumid_fk REFERENCES forum_group_list(group_forum_id) ON DELETE CASCADE);
CREATE INDEX projectgroupforum_groupprojectid ON project_group_forum(group_project_id);
CREATE INDEX projectgroupforum_groupforumid ON project_group_forum(group_forum_id);

--
--	Relation to a category of docs for this project
--
DROP TABLE project_group_doccat;
CREATE TABLE project_group_doccat (
group_project_id int 
	CONSTRAINT projgroupdoccat_projgroupid_fk REFERENCES project_group_list(group_project_id) ON DELETE CASCADE,
doc_group_id int 
	CONSTRAINT projgroupdoccat_docgroupid_fk REFERENCES doc_groups(doc_group) ON DELETE CASCADE);
CREATE INDEX projectgroupdoccat_groupprojectid ON project_group_forum(group_project_id);
CREATE INDEX projectgroupdoccat_groupgroupid ON project_group_doccat(doc_group_id);

--
--
--
DROP VIEW project_depend_vw;
CREATE VIEW project_depend_vw AS 
	SELECT pt.project_task_id,pd.is_dependent_on_task_id,pt.end_date,pt.start_date
	FROM project_task pt NATURAL JOIN project_dependencies pd;

DROP VIEW project_dependon_vw;
CREATE VIEW project_dependon_vw AS 
	SELECT pd.project_task_id,pd.is_dependent_on_task_id,pt.end_date,pt.start_date
	FROM project_task pt FULL JOIN project_dependencies pd ON (pd.is_dependent_on_task_id=pt.project_task_id);

--
--	Remove all existing dependencies, as they may be problematic.
--
DELETE FROM project_dependencies;

--
--	Function to enforce dependencies in the table structure
--

CREATE OR REPLACE FUNCTION projtask_update_depend () RETURNS OPAQUE AS '
DECLARE
	dependent RECORD;
	dependon RECORD;
	delta	INTEGER;
BEGIN
	--
	--  See if tasks that are dependent on us are OK
	--  See if the end date has changed
	--
	IF NEW.end_date > OLD.end_date THEN
		--
		--  If the end date pushed back, push back dependent tasks
		--
		FOR dependent IN SELECT * FROM project_depend_vw WHERE is_dependent_on_task_id=NEW.project_task_id LOOP
			--
			--  Some dependent tasks may not start immediately
			--
			IF dependent.start_date > OLD.end_date THEN
				IF dependent.start_date < NEW.end_date THEN
					delta := NEW.end_date-dependent.start_date;
					UPDATE project_task
						SET start_date=start_date+delta,
						end_date=end_date+delta
						WHERE project_task_id=dependent.project_task_id;
				END IF;
			ELSE
				IF dependent.start_date = OLD.end_date THEN
					delta := NEW.end_date-OLD.end_date;
					UPDATE project_task
						SET start_date=start_date+delta,
						end_date=end_date+delta
						WHERE project_task_id=dependent.project_task_id;
				END IF;
			END IF;
		END LOOP;
	ELSIF NEW.end_date < OLD.end_date THEN
			--
			--	If the end date moved up, move up dependent tasks
			--
			FOR dependent IN SELECT * FROM project_depend_vw WHERE is_dependent_on_task_id=NEW.project_task_id LOOP
				IF dependent.start_date = OLD.end_date THEN
					--
					--  dependent task was constrained by us - bring it forward
					--
					delta := OLD.end_date-NEW.end_date;
					UPDATE project_task
						SET start_date=start_date-delta,
						end_date=end_date-delta
						WHERE project_task_id=dependent.project_task_id;
				END IF;
			END LOOP;
	END IF;
--
--	MAY WISH TO INSERT AUDIT TRAIL HERE FOR CHANGED begin/end DATES
--
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';


DROP TRIGGER projtask_update_depend_trig ON project_task;
CREATE TRIGGER projtask_update_depend_trig AFTER UPDATE ON project_task
	FOR EACH ROW EXECUTE PROCEDURE projtask_update_depend();


--
--	  Function to enforce dependencies in the table structure
--
CREATE OR REPLACE FUNCTION projtask_insert_depend () RETURNS OPAQUE AS '
DECLARE
	dependon RECORD;
	delta INTEGER;
BEGIN
	--
	--  ENFORCE START/END DATE logic
	--
	IF NEW.start_date >= NEW.end_date THEN
		RAISE EXCEPTION ''START DATE CANNOT BE AFTER END DATE'';
	END IF;
	--
	--	  First make sure we start on or after end_date of tasks
	--	  that we depend on
	--
	FOR dependon IN SELECT * FROM project_dependon_vw
				WHERE project_task_id=NEW.project_task_id LOOP
		--
		--	  See if the task we are dependon on
		--	  ends after we are supposed to start
		--
		IF dependon.end_date > NEW.start_date THEN
			delta := dependon.end_date-NEW.start_date;
			RAISE NOTICE ''Bumping Back: % Delta: % '',NEW.project_task_id,delta;
			NEW.start_date := NEW.start_date+delta;
			NEW.end_date := NEW.end_date+delta;
		END IF;

	END LOOP;
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';

DROP TRIGGER projtask_insert_depend_trig ON project_task;
CREATE TRIGGER projtask_insert_depend_trig BEFORE INSERT OR UPDATE ON project_task
	FOR EACH ROW EXECUTE PROCEDURE projtask_insert_depend();

