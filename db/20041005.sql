DELETE FROM project_counts_agg;
INSERT INTO project_counts_agg
        SELECT group_project_id,
        (SELECT count(*) FROM project_task WHERE status_id != 3 AND
                project_task.group_project_id=project_group_list.group_project_id),
        (SELECT count(*) FROM project_task WHERE status_id = 1 AND
                project_task.group_project_id=project_group_list.group_project_id)
        FROM project_group_list;

CREATE RULE projecttask_insert_agg AS ON
INSERT TO project_task DO
UPDATE project_counts_agg
SET count = (project_counts_agg.count + 1),
open_count = (project_counts_agg.open_count + 1)
WHERE (project_counts_agg.group_project_id = new.group_project_id);

UPDATE artifact SET priority=1 WHERE priority=2;
UPDATE artifact SET priority=2 WHERE priority IN (3,4);
UPDATE artifact SET priority=3 WHERE priority IN (5,6);
UPDATE artifact SET priority=4 WHERE priority IN (7,8);
UPDATE artifact SET priority=5 WHERE priority=9;
UPDATE project_task SET priority=1 WHERE priority=2;
UPDATE project_task SET priority=2 WHERE priority IN (3,4);
UPDATE project_task SET priority=3 WHERE priority IN (5,6);
UPDATE project_task SET priority=4 WHERE priority IN (7,8);
UPDATE project_task SET priority=5 WHERE priority=9;
