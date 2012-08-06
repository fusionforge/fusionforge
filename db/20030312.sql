--
--	  Function to enforce consistency in task start/end dates
--
DROP FUNCTION projtask_insert_depend () CASCADE ;
CREATE OR REPLACE FUNCTION projtask_insert_depend () RETURNS OPAQUE AS '
DECLARE
	dependon RECORD;
	delta INTEGER;
BEGIN
	--
	--  ENFORCE START/END DATE logic
	--
	IF NEW.start_date > NEW.end_date THEN
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

--
-- Re-create the trigger
--
CREATE TRIGGER projtask_insert_depend_trig BEFORE INSERT OR UPDATE ON project_task
	FOR EACH ROW EXECUTE PROCEDURE projtask_insert_depend();
