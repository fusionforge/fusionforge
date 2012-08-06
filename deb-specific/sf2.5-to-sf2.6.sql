-- 20001209
-- drop index downloads_idx;
-- create index frsdlstatsgroupagg_day_dls on  frs_dlstats_group_agg (day,downloads);
-- create index projectweeklymetric_ranking on project_weekly_metric(ranking);
-- create index users_status on users(status);
-- drop index news_date;
-- create index support_groupid_assignedto_status on support(group_id,assigned_to,support_status_id);
-- create index support_groupid_assignedto on support(group_id,assigned_to);
-- create index support_groupid_status on support(group_id,support_status_id);
-- create index patch_groupid_assignedto_status on patch(group_id,assigned_to,patch_status_id);
-- create index patch_groupid_assignedto on patch(group_id,assigned_to);
-- create index patch_groupid_status on patch(group_id,patch_status_id);
-- create index projecttask_projid_status on project_task(group_project_id,status_id);

CREATE INDEX forummonitoredforums_user ON forum_monitored_forums(user_id);
CREATE INDEX filemodulemonitor_userid ON filemodule_monitor(user_id);
CREATE INDEX support_status_assignedto ON support(support_status_id,assigned_to);
CREATE INDEX bug_status_assignedto ON bug(status_id,assigned_to);

-- 20001214
-- alter table filemodule_monitor add column id int not null default 0 primary key auto_increment first;
-- alter table frs_dlstats_filetotal_agg change column file_id file_id int not null default 0 primary key;
-- alter table group_cvs_history add column id int not null default 0 primary key auto_increment first;
-- DROP TABLE system_news;
-- DROP TABLE system_history;
-- DROP TABLE system_status;
-- DROP TABLE system_services;
-- DROP TABLE system_machines;
-- create index foundrynews_foundry_date_approved on foundry_news(foundry_id,approve_date,is_approved);
-- create index news_group_date on news_bytes(group_id,date);
-- create index news_approved_date on news_bytes(is_approved,date);

-- 20001220
ALTER TABLE patch ADD COLUMN details text;
INSERT INTO themes (dirname, fullname) VALUES ('ultralite','Ultra Lite');

-- 20010109
CREATE TABLE project_sums_agg (
  group_id int NOT NULL DEFAULT 0,
  type char(4),
  count int NOT NULL DEFAULT 0
);
CREATE INDEX projectsumsagg_groupid ON project_sums_agg (group_id);

-- 20010112
ALTER TABLE groups ADD COLUMN bug_due_period int ;
ALTER TABLE groups ALTER COLUMN bug_due_period SET DEFAULT 2592000;
UPDATE groups SET bug_due_period = 2592000 ;
ALTER TABLE groups ADD COLUMN patch_due_period int ;
ALTER TABLE groups ALTER COLUMN patch_due_period SET DEFAULT 5184000;
UPDATE groups SET patch_due_period = 5184000;
ALTER TABLE groups ADD COLUMN support_due_period int ;
ALTER TABLE groups ALTER COLUMN support_due_period SET DEFAULT 1296000;
UPDATE groups SET support_due_period = 1296000;

-- 20010126
CREATE SEQUENCE "prdb_dbs_dbid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

CREATE TABLE "prdb_dbs" (
	"dbid" integer DEFAULT nextval('"prdb_dbs_dbid_seq"'::text) NOT NULL,
	"group_id" integer NOT NULL,
	"dbname" text NOT NULL,
	"dbusername" text NOT NULL,
	"dbuserpass" text NOT NULL,
	"requestdate" integer NOT NULL,
	"dbtype" integer NOT NULL,
	"created_by" integer NOT NULL,
	"state" integer NOT NULL,
	Constraint "prdb_dbs_pkey" Primary Key ("dbid")
);

CREATE TABLE prdb_states (
  stateid INT NOT NULL,
  statename TEXT
);
CREATE UNIQUE INDEX idx_prdb_dbname ON prdb_dbs (dbname);
INSERT INTO prdb_states VALUES ('1', 'Active');
INSERT INTO prdb_states VALUES ('2', 'Pending Create');
INSERT INTO prdb_states VALUES ('3', 'Pending Delete');
INSERT INTO prdb_states VALUES ('4', 'Pending Update');
INSERT INTO prdb_states VALUES ('5', 'Failed Create');
INSERT INTO prdb_states VALUES ('6', 'Failed Delete');
INSERT INTO prdb_states VALUES ('7', 'Failed Update');
CREATE TABLE prdb_types (
  dbtypeid INT PRIMARY KEY,
  dbservername TEXT NOT NULL,
  dbsoftware TEXT NOT NULL
);
INSERT INTO prdb_types VALUES ('1','pr-db1','mysql');
CREATE SEQUENCE "prweb_vhost_vhostid_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "prweb_vhost" (
	"vhostid" integer DEFAULT nextval('"prweb_vhost_vhostid_seq"'::text) NOT NULL,
	"vhost_name" text,
	"docdir" text,
	"cgidir" text,
	"group_id" integer NOT NULL,
	Constraint "prweb_vhost_pkey" Primary Key ("vhostid")
);
CREATE INDEX idx_vhost_groups ON prweb_vhost (group_id);
CREATE UNIQUE INDEX idx_vhost_hostnames ON prweb_vhost(vhost_name);

-- 20010206
ALTER TABLE db_images ADD COLUMN upload_date int ;
ALTER TABLE db_images ALTER COLUMN upload_date SET DEFAULT '0' ;
ALTER TABLE db_images ADD COLUMN version int ;
ALTER TABLE db_images ALTER COLUMN version SET DEFAULT '0' ;
CREATE UNIQUE INDEX usergroup_uniq_groupid_userid ON user_group(group_id,user_id);

-- 20010301
-- \connect - www
-- CREATE TABLE "kernel_traffic" (
--   "kt_id" serial primary key,
--   "kt_data" text,
--   CONSTRAINT "kernel_traffic_pkey" PRIMARY KEY ("kt_id")
-- );

-----

-- artifact-man
ALTER TABLE user_preferences RENAME COLUMN preference_value TO dead1;
ALTER TABLE user_preferences ADD COLUMN preference_value TEXT;
UPDATE user_preferences SET preference_value=dead1;
UPDATE user_preferences SET dead1='';
ALTER TABLE user_group ADD COLUMN artifact_flags INT ;
ALTER TABLE user_group ALTER COLUMN artifact_flags SET DEFAULT '0';
UPDATE user_group SET artifact_flags=0;

CREATE SEQUENCE "artifact_grou_group_artifac_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

--
-- TOC Entry ID 256 (OID 22552)
--
-- Name: artifact_group_list Type: TABLE Owner: tperdue
--

CREATE TABLE "artifact_group_list" (
	"group_artifact_id" integer DEFAULT nextval('"artifact_grou_group_artifac_seq"'::text) NOT NULL,
	"group_id" integer NOT NULL,
	"name" text,
	"description" text,
	"is_public" integer DEFAULT 0 NOT NULL,
	"allow_anon" integer DEFAULT 0 NOT NULL,
	"email_all_updates" integer DEFAULT 0 NOT NULL,
	"email_address" text NOT NULL,
	"due_period" integer DEFAULT 2592000 NOT NULL,
	"use_resolution" integer DEFAULT 0 NOT NULL,
	"submit_instructions" text,
	"browse_instructions" text,
	"datatype" integer DEFAULT 0 NOT NULL,
	Constraint "artifact_group_list_pkey" Primary Key ("group_artifact_id")
);
CREATE INDEX artgrouplist_groupid on artifact_group_list (group_id);
CREATE INDEX artgrouplist_groupid_public on artifact_group_list (group_id,is_public);
CREATE SEQUENCE "artifact_resolution_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_resolution" (
	"id" integer DEFAULT nextval('"artifact_resolution_id_seq"'::text) NOT NULL,
	"resolution_name" text,
	Constraint "artifact_resolution_pkey" Primary Key ("id")
);
INSERT INTO artifact_resolution SELECT * FROM bug_resolution;
CREATE SEQUENCE "artifact_perm_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_perm" (
	"id" integer DEFAULT nextval('"artifact_perm_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"perm_level" integer DEFAULT 0 NOT NULL,
	Constraint "artifact_perm_pkey" Primary Key ("id")
);
CREATE INDEX artperm_groupartifactid on artifact_perm (group_artifact_id);
CREATE UNIQUE INDEX artperm_groupartifactid_userid on artifact_perm (group_artifact_id,user_id);
CREATE VIEW artifactperm_artgrouplist_vw AS
SELECT agl.group_artifact_id,agl.name,agl.description,agl.group_id,ap.user_id, ap.perm_level
FROM artifact_perm ap, artifact_group_list agl
WHERE ap.group_artifact_id=agl.group_artifact_id;
CREATE SEQUENCE "artifact_category_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_category" (
	"id" integer DEFAULT nextval('"artifact_category_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"category_name" text NOT NULL,
	"auto_assign_to" integer DEFAULT 100 NOT NULL,
	Constraint "artifact_category_pkey" Primary Key ("id")
);
CREATE INDEX artcategory_groupartifactid on artifact_category (group_artifact_id);
CREATE SEQUENCE "artifact_group_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_group" (
	"id" integer DEFAULT nextval('"artifact_group_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"group_name" text NOT NULL,
	Constraint "artifact_group_pkey" Primary Key ("id")
);
CREATE INDEX artgroup_groupartifactid on artifact_group (group_artifact_id);
CREATE SEQUENCE "artifact_status_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_status" (
	"id" integer DEFAULT nextval('"artifact_status_id_seq"'::text) NOT NULL,
	"status_name" text NOT NULL,
	Constraint "artifact_status_pkey" Primary Key ("id")
);
CREATE SEQUENCE "artifact_artifact_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact" (
	"artifact_id" integer DEFAULT nextval('"artifact_artifact_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"status_id" integer DEFAULT '1' NOT NULL,
	"category_id" integer DEFAULT '100' NOT NULL,
	"artifact_group_id" integer DEFAULT '0' NOT NULL,
	"resolution_id" integer DEFAULT '100' NOT NULL,
	"priority" integer DEFAULT '5' NOT NULL,
	"submitted_by" integer DEFAULT '100' NOT NULL,
	"assigned_to" integer DEFAULT '100' NOT NULL,
	"open_date" integer DEFAULT '0' NOT NULL,
	"close_date" integer DEFAULT '0' NOT NULL,
	"summary" text NOT NULL,
	"details" text NOT NULL,
	Constraint "artifact_pkey" Primary Key ("artifact_id")
);
CREATE INDEX art_groupartid ON artifact (group_artifact_id);
CREATE INDEX art_groupartid_statusid ON artifact (group_artifact_id,status_id);
CREATE INDEX art_groupartid_assign ON artifact (group_artifact_id,assigned_to);
CREATE INDEX art_groupartid_submit ON artifact (group_artifact_id,submitted_by);
CREATE INDEX art_submit_status ON artifact(submitted_by,status_id);
CREATE INDEX art_assign_status ON artifact(assigned_to,status_id);
CREATE INDEX art_groupartid_artifactid ON artifact (group_artifact_id,artifact_id);
CREATE SEQUENCE "artifact_history_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_history" (
	"id" integer DEFAULT nextval('"artifact_history_id_seq"'::text) NOT NULL,
	"artifact_id" integer DEFAULT '0' NOT NULL,
	"field_name" text DEFAULT '' NOT NULL,
	"old_value" text DEFAULT '' NOT NULL,
	"mod_by" integer DEFAULT '0' NOT NULL,
	"entrydate" integer DEFAULT '0' NOT NULL,
	Constraint "artifact_history_pkey" Primary Key ("id")
);
CREATE INDEX arthistory_artid on artifact_history(artifact_id);
CREATE INDEX arthistory_artid_entrydate on artifact_history(artifact_id,entrydate);
CREATE SEQUENCE "artifact_file_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_file" (
	"id" integer DEFAULT nextval('"artifact_file_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"description" text NOT NULL,
	"bin_data" text NOT NULL,
	"filename" text NOT NULL,
	"filesize" integer NOT NULL,
	"filetype" text NOT NULL,
	"adddate" integer DEFAULT '0' NOT NULL,
	"submitted_by" integer NOT NULL,
	Constraint "artifact_file_pkey" Primary Key ("id")
);
CREATE INDEX artfile_artid on artifact_file(artifact_id);
CREATE INDEX artfile_artid_adddate on artifact_file(artifact_id,adddate);
CREATE SEQUENCE "artifact_message_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_message" (
	"id" integer DEFAULT nextval('"artifact_message_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"submitted_by" integer NOT NULL,
	"from_email" text NOT NULL,
	"adddate" integer DEFAULT '0' NOT NULL,
	"body" text NOT NULL,
	Constraint "artifact_message_pkey" Primary Key ("id")
);
CREATE INDEX artmessage_artid on artifact_message(artifact_id);
CREATE INDEX artmessage_artid_adddate on artifact_message(artifact_id,adddate);
CREATE SEQUENCE "artifact_monitor_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_monitor" (
	"id" integer DEFAULT nextval('"artifact_monitor_id_seq"'::text) NOT NULL,
	"artifact_id" integer NOT NULL,
	"user_id" integer NOT NULL,
	"email" text,
	Constraint "artifact_monitor_pkey" Primary Key ("id")
);
CREATE INDEX artmonitor_artifactid on artifact_monitor(artifact_id);

INSERT INTO artifact_group_list VALUES (100,1,'Default','Default Data - Dont Edit',3,0,0,'0',0);
INSERT INTO artifact_category VALUES (100,100,'None',100);
INSERT INTO artifact_group VALUES (100,100,'None');
INSERT INTO artifact_status VALUES (1,'Open');
INSERT INTO artifact_status VALUES (2,'Closed');
INSERT INTO artifact_status VALUES (3,'Deleted');
CREATE SEQUENCE "artifact_canned_response_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;
CREATE TABLE "artifact_canned_responses" (
	"id" integer DEFAULT nextval('"artifact_canned_response_id_seq"'::text) NOT NULL,
	"group_artifact_id" integer NOT NULL,
	"title" text NOT NULL,
	"body" text NOT NULL,
	Constraint "artifact_canned_responses_pkey" Primary Key ("id")
);

CREATE INDEX artifactcannedresponses_groupid ON artifact_canned_responses (group_artifact_id);

CREATE TABLE artifact_counts_agg (
	group_artifact_id int not null,
	count int not null
);
CREATE INDEX artifactcountsagg_groupartid ON artifact_counts_agg(group_artifact_id);
----- TODO
-- Re-enable this when the "stats" account exists
-----
-- GRANT SELECT ON
-- artifact,
-- artifact_group_list
-- TO stats;

-- artifact-conversion
UPDATE groups SET bug_due_period='2592000' WHERE bug_due_period is null;
INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+100000,group_id,'Bugs','Bug Tracking System',use_bugs,
1,send_all_bugs,new_bug_address,bug_due_period,1,1
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;
INSERT INTO artifact_perm
(group_artifact_id,user_id,perm_level)
SELECT group_id+100000,user_id,bug_flags
FROM user_group;
INSERT INTO artifact_group (id,group_artifact_id,group_name)
SELECT bug_group_id+100000,group_id+100000,group_name FROM bug_group;
INSERT INTO artifact_category (id,group_artifact_id,category_name,auto_assign_to)
SELECT bug_category_id+100000,group_id+100000,category_name,100 FROM bug_category;
UPDATE bug SET status_id=1 WHERE status_id=100;
INSERT INTO bug_status (status_id,status_name) VALUES (2,'Open');
UPDATE bug SET status_id=2 WHERE status_id=3;
DELETE FROM bug_status WHERE status_id=3;
UPDATE bug SET close_date=0 WHERE close_date is NULL;
INSERT INTO artifact
(artifact_id,group_artifact_id,status_id,category_id,artifact_group_id,priority,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id)
SELECT
bug_id+100000,group_id+100000,status_id,category_id+100000,bug_group_id+100000,priority,
submitted_by,assigned_to,date,close_date,summary,details,resolution_id
FROM bug WHERE summary is not null
ORDER BY group_id ASC;
-- UPDATE bug_history SET old_value=1 WHERE old_value='100' AND field_name='status_id';
UPDATE bug_history SET old_value=2 WHERE old_value='3' AND field_name='status_id';
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
bug_id+100000,field_name,old_value,mod_by,date
FROM bug_history
WHERE field_name IN ('summary','resolution_id','priority','group_id','close_date','assigned_to','status_id');
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
bug_id+100000,'artifact_group_id',(old_value::int)+100000,mod_by,date
FROM bug_history
WHERE field_name='bug_group_id';
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
bug_id+100000,field_name,(old_value::int)+100000,mod_by,date
FROM bug_history
WHERE field_name='category_id';
INSERT INTO artifact_message
(artifact_id,submitted_by,from_email,adddate,body)
SELECT
bh.bug_id+100000,bh.mod_by,users.email,bh.date,bh.old_value
FROM bug_history bh, users
WHERE bh.mod_by=users.user_id
AND bh.field_name='details';
delete from bug_canned_responses where title is null;
INSERT INTO artifact_canned_responses
(group_artifact_id,title,body)
SELECT
group_id+100000,title,body
FROM bug_canned_responses
WHERE group_id > 0;
UPDATE groups SET support_due_period='2592000' WHERE support_due_period is null;
INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+200000,group_id,'Support Requests','Tech Support Tracking System',use_support,
1,send_all_support,new_support_address,support_due_period,0,2
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;
INSERT INTO artifact_perm
(group_artifact_id,user_id,perm_level)
SELECT group_id+200000,user_id,support_flags
FROM user_group;
INSERT INTO artifact_category (id,group_artifact_id,category_name,auto_assign_to)
SELECT support_category_id+200000,group_id+200000,category_name,100 FROM support_category;
DELETE FROM support WHERE NOT EXISTS
(SELECT group_id FROM groups WHERE support.group_id=groups.group_id);
INSERT INTO artifact
(artifact_id,group_artifact_id,status_id,category_id,artifact_group_id,priority,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id)
SELECT
support_id+200000,group_id+200000,support_status_id,support_category_id+200000,100,priority,
submitted_by,assigned_to,open_date,close_date,summary,'',100
FROM support
ORDER BY group_id ASC;
DELETE FROM support_history WHERE support_id=0;
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
support_id+200000,field_name,old_value,mod_by,date
FROM support_history
WHERE
field_name IN ('summary','priority','close_date','assigned_to');
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
support_id+200000,'category_id',(old_value::int)+200000,mod_by,date
FROM support_history
WHERE
field_name='support_category_id';
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
support_id+200000,'status_id',old_value,mod_by,date
FROM support_history
WHERE
field_name='support_status_id';
DELETE FROM support_messages WHERE NOT EXISTS
(SELECT support_id FROM support WHERE support.support_id=support_messages.support_id);
INSERT INTO artifact_message
(artifact_id,submitted_by,from_email,adddate,body)
SELECT
support_id+200000,100,from_email,date,body
FROM support_messages;
INSERT INTO artifact_canned_responses
(group_artifact_id,title,body)
SELECT
group_id+200000,title,body
FROM support_canned_responses
WHERE group_id > 0;
UPDATE groups SET patch_due_period='2592000' WHERE patch_due_period is null;
INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+300000,group_id,'Patches','Patch Tracking System',use_patch,
1,send_all_patches,new_patch_address,patch_due_period,1,3
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;
INSERT INTO artifact_perm
(group_artifact_id,user_id,perm_level)
SELECT group_id+300000,user_id,patch_flags
FROM user_group;
INSERT INTO artifact_category (id,group_artifact_id,category_name,auto_assign_to)
SELECT patch_category_id+300000,group_id+300000,category_name,100 FROM patch_category;
ALTER TABLE patch ADD COLUMN resolution_id INT ;
ALTER TABLE patch ALTER COLUMN resolution_id SET DEFAULT 100;
UPDATE patch SET resolution_id=patch_status_id;
-- vacuum analyze patch;
update patch set patch_status_id=2 where patch_status_id > 3;
update patch set resolution_id=100 WHERE resolution_id < 4;
INSERT INTO artifact_resolution VALUES (102,'Accepted');
INSERT INTO artifact_resolution VALUES (103,'Out of Date');
INSERT INTO artifact_resolution VALUES (104,'Postponed');
INSERT INTO artifact_resolution VALUES (105,'Rejected');
update patch set resolution_id=104 WHERE resolution_id=4;
update patch set resolution_id=105 WHERE resolution_id=101;
delete from patch where patch_id=100000;
UPDATE patch SET details=' ' WHERE details is null;

INSERT INTO artifact
(artifact_id,group_artifact_id,status_id,category_id,artifact_group_id,priority,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id)
SELECT
patch_id+300000,group_id+300000,patch_status_id,patch_category_id+300000,100,5,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id
FROM patch
WHERE summary is not null
ORDER BY group_id ASC;
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
patch_id+300000,field_name,old_value,mod_by,date
FROM patch_history
WHERE field_name IN ('summary','close_date','assigned_to','Patch Code');

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
patch_id+300000,'status_id',old_value,mod_by,date
FROM patch_history
WHERE field_name='patch_status_id';

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
patch_id+300000,'category_id',(old_value::int)+300000,mod_by,date
FROM patch_history
WHERE field_name='patch_category_id';
INSERT INTO artifact_message
(artifact_id,submitted_by,from_email,adddate,body)
SELECT
ph.patch_id+300000,ph.mod_by,users.email,ph.date,ph.old_value
FROM patch_history ph, users
WHERE ph.mod_by=users.user_id
AND ph.field_name='details';

INSERT INTO artifact_file
(artifact_id,description,bin_data,filename,filesize,filetype,adddate,submitted_by)
SELECT patch_id+300000,'None',code,'None',length(code),'text/plain',open_date,submitted_by
FROM patch
WHERE code IS NOT NULL;

INSERT INTO artifact_counts_agg
SELECT group_artifact_id,count(*)
FROM artifact
WHERE status_id <> 3
GROUP BY group_artifact_id;

INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+350000,group_id,'Feature Requests','Feature Request Tracking System',1,
1,0,'',45*24*60*60,0,4
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;

-----
-- Roland Mas 20020307 and 20020308
-- Drop and recreate the groups and users tables.
-- Goals:
-- 1. Remove the dead columns (in groups)
-- 2. Get rid of the undeleteable foreign key constraints with old tables

ALTER TABLE groups RENAME TO old_groups ;
DROP INDEX groups_type ;
DROP INDEX groups_public ;
DROP INDEX groups_status ;

CREATE TABLE "groups" (
	"group_id" integer DEFAULT nextval('groups_pk_seq'::text) NOT NULL,
	"group_name" character varying(40),
	"homepage" character varying(128),
	"is_public" integer DEFAULT '0' NOT NULL,
	"status" character(1) DEFAULT 'A' NOT NULL,
	"unix_group_name" character varying(30) DEFAULT '' NOT NULL,
	"unix_box" character varying(20) DEFAULT 'shell' NOT NULL,
	"http_domain" character varying(80),
	"short_description" character varying(255),
	"cvs_box" character varying(20) DEFAULT 'cvs' NOT NULL,
	"license" character varying(16),
	"register_purpose" text,
	"license_other" text,
	"register_time" integer DEFAULT '0' NOT NULL,
	"rand_hash" text,
	"use_mail" integer DEFAULT '1' NOT NULL,
	"use_survey" integer DEFAULT '1' NOT NULL,
	"use_forum" integer DEFAULT '1' NOT NULL,
	"use_pm" integer DEFAULT '1' NOT NULL,
	"use_cvs" integer DEFAULT '1' NOT NULL,
	"use_news" integer DEFAULT '1' NOT NULL,
	"type" integer DEFAULT '1' NOT NULL,
	"use_docman" integer DEFAULT '1' NOT NULL,
	"new_task_address" text DEFAULT '' NOT NULL,
	"send_all_tasks" integer DEFAULT '0' NOT NULL,
	"use_pm_depend_box" integer DEFAULT '1' NOT NULL,
	CONSTRAINT "groups_pkey" PRIMARY KEY ("group_id")
);

INSERT INTO groups
SELECT group_id, group_name, homepage, is_public, status, unix_group_name,
unix_box, http_domain, short_description, cvs_box, license,
register_purpose, license_other, register_time, rand_hash, use_mail,
use_survey, use_forum, use_pm, use_cvs, use_news, type, use_docman,
new_task_address, send_all_tasks, use_pm_depend_box
FROM old_groups ;

DROP TABLE old_groups ;

ALTER TABLE artifact_group_list ADD CONSTRAINT artifactgroup_groupid_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

CREATE UNIQUE INDEX group_unix_uniq ON groups USING BTREE (unix_group_name varchar_ops);
CREATE INDEX groups_type ON groups USING BTREE (type int4_ops);
CREATE INDEX groups_public ON groups USING BTREE (is_public int4_ops);
CREATE INDEX groups_status ON groups USING BTREE (status bpchar_ops);

ALTER TABLE users RENAME TO old_users ;
DROP INDEX users_status ;
DROP INDEX user_user ;
DROP INDEX idx_users_username ;
DROP INDEX users_user_pw ;

CREATE TABLE "users" (
	"user_id" integer DEFAULT nextval('users_pk_seq'::text) NOT NULL,
	"user_name" text DEFAULT '' NOT NULL,
	"email" text DEFAULT '' NOT NULL,
	"user_pw" character varying(32) DEFAULT '' NOT NULL,
	"realname" character varying(32) DEFAULT '' NOT NULL,
	"status" character(1) DEFAULT 'A' NOT NULL,
	"shell" character varying(20) DEFAULT '/bin/bash' NOT NULL,
	"unix_pw" character varying(40) DEFAULT '' NOT NULL,
	"unix_status" character(1) DEFAULT 'N' NOT NULL,
	"unix_uid" integer DEFAULT '0' NOT NULL,
	"unix_box" character varying(10) DEFAULT 'shell' NOT NULL,
	"add_date" integer DEFAULT '0' NOT NULL,
	"confirm_hash" character varying(32),
	"mail_siteupdates" integer DEFAULT '0' NOT NULL,
	"mail_va" integer DEFAULT '0' NOT NULL,
	"authorized_keys" text,
	"email_new" text,
	"people_view_skills" integer DEFAULT '0' NOT NULL,
	"people_resume" text DEFAULT '' NOT NULL,
	"timezone" character varying(64) DEFAULT 'GMT',
	"language" integer DEFAULT '1' NOT NULL,
	CONSTRAINT "users_pkey" PRIMARY KEY ("user_id")
);
CREATE VIEW artifactperm_user_vw AS
SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname
	FROM artifact_perm ap, users
	WHERE users.user_id=ap.user_id;
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
CREATE VIEW artifact_history_user_vw AS
SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name
FROM artifact_history ah, users
WHERE ah.mod_by=users.user_id;
CREATE VIEW artifact_file_user_vw AS
SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype,
	af.adddate, af.submitted_by, users.user_name, users.realname
FROM artifact_file af,users
WHERE af.submitted_by=users.user_id;
CREATE VIEW artifact_message_user_vw AS
SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate,
users.user_id, users.email, users.user_name, users.realname
FROM artifact_message am,users
WHERE am.submitted_by=users.user_id;

INSERT INTO users
SELECT user_id, user_name, email, user_pw, realname, status, shell,
unix_pw, unix_status, unix_uid, unix_box, add_date, confirm_hash,
mail_siteupdates, mail_va, authorized_keys, email_new,
people_view_skills, people_resume, timezone, language
FROM old_users ;

DROP TABLE old_users ;

ALTER TABLE user_group ADD CONSTRAINT user_group_user_id_fk
	FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL ;
-- ALTER TABLE forum ADD CONSTRAINT forum_posted_by_fk
--	FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL ;
-- ALTER TABLE forum_group_list ADD CONSTRAINT forum_group_list_group_id_fk
--	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;
-- ALTER TABLE project_task ADD CONSTRAINT project_task_created_by_fk
-- 	FOREIGN KEY (created_by) REFERENCES users(user_id) MATCH FULL ;
ALTER TABLE users ADD CONSTRAINT users_languageid_fk
	FOREIGN KEY (language) REFERENCES supported_languages(language_id) MATCH FULL ;

CREATE INDEX users_status ON users USING BTREE (status bpchar_ops);
CREATE INDEX idx_users_username ON users USING BTREE (user_name text_ops);
CREATE INDEX users_user_pw ON users USING BTREE (user_pw varchar_ops);

-- End of Roland Mas 20020307 and 20020308
-----

-- artifact-fkeys
DELETE from artifact_perm
	where not exists (select group_artifact_id
	from artifact_group_list
	where artifact_perm.group_artifact_id=artifact_group_list.group_artifact_id);
ALTER TABLE artifact_perm ADD CONSTRAINT artifactperm_userid_fk
        FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact_perm ADD CONSTRAINT artifactperm_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact_category ADD CONSTRAINT artifactcategory_autoassignto_fk
        FOREIGN KEY (auto_assign_to) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact_category ADD CONSTRAINT artifactcategory_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact_group ADD CONSTRAINT artifactgroup_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact ADD CONSTRAINT artifact_artifactgroupid_fk
        FOREIGN KEY (artifact_group_id) REFERENCES artifact_group(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_assignedto_fk
        FOREIGN KEY (assigned_to) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_categoryid_fk
        FOREIGN KEY (category_id) REFERENCES artifact_category(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_resolutionid_fk
        FOREIGN KEY (resolution_id) REFERENCES artifact_resolution(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_statusid_fk
        FOREIGN KEY (status_id) REFERENCES artifact_status(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

DELETE FROM artifact_history WHERE NOT EXISTS
	(SELECT artifact_id FROM artifact WHERE artifact.artifact_id=artifact_history.artifact_id);
ALTER TABLE artifact_history ADD CONSTRAINT artifacthistory_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_history ADD CONSTRAINT artifacthistory_modby_fk
        FOREIGN KEY (mod_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_file ADD CONSTRAINT artifactfile_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_file ADD CONSTRAINT artifactfile_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_message ADD CONSTRAINT artifactmessage_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_message ADD CONSTRAINT artifactmessage_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_monitor ADD CONSTRAINT artifactmonitor_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;

SELECT setval('artifact_grou_group_artifac_seq',(SELECT max(group_artifact_id) FROM artifact_group_list));
--SELECT setval('artifact_perm_id_seq',(SELECT max(id) FROM artifact_perm));
SELECT setval('artifact_category_id_seq',(SELECT max(id) FROM artifact_category));
SELECT setval('artifact_group_id_seq',(SELECT max(id) FROM artifact_group));
--SELECT setval('artifact_status_id_seq',(SELECT max(id) FROM artifact_status));
SELECT setval('artifact_artifact_id_seq',(SELECT max(artifact_id) FROM artifact));
--SELECT setval('artifact_history_id_seq',(SELECT max(id) FROM artifact_history));
--SELECT setval('artifact_file_id_seq',(SELECT max(id) FROM artifact_file));
--SELECT setval('artifact_message_id_seq',(SELECT max(id) FROM artifact_message));
--SELECT setval('artifact_monitor_id_seq',(SELECT max(id) FROM artifact_monitor));

-- 20010305
----- TODO
-- Re-enable the grants once we are sure the "backend" account exists
-----
-- CREATE USER backend WITH PASSWORD 'xxxxx' NOCREATEDB NOCREATEUSER;
-- GRANT SELECT ON prweb_vhost TO backend;
-- GRANT SELECT,UPDATE ON prdb_dbs TO backend;

DROP SEQUENCE bug_bug_dependencies_pk_seq;
DROP SEQUENCE bug_canned_responses_pk_seq;
DROP SEQUENCE bug_category_pk_seq       ;
DROP SEQUENCE bug_filter_pk_seq         ;
DROP SEQUENCE bug_group_pk_seq       ;
DROP SEQUENCE bug_history_pk_seq    ;
DROP SEQUENCE bug_pk_seq            ;
DROP SEQUENCE bug_resolution_pk_seq  ;
DROP SEQUENCE bug_status_pk_seq      ;
DROP SEQUENCE bug_task_dependencies_pk_seq ;
DROP SEQUENCE patch_category_pk_seq   ;
DROP SEQUENCE patch_history_pk_seq    ;
DROP SEQUENCE patch_pk_seq            ;
DROP SEQUENCE patch_status_pk_seq     ;
DROP SEQUENCE support_canned_responses_pk_seq;
DROP SEQUENCE support_category_pk_seq   ;
DROP SEQUENCE support_history_pk_seq    ;
DROP SEQUENCE support_messages_pk_seq   ;
DROP SEQUENCE support_pk_seq            ;
DROP SEQUENCE support_status_pk_seq     ;

DROP TABLE bug                 ;
DROP TABLE bug_bug_dependencies  ;
DROP TABLE bug_canned_responses  ;
DROP TABLE bug_category          ;
DROP TABLE bug_filter            ;
DROP TABLE bug_group             ;
DROP TABLE bug_history           ;
DROP TABLE bug_resolution        ;
DROP TABLE bug_status            ;
DROP TABLE bug_task_dependencies ;
DROP TABLE patch                 ;
DROP TABLE patch_category        ;
DROP TABLE patch_history         ;
DROP TABLE patch_status          ;
DROP TABLE support               ;
DROP TABLE support_canned_responses ;
DROP TABLE support_category         ;
DROP TABLE support_history          ;
DROP TABLE support_messages         ;
DROP TABLE support_status           ;

-- 20010313
create unique index users_namename_uniq on users(user_name);
-- CREATE FUNCTION plpgsql_call_handler () RETURNS OPAQUE AS '/usr/local/pgsql/lib/plpgsql.so' LANGUAGE 'C';
-- CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER plpgsql_call_handler LANCOMPILER 'PL/pgSQL';
CREATE FUNCTION forumgrouplist_insert_agg () RETURNS OPAQUE AS '
BEGIN
        INSERT INTO forum_agg_msg_count (group_forum_id,count) \
                VALUES (NEW.group_forum_id,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';
CREATE TRIGGER forumgrouplist_insert_trig AFTER INSERT ON forum_group_list
        FOR EACH ROW EXECUTE PROCEDURE forumgrouplist_insert_agg();
CREATE RULE forum_insert_agg AS
    ON INSERT TO forum
    DO UPDATE forum_agg_msg_count SET count=count+1
        WHERE group_forum_id=new.group_forum_id;
CREATE RULE forum_delete_agg AS
    ON DELETE TO forum
    DO UPDATE forum_agg_msg_count SET count=count-1
        WHERE group_forum_id=old.group_forum_id;
ALTER TABLE artifact_counts_agg ADD COLUMN open_count int;
CREATE FUNCTION artifactgrouplist_insert_agg () RETURNS OPAQUE AS '
BEGIN
	INSERT INTO artifact_counts_agg (group_artifact_id,count,open_count) \
		VALUES (NEW.group_artifact_id,0,0);
        RETURN NEW;
END;
' LANGUAGE 'plpgsql';

CREATE TRIGGER artifactgrouplist_insert_trig AFTER INSERT ON artifact_group_list
        FOR EACH ROW EXECUTE PROCEDURE artifactgrouplist_insert_agg();
CREATE RULE artifact_insert_agg AS
	ON INSERT TO artifact
	DO UPDATE artifact_counts_agg SET count=count+1,open_count=open_count+1
		WHERE group_artifact_id=new.group_artifact_id;
-- drop TRIGGER artifactgroup_update_trig ON artifact;
-- drop function artifactgroup_update_agg();

CREATE FUNCTION artifactgroup_update_agg () RETURNS OPAQUE AS '
BEGIN
	--
	-- see if they are moving to a new artifacttype
	-- if so, its a more complex operation
	--
	IF NEW.group_artifact_id <> OLD.group_artifact_id THEN
		--
		-- transferred artifacts always have a status of 1
		-- so we will increment the new artifacttypes sums
		--
		UPDATE artifact_counts_agg SET count=count+1, open_count=open_count+1 \
			WHERE group_artifact_id=NEW.group_artifact_id;

		--
		--	now see how to increment/decrement the old types sums
		--
		IF NEW.status_id <> OLD.status_id THEN
			IF OLD.status_id = 2 THEN
				UPDATE artifact_counts_agg SET count=count-1 \
					WHERE group_artifact_id=OLD.group_artifact_id;
			--
			--	no need to do anything if it was in deleted status
			--
			END IF;
		ELSE
			--
			--	Was already in open status before
			--
			UPDATE artifact_counts_agg SET count=count-1, open_count=open_count-1 \
				WHERE group_artifact_id=OLD.group_artifact_id;
		END IF;
	ELSE
		--
		-- just need to evaluate the status flag and
		-- increment/decrement the counter as necessary
		--
		IF NEW.status_id <> OLD.status_id THEN
			IF new.status_id = 1 THEN
				UPDATE artifact_counts_agg SET open_count=open_count+1 \
					WHERE group_artifact_id=new.group_artifact_id;
			ELSE
				IF new.status_id = 2 THEN
					UPDATE artifact_counts_agg SET open_count=open_count-1 \
						WHERE group_artifact_id=new.group_artifact_id;
				ELSE
					IF new.status_id = 3 THEN
						UPDATE artifact_counts_agg SET open_count=open_count-1,count=count-1 \
							WHERE group_artifact_id=new.group_artifact_id;
					END IF;
				END IF;
			END IF;
		END IF;
	END IF;
	RETURN NEW;
END;
' LANGUAGE 'plpgsql';
CREATE TRIGGER artifactgroup_update_trig AFTER UPDATE ON artifact
	FOR EACH ROW EXECUTE PROCEDURE artifactgroup_update_agg();

-- 20010317
CREATE SEQUENCE "massmail_queue_id_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1  cache 1 ;

CREATE TABLE "massmail_queue" (
	"id" integer DEFAULT nextval('"massmail_queue_id_seq"'::text) NOT NULL,
	"type" character varying(8) NOT NULL,
	"subject" text NOT NULL,
	"message" text NOT NULL,
	"queued_date" integer NOT NULL,
	"last_userid" integer DEFAULT 0 NOT NULL,
	"failed_date" integer DEFAULT 0 NOT NULL,
	"finished_date" integer DEFAULT 0 NOT NULL,
	Constraint "massmail_queue_pkey" Primary Key ("id")
);

-- 20010409
-- drop table stats_project_build_tmp;
drop table tmp_projs_releases_tmp;
delete from stats_project where day is null or week is null;
drop table stats_project_tmp;
-- drop table topproj_admins;
DROP TABLE frs_dlstats_agg;
DROP TABLE frs_dlstats_filetotal_agg_old;
DROP TABLE stats_agg_pages_by_browser;
DROP TABLE stats_agg_pages_by_day_old;
DROP TABLE stats_agr_filerelease;
DROP TABLE stats_agr_project;
DROP TABLE group_cvs_history;
CREATE TABLE frs_dlstats_file_agg_tmp AS
SELECT
	substring(day::text from 1 for 6)::int AS month,
	substring(day::text from 7 for 2)::int AS day,
	file_id,
	downloads
	from frs_dlstats_file_agg;

DROP TABLE frs_dlstats_file_agg;
ALTER TABLE frs_dlstats_file_agg_tmp RENAME TO frs_dlstats_file_agg;
CREATE UNIQUE INDEX frsdlfileagg_month_day_file ON frs_dlstats_file_agg(month,day,file_id);
drop index httpdl_fid;
drop index httpdl_group_id;
create index statshttpdl_day_fileid ON stats_http_downloads(day,filerelease_id);
drop index ftpdl_fid;
drop index ftpdl_group_id;
create index statsftpdl_day_fileid ON stats_ftp_downloads(day,filerelease_id);
CREATE TABLE stats_project_metric (
month int not null default 0,
day int not null default 0,
ranking int not null default 0,
percentile float not null default 0,
group_id int not null default 0
);
-- copy stats_project_metric from '/tmp/stats_project_metric.dump';
CREATE UNIQUE INDEX statsprojectmetric_month_day_group ON stats_project_metric(month,day,group_id);
CREATE TABLE stats_agg_site_by_group_tmp AS
SELECT
	substring(day::text from 1 for 6)::int AS month,
	substring(day::text from 7 for 2)::int AS day,
	group_id,
	count
	from stats_agg_site_by_group ;

DROP TABLE stats_agg_site_by_group;
ALTER TABLE stats_agg_site_by_group_tmp RENAME TO stats_agg_site_by_group;

DROP TABLE stats_agg_site_by_day;

CREATE UNIQUE INDEX statssitebygroup_month_day_group ON stats_agg_site_by_group(month,day,group_id);
CREATE TABLE stats_agg_logo_by_group_tmp AS
SELECT
	substring(day::text from 1 for 6)::int AS month,
	substring(day::text from 7 for 2)::int AS day,
	group_id,
	count
	from stats_agg_logo_by_group ;

DROP TABLE stats_agg_logo_by_group;
ALTER TABLE stats_agg_logo_by_group_tmp RENAME TO stats_agg_logo_by_group;

CREATE UNIQUE INDEX statslogobygroup_month_day_group ON stats_agg_logo_by_group(month,day,group_id);
create table stats_subd_pages (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
pages INT NOT NULL DEFAULT 0
);
INSERT INTO stats_subd_pages
SELECT month,day,group_id,subdomain_views
FROM stats_project WHERE subdomain_views > 0;

CREATE UNIQUE INDEX statssubdpages_month_day_group ON stats_subd_pages(month,day,group_id);


create table stats_cvs_user (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
user_id INT NOT NULL DEFAULT 0,
checkouts INT NOT NULL DEFAULT 0,
commits INT NOT NULL DEFAULT 0,
adds INT NOT NULL DEFAULT 0
);
create table stats_cvs_group (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
checkouts INT NOT NULL DEFAULT 0,
commits INT NOT NULL DEFAULT 0,
adds INT NOT NULL DEFAULT 0
);
INSERT INTO stats_cvs_group
SELECT month,day,group_id,cvs_checkouts,cvs_commits,cvs_adds
FROM stats_project
WHERE cvs_checkouts > 0
OR cvs_commits > 0
OR cvs_adds > 0;

CREATE UNIQUE INDEX statscvsgroup_month_day_group ON stats_cvs_group(month,day,group_id);
DROP INDEX archive_project_day;
DROP INDEX archive_project_month;
DROP INDEX archive_project_monthday;
DROP INDEX archive_project_week;
DROP INDEX project_log_group;
create table stats_project_developers (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
developers INT NOT NULL DEFAULT 0
);
-- COPY stats_project_developers from '/tmp/stats_project_developers';
CREATE UNIQUE INDEX statsprojectdev_month_day_group ON stats_project_developers(month,day,group_id);
DROP TABLE stats_project;

create table stats_project (
month INT NOT NULL DEFAULT 0,
day INT NOT NULL DEFAULT 0,
group_id INT NOT NULL DEFAULT 0,
file_releases INT DEFAULT 0,
msg_posted INT DEFAULT 0,
msg_uniq_auth INT DEFAULT 0,
bugs_opened INT DEFAULT 0,
bugs_closed INT DEFAULT 0,
support_opened INT DEFAULT 0,
support_closed INT DEFAULT 0,
patches_opened INT DEFAULT 0,
patches_closed INT DEFAULT 0,
artifacts_opened INT DEFAULT 0,
artifacts_closed INT DEFAULT 0,
tasks_opened INT DEFAULT 0,
tasks_closed INT DEFAULT 0,
help_requests INT DEFAULT 0
);
-- copy stats_project from '/tmp/stats_project.dump';
CREATE UNIQUE INDEX statsproject_month_day_group ON stats_project(month,day,group_id);
CREATE TABLE stats_site_tmp AS
SELECT month,day,uniq_users,sessions,total_users,new_users,new_projects
FROM stats_site;

DROP TABLE stats_site;
ALTER TABLE stats_site_tmp RENAME TO stats_site;

CREATE UNIQUE INDEX statssite_month_day on stats_site(month,day);
----- TODO
-- Re-enable this once we are sure the "stats" account exists
-----
-- GRANT ALL ON stats_cvs_group TO stats;
-- GRANT ALL ON stats_project TO stats;
-- GRANT ALL ON stats_subd_pages TO stats;

-- 20010507
ALTER TABLE users ADD COLUMN block_ratings int ;
ALTER TABLE users ALTER COLUMN block_ratings SET DEFAULT 0;

-- 20010509
INSERT INTO frs_filetype VALUES (100,'None');
INSERT INTO frs_processor VALUES (100,'None');

DELETE FROM frs_file
WHERE NOT EXISTS(
	SELECT release_id
	FROM frs_release
	WHERE frs_file.release_id=frs_release.release_id
);

UPDATE frs_file
SET type_id=100
WHERE NOT EXISTS(
	SELECT type_id
	FROM frs_filetype
	WHERE frs_file.type_id=frs_filetype.type_id
)
;

UPDATE frs_file
SET processor_id=100
WHERE NOT EXISTS(
	SELECT processor_id
	FROM frs_processor
	WHERE frs_file.processor_id=frs_processor.processor_id
)
;

ALTER TABLE frs_file ADD CONSTRAINT frsfile_processorid_fk
	FOREIGN KEY (processor_id) REFERENCES frs_processor(processor_id) MATCH FULL;
ALTER TABLE frs_file ADD CONSTRAINT frsfile_releaseid_fk
	FOREIGN KEY (release_id) REFERENCES frs_release(release_id) MATCH FULL;
ALTER TABLE frs_file ADD CONSTRAINT frsfile_typeid_fk
	FOREIGN KEY (type_id) REFERENCES frs_filetype(type_id) MATCH FULL;

ALTER TABLE frs_package ADD CONSTRAINT frspackage_statusid_fk
	FOREIGN KEY (status_id) REFERENCES frs_status(status_id) MATCH FULL;
ALTER TABLE frs_package ADD CONSTRAINT frspackage_groupid_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;

ALTER TABLE frs_release ADD CONSTRAINT frsrelease_packageid_fk
	FOREIGN KEY (package_id) REFERENCES frs_package(package_id) MATCH FULL;
ALTER TABLE frs_release ADD CONSTRAINT frsrelease_releasedby_fk
	FOREIGN KEY (released_by) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE frs_release ADD CONSTRAINT frsrelease_statusid_fk
	FOREIGN KEY (status_id) REFERENCES frs_status(status_id) MATCH FULL;

ALTER TABLE artifact_group_list ADD COLUMN status_timeout integer;
UPDATE artifact_group_list SET status_timeout='1209600' WHERE status_timeout is NULL;

INSERT INTO artifact_status VALUES('4','Pending');

-- 20010511
CREATE TABLE user_metric_history(
month int not null,
day  int not null,
user_id int not null,
ranking int not null,
metric float not null);

---- From now on, everything comes from Debian-SF

-- Get rid of another dead column
ALTER TABLE user_preferences RENAME TO old_user_preferences ;
DROP INDEX user_pref_user_id ;

CREATE TABLE "user_preferences" (
	"user_id" integer DEFAULT '0' NOT NULL,
	"preference_name" character varying(20),
	"preference_value" text,
	"set_date" integer DEFAULT '0' NOT NULL
);

INSERT INTO user_preferences
SELECT user_id, preference_name, preference_value, set_date
FROM old_user_preferences ;

DROP TABLE old_user_preferences ;

CREATE INDEX "user_pref_user_id" on "user_preferences" using btree ( "user_id" "int4_ops" );

-- Fix some hostnames
UPDATE groups SET unix_box = 'shell', cvs_box = 'cvs' ;
UPDATE users SET unix_box = 'shell' ;

-- Drop a few indexes
DROP INDEX frs_dlstats_group_agg_day ;
DROP INDEX frs_file_name ;
DROP INDEX frs_file_processor ;
DROP INDEX frs_file_release_id ;
DROP INDEX frs_file_type ;
DROP INDEX frs_release_by ;
DROP INDEX frs_release_date ;
DROP INDEX frs_release_package ;
DROP INDEX frsdlstatsgroupagg_day_dls ;
DROP INDEX ftpdl_day ;
DROP INDEX group_id_idx ;
DROP INDEX httpdl_day ;
DROP INDEX idx_users_username ;
DROP INDEX stats_agr_tmp_fid ;
DROP INDEX stats_agr_tmp_gid ;

-- Add a few missing tables
CREATE TABLE "cache_store" (
	"name" character varying(255) NOT NULL,
	"data" text,
	"indate" integer DEFAULT 0 NOT NULL,
	Constraint "cache_store_pkey" Primary Key ("name")
);

CREATE TABLE "foundry_project_downloads_agg" (
	"foundry_id" integer,
	"downloads" integer,
	"group_id" integer,
	"group_name" character varying(40),
	"unix_group_name" character varying(30)
);

CREATE TABLE "foundry_project_rankings_agg" (
	"foundry_id" integer,
	"group_id" integer,
	"group_name" character varying(40),
	"unix_group_name" character varying(30),
	"ranking" integer,
	"percentile" double precision
);

CREATE TABLE "stats_project_all" (
	"group_id" integer,
	"developers" integer,
	"group_ranking" integer,
	"group_metric" double precision,
	"logo_showings" integer,
	"downloads" integer,
	"site_views" integer,
	"subdomain_views" integer,
	"page_views" integer,
	"msg_posted" integer,
	"msg_uniq_auth" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);

CREATE TABLE "stats_project_developers_last30" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"developers" integer
);

CREATE TABLE "stats_project_last_30" (
	"month" integer,
	"day" integer,
	"group_id" integer,
	"developers" integer,
	"group_ranking" integer,
	"group_metric" double precision,
	"logo_showings" integer,
	"downloads" integer,
	"site_views" integer,
	"subdomain_views" integer,
	"page_views" integer,
	"filereleases" integer,
	"msg_posted" integer,
	"msg_uniq_auth" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);

CREATE TABLE "stats_project_months" (
	"month" integer,
	"group_id" integer,
	"developers" integer,
	"group_ranking" integer,
	"group_metric" double precision,
	"logo_showings" integer,
	"downloads" integer,
	"site_views" integer,
	"subdomain_views" integer,
	"page_views" integer,
	"file_releases" integer,
	"msg_posted" integer,
	"msg_uniq_auth" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);

CREATE TABLE "stats_site_all" (
	"site_page_views" integer,
	"downloads" integer,
	"subdomain_views" integer,
	"msg_posted" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);

CREATE TABLE "stats_site_last_30" (
	"month" integer,
	"day" integer,
	"site_page_views" integer,
	"downloads" integer,
	"subdomain_views" integer,
	"msg_posted" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);

CREATE TABLE "stats_site_months" (
	"month" integer,
	"site_page_views" integer,
	"downloads" integer,
	"subdomain_views" integer,
	"msg_posted" integer,
	"bugs_opened" integer,
	"bugs_closed" integer,
	"support_opened" integer,
	"support_closed" integer,
	"patches_opened" integer,
	"patches_closed" integer,
	"artifacts_opened" integer,
	"artifacts_closed" integer,
	"tasks_opened" integer,
	"tasks_closed" integer,
	"help_requests" integer,
	"cvs_checkouts" integer,
	"cvs_commits" integer,
	"cvs_adds" integer
);

CREATE TABLE "stats_site_pages_by_day" (
	"month" integer,
	"day" integer,
	"site_page_views" integer
);

CREATE TABLE "stats_site_pages_by_month" (
	"month" integer,
	"site_page_views" integer
);

-- Add/alter a few columns
ALTER TABLE frs_dlstats_group_agg ADD COLUMN month integer;
ALTER TABLE frs_dlstats_group_agg ALTER COLUMN month SET DEFAULT '0';
ALTER TABLE project_weekly_metric ALTER COLUMN group_id SET DEFAULT '0';

-- Drop an unused table
DROP TABLE intel_agreement ;

-- (Re-)create indexes
CREATE INDEX frs_file_release_id ON frs_file USING btree (release_id);
CREATE INDEX frs_release_package ON frs_release USING btree (package_id);
CREATE INDEX frsdlfiletotal_fileid ON frs_dlstats_filetotal_agg USING btree (file_id);
CREATE INDEX frsdlgroup_groupid ON frs_dlstats_group_agg USING btree (group_id);
CREATE INDEX frsdlgroup_month_day_groupid ON frs_dlstats_group_agg USING btree ("month", "day", group_id);
CREATE INDEX frsdlgrouptotal_groupid ON frs_dlstats_grouptotal_agg USING btree (group_id);
-- CREATE INDEX project_metric_group ON project_metric USING btree (group_id);
-- CREATE INDEX project_metric_weekly_group ON project_weekly_metric USING btree (group_id);
-- CREATE INDEX projectweeklymetric_ranking ON project_weekly_metric USING btree (ranking);
CREATE INDEX statsproject30_groupid ON stats_project_last_30 USING btree (group_id);
CREATE INDEX statsprojectall_groupid ON stats_project_all USING btree (group_id);
CREATE INDEX statsprojectmonths_groupid ON stats_project_months USING btree (group_id);
CREATE INDEX statsprojectmonths_groupid_mont ON stats_project_months USING btree (group_id, "month");
CREATE INDEX statssitelast30_month_day ON stats_site_last_30 USING btree ("month", "day");
CREATE INDEX statssitemonths_month ON stats_site_months USING btree ("month");
CREATE INDEX statssitepagesbyday_month_day ON stats_site_pages_by_day USING btree ("month", "day");
CREATE INDEX troveagg_trovecatid_ranking ON trove_agg USING btree (trove_cat_id, ranking);
CREATE INDEX user_metric_history_date_userid ON user_metric_history USING btree ("month", "day", user_id);

CREATE  INDEX "foundryprojdlsagg_foundryid_dls" on "foundry_project_downloads_agg" using btree ( "foundry_id" "int4_ops", "downloads" "int4_ops" );
CREATE  INDEX "foundryprojectrankingsagg_found" on "foundry_project_rankings_agg" using btree ( "foundry_id" "int4_ops", "ranking" "int4_ops" );

CREATE UNIQUE INDEX frsdlfileagg_oid ON frs_dlstats_file_agg USING btree (oid);
CREATE UNIQUE INDEX statsagglogobygrp_oid ON stats_agg_logo_by_group USING btree (oid);
CREATE UNIQUE INDEX statsaggsitebygrp_oid ON stats_agg_site_by_group USING btree (oid);
CREATE UNIQUE INDEX statscvsgrp_oid ON stats_cvs_group USING btree (oid);
CREATE UNIQUE INDEX statsproject_oid ON stats_project USING btree (oid);
CREATE UNIQUE INDEX statsprojectdevelop_oid ON stats_project_developers USING btree (oid);
CREATE UNIQUE INDEX statsprojectmetric_oid ON stats_project_metric USING btree (oid);
CREATE UNIQUE INDEX statssite_oid ON stats_site USING btree (oid);
CREATE UNIQUE INDEX statssitepgsbyday_oid ON stats_site_pages_by_day USING btree (oid);
CREATE UNIQUE INDEX statssubdpages_oid ON stats_subd_pages USING btree (oid);

-- Add two new themes
INSERT INTO themes (dirname, fullname) VALUES ('debian', 'Debian') ;
INSERT INTO themes (dirname, fullname) VALUES ('savannah', 'Savannah') ;

-- Constraints
ALTER TABLE project_group_list ADD CONSTRAINT project_group_list_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;
ALTER TABLE user_group ADD CONSTRAINT user_group_group_id_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL ;
