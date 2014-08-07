--
-- Adding users.realname and users.user_name to project_task_vw
--
DROP VIEW project_task_vw;

CREATE VIEW project_task_vw AS
SELECT project_task.*,project_category.category_name,project_status.status_name,users.user_name,users.realname
FROM project_task
FULL JOIN project_category ON (project_category.category_id=project_task.category_id)
FULL JOIN users ON (users.user_id=project_task.created_by)
NATURAL JOIN project_status;

