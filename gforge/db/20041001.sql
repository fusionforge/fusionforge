CREATE VIEW nss_passwd AS
	SELECT unix_uid+20000 AS uid,
		unix_uid+20000 AS gid,
		user_name AS login,
		CHAR(1) 'x' AS passwd,
		realname AS gecos,
		shell,
		'/var/lib/gforge/chroot/home/users/' || user_name AS homedir
	FROM users
	WHERE unix_status='A';

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
	CHAR(1) 'x' AS passwd
	FROM groups;


CREATE VIEW nss_usergroups AS
	SELECT group_id+10000 AS gid,
		user_id+20000 AS uid
	FROM user_group
--	WHERE group_id!=800	-- drop unused and overfull Debian group
;

GRANT SELECT ON nss_passwd, nss_groups, nss_usergroups TO gforge_nss;
