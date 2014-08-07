CREATE VIEW nss_passwd AS
	SELECT unix_uid+20000 AS uid,
		unix_uid+20000 AS gid,
		user_name AS login,
		unix_pw AS passwd,
		realname AS gecos,
		shell,
		'/var/lib/gforge/chroot/home/users/' || user_name AS homedir
	FROM users
	WHERE status='A'
	UNION
	SELECT group_id+50000 AS uid,
		group_id+20000 AS gid,
		'anoncvs_' || unix_group_name AS login,
		CHAR(1) 'x' AS passwd,
		group_name AS gecos,
		'/bin/false' AS shell,
		'/var/lib/gforge/chroot/home/groups' || group_name AS homedir
	FROM groups
	UNION
	SELECT 9999 AS uid,
		9999 AS gid,
		'gforge_scm' AS login,
		CHAR(1) 'x' AS passwd,
		'Gforge SCM user' AS gecos,
		'/bin/false' AS shell,
		'/var/lib/gforge/chroot/home' AS homedir;

CREATE VIEW nss_shadow AS
	SELECT user_name AS login,
		unix_pw AS passwd,
		CHAR(1) 'n' AS expired,
		CHAR(1) 'n' AS pwchange
	FROM users
	WHERE status='A';

CREATE VIEW nss_groups AS
	SELECT group_id+10000 AS gid,
		unix_group_name AS name,
		group_name AS descr,
		CHAR(1) 'x' AS passwd
	FROM groups
	UNION
	SELECT unix_uid+20000 AS gid,
		user_name AS name,
		lastname AS descr,
		CHAR(1) 'x' AS passwd
	FROM users;

CREATE VIEW nss_usergroups AS
	SELECT group_id+10000 AS gid,
		users.unix_uid+20000 AS uid
	FROM user_group,users
	WHERE user_group.user_id=users.user_id
	UNION
	SELECT unix_uid+20000 AS gid,
		unix_uid+20000 AS uid
	FROM users
--         WHERE group_id!=800    -- drop unused and overfull Debian group
	;
