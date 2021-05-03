CREATE TABLE plugin_quotamanagement (
	group_id int NOT NULL,
	quota_soft int NOT NULL,
	quota_hard int NOT NULL,
	quota_db_soft int NOT NULL,
	quota_db_hard int NOT NULL,
	FOREIGN KEY (group_id) REFERENCES groups (group_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX plugin_quotamanagement_gid_idx ON plugin_quotamanagement(group_id);

INSERT INTO plugin_quotamanagement (SELECT group_id, 0, 0, 0, 0 FROM groups);

ALTER TABLE groups DROP COLUMN IF EXISTS quota_soft;
ALTER TABLE groups DROP COLUMN IF EXISTS quota_hard;
