DROP TRIGGER artifact_update_last_modified_date ON artifact;

ALTER TABLE artifact
   ADD COLUMN last_modified_by integer NOT NULL DEFAULT 100,
   ADD COLUMN is_deleted integer NOT NULL DEFAULT 0,
   ADD CONSTRAINT artifact_last_modified_by_fk FOREIGN KEY (last_modified_by)
      REFERENCES users (user_id) MATCH FULL
      ON UPDATE NO ACTION ON DELETE NO ACTION;

CREATE OR REPLACE VIEW artifact_vw AS 
 SELECT
    artifact.artifact_id,
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
    artifact.last_modified_date,
    artifact.last_modified_by,
    u3.user_name AS last_modified_unixname,
    u3.realname AS last_modified_realname,
    u3.email AS last_modified_email
   FROM
    artifact 
    INNER JOIN users AS u ON (artifact.assigned_to = u.user_id)
    INNER JOIN users AS u2 ON (artifact.submitted_by = u2.user_id)
    INNER JOIN users AS u3 ON (artifact.last_modified_by = u3.user_id)
    INNER JOIN artifact_status ON (artifact.status_id = artifact_status.id)
  WHERE
    artifact.is_deleted = 0;
