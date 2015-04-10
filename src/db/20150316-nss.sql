-- unix accounts: fix name conflicts between per-user groups and project groups [#660]

-- drop 'gid'
DROP VIEW nss_passwd;
CREATE VIEW nss_passwd AS
  SELECT users.unix_uid AS uid,
    users.user_name AS login,
    users.unix_pw AS passwd,
    users.realname AS gecos,
    users.shell,
    users.user_name AS homedir,
    users.status
  FROM users
  WHERE users.unix_status = 'A';
ALTER TABLE users DROP "unix_gid";

-- only list project gids, users share a default gid (cf. 'users_default_gid')
DELETE FROM nss_groups WHERE group_id=0;
ALTER TABLE nss_groups DROP "user_id";
ALTER TABLE nss_groups ADD CONSTRAINT "gid_pk" PRIMARY KEY (gid);

-- Next: 20150317-nss.sql:: GRANT SELECT ON nss_groups TO ${database_user}_nss;
