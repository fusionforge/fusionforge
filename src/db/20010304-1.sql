--
--	need to widen the preference column in user_preferences
--	postgres isn't as slick as MySQL in this respect
--
BEGIN;
ALTER TABLE user_preferences RENAME COLUMN preference_value TO dead1;
ALTER TABLE user_preferences ADD COLUMN preference_value TEXT;
UPDATE user_preferences SET preference_value=dead1;
UPDATE user_preferences SET dead1='';
COMMIT;
--
--	user_group permissions flag
--
ALTER TABLE user_group ADD COLUMN artifact_flags INT;
ALTER TABLE user_group ALTER COLUMN artifact_flags SET NOT NULL;
ALTER TABLE user_group ALTER COLUMN artifact_flags SET DEFAULT 0;

UPDATE user_group SET artifact_flags=0;

--
--	each group can have multiple artifact types
--
create table artifact_group_list (
group_artifact_id serial primary key,
group_id int not null,
name text,
description text,
is_public int not null default 0,
allow_anon int not null default 0,
email_all_updates int not null default 0,
email_address text not null,
due_period int not null default 2592000,
use_resolution int not null default 0,
submit_instructions text,
browse_instructions text,
datatype int not null default 0
);

CREATE INDEX artgrouplist_groupid on artifact_group_list (group_id);
CREATE INDEX artgrouplist_groupid_public on artifact_group_list (group_id,is_public);

create table artifact_resolution (
id serial primary key,
resolution_name text
);

INSERT INTO artifact_resolution SELECT * FROM bug_resolution;

--
--	new permissions model required
--
create table artifact_perm (
id serial primary key,
group_artifact_id int not null,
user_id int not null,
perm_level int not null DEFAULT 0
);

CREATE INDEX artperm_groupartifactid on artifact_perm (group_artifact_id);
CREATE UNIQUE INDEX artperm_groupartifactid_userid on artifact_perm (group_artifact_id,user_id);

--
--	create a view to make selecting all perms for a user_id/group_id easier
--

CREATE VIEW artifactperm_user_vw AS
SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname
	FROM artifact_perm ap, users
	WHERE users.user_id=ap.user_id;

CREATE VIEW artifactperm_artgrouplist_vw AS
SELECT agl.group_artifact_id,agl.name,agl.description,agl.group_id,ap.user_id, ap.perm_level
FROM artifact_perm ap, artifact_group_list agl
WHERE ap.group_artifact_id=agl.group_artifact_id;

--
--	similar to bug_category
--
CREATE TABLE artifact_category (
  id serial primary key,
  group_artifact_id int NOT NULL,
  category_name text NOT NULL,
  auto_assign_to int not null DEFAULT 100
);

CREATE INDEX artcategory_groupartifactid on artifact_category (group_artifact_id);

--
--	similar to bug_group
--
CREATE TABLE artifact_group (
  id serial primary key,
  group_artifact_id int NOT NULL,
  group_name text NOT NULL
);

CREATE INDEX artgroup_groupartifactid on artifact_group (group_artifact_id);

--
--      similar to bug_status
--
CREATE TABLE artifact_status (
  id serial primary key,
  status_name text NOT NULL
);

--
--	similar to bug table
--
CREATE TABLE artifact (
  artifact_id serial primary key,
  group_artifact_id int NOT NULL,
  status_id int DEFAULT '1' NOT NULL,
  category_id int DEFAULT '100' NOT NULL,
  artifact_group_id int DEFAULT '0' NOT NULL,
  resolution_id int not null default '100',
  priority int DEFAULT '5' NOT NULL,
  submitted_by int DEFAULT '100' NOT NULL,
  assigned_to int DEFAULT '100' NOT NULL,
  open_date int DEFAULT '0' NOT NULL,
  close_date int DEFAULT '0' NOT NULL,
  summary text NOT NULL,
  details text NOT NULL
);

CREATE INDEX art_groupartid ON artifact (group_artifact_id);
CREATE INDEX art_groupartid_statusid ON artifact (group_artifact_id,status_id);
CREATE INDEX art_groupartid_assign ON artifact (group_artifact_id,assigned_to);
CREATE INDEX art_groupartid_submit ON artifact (group_artifact_id,submitted_by);
create index art_submit_status ON artifact(submitted_by,status_id);
create index art_assign_status ON artifact(assigned_to,status_id);
create index art_groupartid_artifactid on artifact (group_artifact_id,artifact_id);



CREATE VIEW artifact_vw AS
SELECT
artifact.*,
u.user_name AS assigned_unixname,
u.realname AS assigned_realname,
u.email AS assigned_email,
u2.user_name AS submitted_unixname,
u2.realname AS submitted_realname,
u2.email AS submitted_email,
artifact_status.status_name,
artifact_category.category_name,
artifact_group.group_name,
artifact_resolution.resolution_name
FROM
users u, users u2, artifact, artifact_status, artifact_category, artifact_group, artifact_resolution
WHERE
artifact.assigned_to=u.user_id
AND artifact.submitted_by=u2.user_id
AND artifact.status_id=artifact_status.id
AND artifact.category_id=artifact_category.id
AND artifact.artifact_group_id=artifact_group.id
AND artifact.resolution_id=artifact_resolution.id;


--
--	audit trail table
--
CREATE TABLE artifact_history (
  id serial primary key,
  artifact_id int DEFAULT '0' NOT NULL,
  field_name text DEFAULT '' NOT NULL,
  old_value text DEFAULT '' NOT NULL,
  mod_by int DEFAULT '0' NOT NULL,
  entrydate int DEFAULT '0' NOT NULL
);

CREATE INDEX arthistory_artid on artifact_history(artifact_id);
CREATE INDEX arthistory_artid_entrydate on artifact_history(artifact_id,entrydate);

--
--	create a view from the audit trail which joins the user table and history table
--
CREATE VIEW artifact_history_user_vw AS
SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name
FROM artifact_history ah, users
WHERE ah.mod_by=users.user_id;

--
--	files attached to a given artifact
--
CREATE TABLE artifact_file (
  id serial primary key,
  artifact_id int NOT NULL,
  description text NOT NULL,
  bin_data text NOT NULL,
  filename text NOT NULL,
  filesize int NOT NULL,
  filetype text NOT NULL,
  adddate int not null DEFAULT '0',
  submitted_by int not null
);

CREATE INDEX artfile_artid on artifact_file(artifact_id);
CREATE INDEX artfile_artid_adddate on artifact_file(artifact_id,adddate);

--
--      create a view from the files which joins the user table and files table
--
CREATE VIEW artifact_file_user_vw AS
SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype,
	af.adddate, af.submitted_by, users.user_name, users.realname
FROM artifact_file af,users
WHERE af.submitted_by=users.user_id;

--
--	messages and comments attached to an artifact
--
CREATE TABLE artifact_message (
  id serial primary key,
  artifact_id int NOT NULL,
  submitted_by int not null,
  from_email text NOT NULL,
  adddate int DEFAULT '0' NOT NULL,
  body text NOT NULL
);

CREATE INDEX artmessage_artid on artifact_message(artifact_id);
CREATE INDEX artmessage_artid_adddate on artifact_message(artifact_id,adddate);

--
--      create a view from the messages which joins the user table and messages table
--
CREATE VIEW artifact_message_user_vw AS
SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate,
users.user_id, users.email, users.user_name, users.realname
FROM artifact_message am,users
WHERE am.submitted_by=users.user_id;

--
--	table containing list of people monitoring each artifact
--
CREATE TABLE artifact_monitor (
id serial primary key,
artifact_id int NOT NULL,
user_id int not null,
email text
);

CREATE INDEX artmonitor_artifactid on artifact_monitor(artifact_id);

ALTER TABLE artifact_monitor ADD CONSTRAINT artifactmonitor_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;

INSERT INTO artifact_group_list VALUES (100,1,'Default','Default Data - Dont Edit',3,0,0,'0',0);
INSERT INTO artifact_category VALUES (100,100,'None',100);
INSERT INTO artifact_group VALUES (100,100,'None');
INSERT INTO artifact_status VALUES (1,'Open');
INSERT INTO artifact_status VALUES (2,'Closed');
INSERT INTO artifact_status VALUES (3,'Deleted');

CREATE TABLE artifact_canned_responses (
  id serial primary key,
  group_artifact_id int NOT NULL,
  title text NOT NULL,
  body text NOT NULL
);

CREATE INDEX artifactcannedresponses_groupid ON artifact_canned_responses (group_artifact_id);

CREATE TABLE artifact_counts_agg (
group_artifact_id int not null,
count int not null
);
CREATE INDEX artifactcountsagg_groupartid ON artifact_counts_agg(group_artifact_id);
