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
        coalesce(max(artifact_history.entrydate), artifact.open_date) AS update_date,
        coalesce(max(artifact_message.adddate), artifact.open_date) AS message_date
FROM    users u,
        users u2,
        artifact_status,
        artifact_category,
        artifact_group,
        artifact_resolution,
        artifact
        LEFT JOIN   artifact_history
                    on  artifact.artifact_id = artifact_history.artifact_id
        LEFT JOIN   artifact_message
                    on  artifact.artifact_id = artifact_message.artifact_id
WHERE   artifact.assigned_to = u.user_id
AND     artifact.submitted_by = u2.user_id
AND     artifact.status_id = artifact_status.id
AND     artifact.category_id = artifact_category.id
AND     artifact.artifact_group_id = artifact_group.id
AND     artifact.resolution_id = artifact_resolution.id
GROUP BY
        artifact.artifact_id,
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
        assigned_unixname,
        assigned_realname,
        assigned_email,
        submitted_unixname,
        submitted_realname,
        submitted_email,
        artifact_status.status_name,
        artifact_category.category_name,
        artifact_group.group_name,
        artifact_resolution.resolution_name;
