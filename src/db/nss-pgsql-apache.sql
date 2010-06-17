-- This view solves the problem of apache user not being able to access private repositories under
-- nss-pgsql.
-- You MUST replace the markers with your apache user name & user ID

CREATE OR REPLACE VIEW "public"."nss_usergroups" (
    uid,
    gid,
    user_id,
    group_id,
    user_name)
AS
SELECT users.unix_uid AS uid, groups.unix_gid AS gid, users.user_id,
    groups.group_id, users.user_name
FROM users, groups, user_group
WHERE (((((users.user_id = user_group.user_id) AND (groups.group_id =
    user_group.group_id)) AND (groups.status = 'A'::bpchar)) AND (users.status
    = 'A'::bpchar)) AND ((user_group.cvs_flags = 0) OR (user_group.cvs_flags = 1)))
UNION
SELECT (INSERT APACHE USER ID HERE!!) AS uid, groups.unix_gid AS gid, NULL::"unknown" AS user_id,
    NULL::"unknown" AS group_id, '(INSERT APACHE USER NAME HERE!!)' AS user_name
FROM groups
WHERE ((groups.enable_anonscm = 0) OR (groups.is_public = 0));
