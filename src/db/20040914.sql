CREATE TABLE project_counts_agg (
        group_project_id integer NOT NULL,
        count integer DEFAULT 0 NOT NULL,
        open_count integer DEFAULT 0
);

CREATE VIEW project_group_list_vw AS SELECT * FROM project_group_list NATURAL JOIN project_counts_agg;

INSERT INTO project_counts_agg
	SELECT group_project_id,
	(SELECT count(*) FROM project_task WHERE status_id != 3 AND
		project_task.group_project_id=project_group_list.group_project_id),
	(SELECT count(*) FROM project_task WHERE status_id = 1 AND
		project_task.group_project_id=project_group_list.group_project_id)
	FROM project_group_list;

CREATE FUNCTION projectgrouplist_insert_agg () RETURNS opaque AS '
BEGIN
    INSERT INTO project_counts_agg (group_project_id,count,open_count)
        VALUES (NEW.group_project_id,0,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';


CREATE FUNCTION projectgroup_update_agg () RETURNS opaque AS '
BEGIN
    --
    -- see if they are moving to a new subproject
    -- if so, its a more complex operation
    --
    IF NEW.group_project_id <> OLD.group_project_id THEN
        --
        -- transferred tasks always have a status of 1
        -- so we will increment the new subprojects sums
        --
        IF OLD.status_id=3 THEN
            -- No need to decrement counters on old tracker
        ELSE
            IF OLD.status_id=2 THEN
                UPDATE project_counts_agg SET count=count-1
                    WHERE group_project_id=OLD.group_project_id;
            ELSE
                IF OLD.status_id=1 THEN
                    UPDATE project_counts_agg SET count=count-1,open_count=open_count-1
                        WHERE group_project_id=OLD.group_project_id;
                END IF;
            END IF;
        END IF;

        IF NEW.status_id=3 THEN
            --DO NOTHING
        ELSE
            IF NEW.status_id=2 THEN
                    UPDATE project_counts_agg SET count=count+1
                        WHERE group_project_id=NEW.group_project_id;
            ELSE
                IF NEW.status_id=1 THEN
                    UPDATE project_counts_agg SET count=count+1, open_count=open_count+1
                        WHERE group_project_id=NEW.group_project_id;
                END IF;
            END IF;
        END IF;
    ELSE
        --
        -- just need to evaluate the status flag and
        -- increment/decrement the counter as necessary
        --
        IF NEW.status_id <> OLD.status_id THEN
            IF NEW.status_id = 1 THEN
                IF OLD.status_id=2 THEN
                    UPDATE project_counts_agg SET open_count=open_count+1
                        WHERE group_project_id=NEW.group_project_id;
                ELSE
                    IF OLD.status_id=3 THEN
                        UPDATE project_counts_agg SET open_count=open_count+1, count=count+1
                            WHERE group_project_id=NEW.group_project_id;
                    END IF;
                END IF;
            ELSE
                IF NEW.status_id = 2 THEN
                    IF OLD.status_id=1 THEN
                        UPDATE project_counts_agg SET open_count=open_count-1
                            WHERE group_project_id=NEW.group_project_id;
                    ELSE
                        IF OLD.status_id=3 THEN
                            UPDATE project_counts_agg SET count=count+1
                                WHERE group_project_id=NEW.group_project_id;
                        END IF;
                    END IF;
                ELSE
                    IF NEW.status_id = 3 THEN
                        IF OLD.status_id=2 THEN
                            UPDATE project_counts_agg SET count=count-1
                                WHERE group_project_id=NEW.group_project_id;
                        ELSE
                            IF OLD.status_id=1 THEN
                                UPDATE project_counts_agg SET open_count=open_count-1,count=count-1
                                    WHERE group_project_id=NEW.group_project_id;
                            END IF;
                        END IF;
                    END IF;
                END IF;
            END IF;
        END IF;
    END IF;
    RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER projectgrouplist_insert_trig AFTER INSERT ON project_group_list
FOR EACH ROW EXECUTE PROCEDURE projectgrouplist_insert_agg ();


CREATE TRIGGER projectgroup_update_trig AFTER UPDATE ON project_task
FOR EACH ROW EXECUTE PROCEDURE projectgroup_update_agg ();

