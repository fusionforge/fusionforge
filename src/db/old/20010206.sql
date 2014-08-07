-- by: pfalcon
-- purpose: add timestamp/version for stored multimedia files

ALTER TABLE db_images ADD COLUMN upload_date int;
ALTER TABLE db_images ALTER COLUMN upload_date SET NOT NULL;

ALTER TABLE db_images ADD COLUMN version int;
ALTER TABLE db_images ALTER COLUMN version SET NOT NULL;

CREATE UNIQUE INDEX usergroup_uniq_groupid_userid ON
user_group(group_id,user_id);
