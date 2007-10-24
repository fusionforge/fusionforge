DROP VIEW artifactperm_artgrouplist_vw ;
DROP VIEW artifactperm_user_vw ;
DROP TABLE artifact_perm ;

CREATE VIEW artifact_perm AS
       SELECT
	0 AS id,
	role_setting.ref_id AS group_artifact_id,
	user_group.user_id,
	role_setting.value::int AS perm_level
       FROM role_setting, user_group
       WHERE user_group.role_id = role_setting.role_id
         AND role_setting.section_name='tracker';

CREATE VIEW artifactperm_artgrouplist_vw AS
       SELECT
	agl.group_artifact_id, agl.name, agl.description,
	agl.group_id, ap.user_id, ap.perm_level
       FROM artifact_perm ap, artifact_group_list agl
       WHERE (ap.group_artifact_id = agl.group_artifact_id) ;

CREATE VIEW artifactperm_user_vw AS
       SELECT
	ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level,
	users.user_name, users.realname
       FROM artifact_perm ap, users
       WHERE (users.user_id = ap.user_id) ;
