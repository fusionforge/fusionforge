ALTER TABLE project_task ADD COLUMN duration int;
ALTER TABLE project_task ALTER COLUMN duration SET DEFAULT 0;
UPDATE project_task SET duration=0;
ALTER TABLE project_task ADD COLUMN parent_id int;
ALTER TABLE project_task ALTER COLUMN parent_id SET DEFAULT 0;
UPDATE project_task SET parent_id=0;

DROP VIEW project_task_vw;

CREATE VIEW project_task_vw AS
SELECT project_task.*,project_category.category_name,project_status.status_name,users.user_name,users.realname
FROM project_task
FULL JOIN project_category ON (project_category.category_id=project_task.category_id)
FULL JOIN users ON (users.user_id=project_task.created_by)
NATURAL JOIN project_status;

ALTER TABLE project_dependencies ADD COLUMN link_type char(2);
ALTER TABLE project_dependencies ALTER COLUMN link_type SET DEFAULT 'SS';
UPDATE project_dependencies SET link_type='SS';

DROP VIEW project_dependon_vw;
DROP VIeW project_depend_vw;

CREATE VIEW project_depend_vw AS
        SELECT pt.project_task_id,pd.is_dependent_on_task_id,pd.link_type,pt.end_date,pt.start_date
        FROM project_task pt NATURAL JOIN project_dependencies pd;

CREATE VIEW project_dependon_vw AS
        SELECT pd.project_task_id,pd.is_dependent_on_task_id,pd.link_type,pt.end_date,pt.start_date
        FROM project_task pt FULL JOIN project_dependencies pd ON
        (pd.is_dependent_on_task_id=pt.project_task_id);

