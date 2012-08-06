--
--
--
CREATE SEQUENCE user_unix_id_seq START 20000;
CREATE SEQUENCE group_unix_id_seq START 50000;
UPDATE users SET unix_uid=0,unix_gid=0,shell='/bin/cvssh.pl';
ALTER TABLE users ALTER COLUMN shell SET default '/bin/cvssh.pl';
UPDATE users SET unix_uid=nextval('user_unix_id_seq'),unix_gid=currval('user_unix_id_seq')
	WHERE user_id IN (SELECT user_id FROM user_group);
ALTER TABLE groups ADD COLUMN unix_gid int;
ALTER TABLE groups SET DEFAULT nextval('group_unix_id_seq');
UPDATE groups SET unix_gid=nextval('group_unix_id_seq');

DROP FUNCTION userunixid_func() CASCADE;
CREATE OR REPLACE FUNCTION userunixid_func() RETURNS TRIGGER AS '
DECLARE
	newuser RECORD;
BEGIN
	FOR newuser IN SELECT unix_uid FROM users WHERE user_id=NEW.user_id LOOP
		IF newuser.unix_uid=0 THEN
			UPDATE users SET unix_uid=nextval(''user_unix_id_seq''),
				unix_gid=currval(''user_unix_id_seq'')
				WHERE user_id=NEW.user_id;
		END IF;
	END LOOP;
	RETURN NEW;
END;
' LANGUAGE plpgsql;

CREATE TRIGGER usergroup_insert_userunixid AFTER INSERT ON user_group
        FOR EACH ROW EXECUTE PROCEDURE userunixid_func();

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
	WHERE STATUS='A' AND EXISTS (SELECT user_id
		FROM user_group WHERE user_id=users.user_id AND cvs_flags IN (0,1));

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
	WHERE STATUS='A' AND EXISTS (SELECT user_id
		FROM user_group WHERE user_id=users.user_id AND cvs_flags IN (0,1));
--
-- Group Table
-- Extracted from group information
--
DROP TABLE nss_groups;
DROP VIEW nss_groups;
CREATE VIEW nss_groups AS
--	SELECT user_id,0,user_name AS NAME, unix_gid
--	FROM users
--	WHERE status = 'A' AND EXISTS (SELECT user_id
--		FROM user_group WHERE user_id=users.user_id AND cvs_flags IN (0,1));
--	UNION
	SELECT 0 AS user_id, group_id,unix_group_name AS name, unix_gid AS gid
	FROM groups;
--
-- User_Group Table
--
DROP TABLE nss_usergroups ;
DROP VIEW nss_usergroups;
CREATE VIEW nss_usergroups AS
	SELECT
		users.unix_uid AS uid,
		groups.unix_gid AS gid,
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
		users.status = 'A'
	AND user_group.cvs_flags IN (0,1);

--create index nssusergroup_gidusername ON nss_usergroups(gid,user_name);
--create index nssusergroup_usernamegid ON nss_usergroups(user_name,gid);
create index users_uid on users(unix_uid);
create index users_gid on users(unix_gid);
create index groups_gid on groups (unix_gid);
grant select on nss_passwd to cvsuser;
grant select on nss_usergroups to cvsuser;
grant select on nss_groups to cvsuser;
grant select on nss_shadow to cvsuser;
