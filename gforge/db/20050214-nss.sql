-- 
ALTER TABLE users  ADD COLUMN unix_gid INTEGER;
 ALTER TABLE users  ALTER COLUMN unix_gid SET DEFAULT 0;
---ALTER TABLE users  ADD COLUMN register_purpose TEXT;
---ALTER TABLE users  ADD COLUMN register_time INTEGER;
---ALTER TABLE users  ALTER COLUMN register_time SET DEFAULT 0;
-- ALTER TABLE users  RENAME COLUMN user_name TO unix_user_name;
-- Should be done to have the same field name convention in users and groups
---ALTER TABLE groups ADD COLUMN unix_gid INTEGER;
---ALTER TABLE groups ALTER COLUMN unix_gid SET DEFAULT 0;
---ALTER TABLE groups ADD COLUMN unix_status CHARACTER(1);
---ALTER TABLE groups ALTER COLUMN unix_status SET DEFAULT 'A';
---ALTER TABLE groups ADD COLUMN add_date INTEGER;
---ALTER TABLE groups ALTER COLUMN add_date SET DEFAULT 0;
--
UPDATE users SET unix_uid=unix_uid + 20000 WHERE unix_uid!=0;
UPDATE users SET unix_uid=user_id  + 20000 WHERE unix_uid=0;
UPDATE users SET unix_gid=unix_uid;
-- Prepare TABLE for user registration
---UPDATE users SET register_time=add_date;
--
---UPDATE groups SET unix_gid=group_id + 10000;
---UPDATE groups SET add_date=register_time;
---UPDATE groups SET unix_status='A';
--
-- List users an groups
--
--SELECT unix_group_name, unix_gid, status, unix_status FROM groups;
--SELECT unix_uid, unix_gid, user_name, unix_pw, realname, shell, status, unix_status FROM users;
--
-- Insert scm_<project_account> in the users table
-- These users are used by CVS pserver
-- We can get rid of these users changing lock dir place and making it world writeable.
--DELETE FROM users WHERE user_name LIKE 'scm_%';
--DELETE FROM users WHERE user_name LIKE 'anoncvs_%';
--INSERT INTO users ( unix_uid, unix_gid, user_name, unix_pw, realname, shell, status, unix_status) 
--        SELECT 
--		unix_gid+40000 ,
--		unix_gid ,
--		'scm_' || unix_group_name,
--		CHAR(1) 'x',
--		unix_group_name,
--		'/bin/false',
--		'N',
--		'A'
--	FROM groups
--	WHERE unix_status='A'
--	AND status='A'
--	AND enable_anonscm = 1;
--	
-- Dummy user
--
--DELETE FROM users WHERE user_name='dummy';
--INSERT INTO users ( unix_uid, unix_gid, user_name, unix_pw, realname, shell, status, unix_status)
--	VALUES (9999,9999,'dummy','x','Dummy User','/bin/false','N','A');
--
--
-- Passwd view
--
DROP VIEW nss_passwd;
CREATE VIEW nss_passwd AS
	SELECT
		unix_uid AS uid,
		unix_gid AS gid,
		user_name AS login,
		unix_pw AS passwd,
		realname AS gecos,
		shell,
		user_name AS homedir,
		status
	FROM users
	WHERE unix_status='A';
--
-- Shadow view (for future use)
--
DROP VIEW nss_shadow;
CREATE VIEW nss_shadow AS
	SELECT
		user_name AS login,
		unix_pw AS passwd,
		CHAR(1) 'n' AS expired,
		CHAR(1) 'n' AS pwchange
	FROM users
	WHERE unix_status='A';
--
-- Group Table
--
DROP VIEW nss_groups;
CREATE TABLE nss_groups AS
	SELECT 0 AS user_id, group_id,unix_group_name AS name, group_id + 10000 AS gid
	FROM groups;
--
-- Insert users group ids in group table
-- 
--DELETE FROM groups WHERE unix_group_name IN (SELECT user_name FROM users);
--INSERT INTO groups (unix_group_name, unix_gid, status, unix_status)
--	SELECT
--		user_name AS name,
--		unix_gid AS gid,
--		'N',
--		'A'
--	FROM users
--	WHERE unix_status='A'
--	AND status = 'A';
INSERT INTO nss_groups (user_id,group_id,name, gid)
	SELECT user_id,0,user_name, unix_gid
	FROM users
	WHERE unix_status='A'
	AND status = 'A';
--
-- Insert scm group ids in group table
--
--DELETE FROM groups WHERE unix_group_name LIKE 'scm_%';
--INSERT INTO groups (unix_group_name, unix_gid, status, unix_status)
--	SELECT 
--		'scm_' || unix_group_name,
--		group_id + 50000,
--		'N',
--		'A'
--	FROM groups
--	WHERE unix_status='A'
--	AND status = 'A'
--	AND enable_anonscm = 1;
INSERT INTO nss_groups (user_id,group_id,name, gid)
	SELECT 0,group_id,'scm_' || unix_group_name, group_id + 50000
	FROM groups
	WHERE status = 'A'
	AND enable_anonscm = 1;
--
-- Groups view
--
--DROP VIEW nss_groups;
--CREATE VIEW nss_groups AS
--	SELECT
--		unix_group_name AS name,
--		CHAR(1) 'x' AS passwd,
--		unix_gid AS gid,
--		status
--	FROM groups
--	WHERE unix_status='A';
--
-- User_Group view
--
DROP VIEW nss_usergroups ;
CREATE TABLE nss_usergroups AS (
	SELECT
		users.unix_uid AS uid,
		groups.group_id + 10000 AS gid,
		users.user_id AS user_id,
		groups.group_id AS group_id,
		users.user_name AS user_name,
		groups.unix_group_name AS unix_group_name
	FROM users,groups,user_group
	WHERE 
		users.user_id=user_group.user_id
	AND
		groups.group_id=user_group.group_id
	AND
		groups.status = 'A'
	AND
		users.unix_status='A'
	AND
		users.status = 'A'
	UNION
	SELECT
		users.unix_uid AS uid,
		groups.group_id + 50000 AS gid,
		users.user_id AS user_id,
		groups.group_id AS group_id,
		users.user_name AS user_name,
		'scm_' || groups.unix_group_name AS unix_group_name
	FROM users,groups,user_group
	WHERE 
		users.user_id=user_group.user_id
	AND
		groups.group_id=user_group.group_id
	AND
		groups.status = 'A'
	AND
		users.unix_status='A'
	AND
		users.status = 'A'
	AND
		groups.enable_anonscm = 1);
--
-- Grants
--
GRANT SELECT ON nss_passwd TO gforge_nss;
GRANT SELECT ON nss_groups TO gforge_nss;
GRANT SELECT ON nss_usergroups TO gforge_nss;
--


