-- unix accounts: add a group for SCM read-only access
--                + rename scm_xxx to xxx_scmrw

UPDATE nss_groups
  SET name=substr(nss_groups.name, 5)||'_scmrw'
  WHERE gid > 50000;

INSERT INTO nss_groups
  SELECT group_id,name||'_scmro',group_id+100000
  FROM nss_groups
  WHERE gid < 50000;

-- Next: 20150403-scmro.php: regen nss_usergroups
