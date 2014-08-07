--
UPDATE users SET unix_uid=user_id  + 20000 WHERE unix_uid=0;
UPDATE users SET unix_gid=unix_uid WHERE unix_gid=0;
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
-- Extracted from group information
--
DROP TABLE nss_groups;
CREATE TABLE nss_groups AS
	SELECT 0 AS user_id, group_id,unix_group_name AS name, group_id + 10000 AS gid
	FROM groups;
--
-- Insert users group ids in nss_group table
--
INSERT INTO nss_groups (user_id,group_id,name, gid)
	SELECT user_id,0,user_name, unix_gid
	FROM users
	WHERE unix_status='A'
	AND status = 'A';
--
-- Insert scm group ids in nss_group table
--
INSERT INTO nss_groups (user_id,group_id,name, gid)
	SELECT 0,group_id,'scm_' || unix_group_name, group_id + 50000
	FROM groups
	WHERE status = 'A';
--
-- User_Group Table
--
DROP TABLE nss_usergroups ;
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
		user_group.cvs_flags > 0);
--
-- Grants
--
GRANT SELECT ON nss_passwd TO gforge_nss;
GRANT SELECT ON nss_groups TO gforge_nss;
GRANT SELECT ON nss_usergroups TO gforge_nss;
--


