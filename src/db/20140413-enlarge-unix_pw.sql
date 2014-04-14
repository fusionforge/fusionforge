-- Enlarge your pen^W^Wthe unix_pw field so it can store crypt(3)-compatible SHA-512 hashes

-- Need to recreate the related views :/ (1)
DROP VIEW nss_shadow;
DROP VIEW nss_passwd;

-- Increase field size (40->128)
ALTER TABLE users ALTER unix_pw TYPE character varying(128);

-- Need to recreate the related views :/ (2)
CREATE VIEW nss_shadow AS SELECT user_name AS login, unix_pw AS passwd, 'n'::bpchar AS expired, 'n'::bpchar AS pwchange
  FROM users WHERE unix_status = 'A';
CREATE VIEW nss_passwd AS SELECT unix_uid AS uid, unix_gid AS gid, user_name AS login, unix_pw AS passwd, realname AS gecos, shell, user_name AS homedir, status
  FROM users WHERE unix_status = 'A';
