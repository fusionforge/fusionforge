DROP TABLE artifact_category CASCADE;
DROP TABLE artifact_group CASCADE;
DROP TABLE artifact_resolution CASCADE;
ALTER TABLE artifact DROP COLUMN artifact_group_id;
ALTER TABLE artifact DROP COLUMN resolution_id;
ALTER TABLE artifact DROP COLUMN category_id;
ALTER TABLE artifact_group_list DROP COLUMN use_resolution CASCADE;

CREATE VIEW artifact_group_list_vw AS
SELECT agl.*,aca.count,aca.open_count
        FROM artifact_group_list agl
        LEFT JOIN artifact_counts_agg aca USING (group_artifact_id);

CREATE VIEW artifact_vw
AS
SELECT  artifact.artifact_id,
        artifact.group_artifact_id,
        artifact.status_id,
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
        artifact.last_modified_date
FROM    users u,
        users u2,
        artifact_status,
        artifact
WHERE   artifact.assigned_to = u.user_id
AND     artifact.submitted_by = u2.user_id
AND     artifact.status_id = artifact_status.id;

DELETE FROM user_preferences WHERE preference_name LIKE 'art_cust%';
