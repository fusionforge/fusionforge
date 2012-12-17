DROP VIEW project_task_vw;

CREATE VIEW project_task_vw AS
SELECT project_task.*, project_category.category_name, project_status.status_name, users.user_name, users.realname, project_task_external_order.external_id
FROM project_task
FULL JOIN project_category ON (project_category.category_id = project_task.category_id)
FULL JOIN users ON (users.user_id = project_task.created_by)
FULL JOIN project_task_external_order ON (project_task_external_order.project_task_id = project_task.project_task_id)
NATURAL JOIN project_status;
