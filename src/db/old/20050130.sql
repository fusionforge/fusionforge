ALTER TABLE artifact ADD COLUMN last_modified_date integer;
ALTER TABLE project_task ADD COLUMN last_modified_date integer;

update project_task SET last_modified_date=EXTRACT(EPOCH FROM now())::integer;
update artifact SET last_modified_date=EXTRACT(EPOCH FROM now())::integer;

CREATE FUNCTION "update_last_modified_date" () RETURNS OPAQUE AS '
BEGIN
NEW.last_modified_date = EXTRACT(EPOCH FROM now())::integer;
RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER artifact_update_last_modified_date BEFORE INSERT OR UPDATE ON artifact
FOR EACH ROW EXECUTE PROCEDURE update_last_modified_date();

CREATE TRIGGER project_task_update_last_modified_date BEFORE INSERT OR UPDATE ON project_task
FOR EACH ROW EXECUTE PROCEDURE update_last_modified_date();

DROP VIEW project_task_vw;

CREATE VIEW project_task_vw AS
SELECT project_task.*,project_category.category_name,project_status.status_name,users.user_name,users.realname
FROM project_task
FULL JOIN project_category ON (project_category.category_id=project_task.category_id)
FULL JOIN users ON (users.user_id=project_task.created_by)
NATURAL JOIN project_status;


DROP VIEW artifact_vw;

CREATE VIEW artifact_vw
AS
SELECT  artifact.artifact_id,
        artifact.group_artifact_id,
        artifact.status_id,
        artifact.category_id,
        artifact.artifact_group_id,
        artifact.resolution_id,
        artifact.priority,
        artifact.submitted_by,
        artifact.assigned_to,
        artifact.open_date,
        artifact.close_date,
        artifact.summary,
        artifact.details,
        u.user_name AS assigned_unixname,
        u.realname AS assigned_realname,
        u.email AS assigned_email,
        u2.user_name AS submitted_unixname,
        u2.realname AS submitted_realname,
        u2.email AS submitted_email,
        artifact_status.status_name,
        artifact_category.category_name,
        artifact_group.group_name,
        artifact_resolution.resolution_name,
        artifact.last_modified_date
FROM    users u,
        users u2,
        artifact_status,
        artifact_category,
        artifact_group,
        artifact_resolution,
        artifact
WHERE   artifact.assigned_to = u.user_id
AND     artifact.submitted_by = u2.user_id
AND     artifact.status_id = artifact_status.id
AND     artifact.category_id = artifact_category.id
AND     artifact.artifact_group_id = artifact_group.id
AND     artifact.resolution_id = artifact_resolution.id;
