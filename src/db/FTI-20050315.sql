SET client_min_messages TO warning;

BEGIN;

DROP FUNCTION update_vectors() CASCADE;

CREATE OR REPLACE FUNCTION update_vectors() RETURNS TRIGGER AS '
DECLARE
table_name TEXT;
BEGIN
	table_name := TG_ARGV[0];
	-- **** artifact table ****
	IF table_name = ''artifact'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO artifact_idx (artifact_id, group_artifact_id, vectors) VALUES (NEW.artifact_id, NEW.group_artifact_id, to_tsvector(coalesce(NEW.details,\'\') ||\' \'|| coalesce(NEW.summary,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE artifact_idx SET group_artifact_id=NEW.group_artifact_id, vectors=to_tsvector(coalesce(NEW.details,\'\') ||\' \'|| coalesce(NEW.summary,\'\')) WHERE artifact_id=NEW.artifact_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM artifact_idx WHERE artifact_id=OLD.artifact_id;
		END IF;
	-- **** artifact_message table ****
	ELSIF table_name = ''artifact_message'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO artifact_message_idx (id, artifact_id, vectors) VALUES (NEW.id, NEW.artifact_id, to_tsvector(coalesce(NEW.body,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE artifact_message_idx SET artifact_id=NEW.artifact_id, vectors=to_tsvector(coalesce(NEW.body,\'\')) WHERE id=NEW.id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM artifact_message_idx WHERE id=OLD.id;
		END IF;
	-- **** doc_data table ****
	ELSIF table_name = ''doc_data'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO doc_data_idx (docid, group_id, vectors) VALUES (NEW.docid, NEW.group_id, to_tsvector(coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.description,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE doc_data_idx SET group_id=NEW.group_id, vectors=to_tsvector(coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.description,\'\')) WHERE docid=NEW.docid;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM doc_data_idx WHERE docid=OLD.docid;
		END IF;
	-- **** forum table ****
	ELSIF table_name = ''forum'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO forum_idx (msg_id, group_id, vectors) (SELECT f.msg_id, g.group_id, to_tsvector(coalesce(f.subject,\'\') ||\' \'|| 
			coalesce(f.body,\'\')) AS vectors FROM forum f, forum_group_list g WHERE f.group_forum_id = g.group_forum_id AND f.msg_id = NEW.msg_id);
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE forum_idx SET vectors=to_tsvector(coalesce(NEW.subject,\'\') ||\' \'|| coalesce(NEW.body,\'\')) WHERE msg_id=NEW.msg_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM forum_idx WHERE msg_id=OLD.msg_id;
		END IF;
	-- **** frs_file table ****
	ELSIF table_name = ''frs_file'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO frs_file_idx (file_id, release_id, vectors) VALUES (NEW.file_id, NEW.release_id, to_tsvector(coalesce(NEW.filename,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE frs_file_idx SET vectors=to_tsvector(coalesce(NEW.filename,\'\')), release_id=NEW.release_id WHERE file_id=NEW.file_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM frs_file_idx WHERE file_id=OLD.file_id;
		END IF;
	-- **** frs_release table ****
	ELSIF table_name = ''frs_release'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO frs_release_idx (release_id, vectors) VALUES (NEW.release_id, to_tsvector(coalesce(changes,\'\') ||\' \'|| coalesce(notes,\'\') ||\' \'|| coalesce(name,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE frs_release_idx SET vectors=to_tsvector(coalesce(changes,\'\') ||\' \'|| coalesce(notes,\'\') ||\' \'|| coalesce(name,\'\')) WHERE release_id=NEW.release_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM frs_release_idx WHERE release_id=OLD.release_id;
			DELETE FROM frs_file_idx WHERE release_id=OLD.release_id;
		END IF;
	-- **** groups table ****
	ELSIF table_name = ''groups'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO groups_idx (group_id, vectors) VALUES (NEW.group_id, to_tsvector(coalesce(NEW.group_name,\'\') ||\' \'|| coalesce(NEW.short_description,\'\') ||\' \'|| coalesce(NEW.unix_group_name,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE groups_idx SET vectors=to_tsvector(coalesce(NEW.group_name,\'\') ||\' \'|| coalesce(NEW.short_description,\'\') ||\' \'|| coalesce(NEW.unix_group_name,\'\')) WHERE group_id=NEW.group_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM groups_idx WHERE group_id=OLD.group_id;
		END IF;
	-- **** news_bytes table ****
	ELSIF table_name = ''news_bytes'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO news_bytes_idx (id, vectors) VALUES (NEW.id, to_tsvector(coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE news_bytes_idx SET vectors=to_tsvector(coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')) WHERE id=NEW.id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM news_bytes_idx WHERE id=OLD.id;
		END IF;
	-- **** project_task table ****
	ELSIF table_name = ''project_task'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO project_task_idx (project_task_id, vectors) VALUES (NEW.project_task_id, to_tsvector(coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE project_task_idx SET vectors=to_tsvector(coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')) WHERE project_task_id=NEW.project_task_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM project_task_idx WHERE project_task_id=OLD.project_task_id;
		END IF;
	-- **** skills_data table ****
	ELSIF table_name = ''skills_data'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO skills_data_idx (skills_data_id, vectors) VALUES (NEW.skill_data_id, to_tsvector(coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.keywords,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE skills_data_idx SET vectors=to_tsvector(coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.keywords,\'\')) WHERE skills_data_id=NEW.skills_data_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM skills_data_idx WHERE skills_data_id=OLD.skills_data_id;
		END IF;
	-- **** users table ****
	ELSIF table_name = ''users'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO users_idx (user_id, vectors) VALUES (NEW.user_id, to_tsvector(coalesce(NEW.user_name,\'\') ||\' \'|| coalesce(NEW.realname,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE users_idx SET vectors=to_tsvector(coalesce(NEW.user_name,\'\') ||\' \'|| coalesce(NEW.realname,\'\')) WHERE user_id=NEW.user_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM users_idx WHERE user_id=OLD.user_id;
		END IF;
	END IF;

	RETURN NEW;
END;'
LANGUAGE 'plpgsql';

-- ********** Set up triggers ********** 

CREATE TRIGGER artifact_ts_update AFTER UPDATE OR INSERT OR DELETE ON artifact
FOR EACH ROW EXECUTE PROCEDURE update_vectors('artifact');

CREATE TRIGGER doc_data_ts_update AFTER UPDATE OR INSERT OR DELETE ON doc_data
FOR EACH ROW EXECUTE PROCEDURE update_vectors('doc_data');

CREATE TRIGGER forum_update AFTER UPDATE OR INSERT OR DELETE ON forum
FOR EACH ROW EXECUTE PROCEDURE update_vectors('forum');

CREATE TRIGGER frs_file_ts_update AFTER UPDATE OR INSERT OR DELETE ON frs_file
FOR EACH ROW EXECUTE PROCEDURE update_vectors('frs_file');

CREATE TRIGGER frs_release_ts_update AFTER UPDATE OR INSERT OR DELETE ON frs_release
FOR EACH ROW EXECUTE PROCEDURE update_vectors('frs_release');

CREATE TRIGGER groups_ts_update AFTER UPDATE OR INSERT OR DELETE ON groups
FOR EACH ROW EXECUTE PROCEDURE update_vectors('groups');

CREATE TRIGGER news_bytes_ts_update AFTER UPDATE OR INSERT OR DELETE ON news_bytes
FOR EACH ROW EXECUTE PROCEDURE update_vectors('news_bytes');

CREATE TRIGGER project_task_ts_update AFTER UPDATE OR INSERT OR DELETE ON project_task
FOR EACH ROW EXECUTE PROCEDURE update_vectors('project_task');

CREATE TRIGGER skills_data_ts_update AFTER UPDATE OR INSERT OR DELETE ON skills_data
FOR EACH ROW EXECUTE PROCEDURE update_vectors('skills_data');

CREATE TRIGGER users_ts_update AFTER UPDATE OR INSERT OR DELETE ON users
FOR EACH ROW EXECUTE PROCEDURE update_vectors('users');

COMMIT;
