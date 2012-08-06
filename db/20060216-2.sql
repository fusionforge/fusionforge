CREATE VIEW activity_vw AS
SELECT
agl.group_id, 'trackeropen'::text AS section, agl.group_artifact_id AS ref_id,
a.artifact_id as subref_id, a.summary as description, a.open_date AS activity_date, u.user_id, u.user_name, u.realname
FROM artifact_group_list agl JOIN artifact a using (group_artifact_id),
users u WHERE u.user_id=a.submitted_by
UNION
SELECT
agl.group_id, 'trackerclose'::text AS section, agl.group_artifact_id AS ref_id,
a.artifact_id as subref_id, a.summary as description, a.close_date AS activity_date, u.user_id, u.user_name, u.realname
FROM artifact_group_list agl JOIN artifact a using (group_artifact_id), users u WHERE u.user_id=a.assigned_to
--actually should join against
AND a.close_date > 0
UNION
SELECT
agl.group_id, 'commit'::text AS section, agl.group_artifact_id AS ref_id,
a.artifact_id as subref_id, pcdm.log_text AS description, pcdm.cvs_date AS activity_date, u.user_id, u.user_name, u.realname
FROM artifact_group_list agl JOIN artifact a using (group_artifact_id),
plugin_cvstracker_data_master pcdm, plugin_cvstracker_data_artifact pcda, users u
WHERE pcdm.holder_id=pcda.id
AND pcda.group_artifact_id=a.artifact_id
AND u.user_name=pcdm.author
UNION
SELECT
frsp.group_id, 'frsrelease'::text as section,frsp.package_id as ref_id,
frsr.release_id as subref_id, frsr.name AS description, frsr.release_date AS activity_date,
u.user_id, u.user_name, u.realname FROM frs_package frsp JOIN frs_release frsr USING (package_id), users u WHERE
u.user_id=frsr.released_by
UNION
SELECT
fgl.group_id, 'forumpost'::text as section,fgl.group_forum_id as ref_id, forum.msg_id
as subref_id, forum.subject AS description, forum.post_date AS activity_date, u.user_id,
u.user_name, u.realname FROM forum_group_list fgl JOIN forum USING (group_forum_id), users u WHERE
u.user_id=forum.posted_by
;

CREATE TABLE group_activity_monitor (
group_id int not null CONSTRAINT group_id REFERENCES groups(group_id) ON DELETE CASCADE,
user_id int NOT NULL CONSTRAINT userid_fk REFERENCES users(user_id),
filter text,
PRIMARY KEY (group_id,user_id)
);

CREATE RULE groupactivity_userdelete_rule AS ON UPDATE TO USERS DO
	DELETE FROM group_activity_monitor WHERE user_id =(CASE WHEN NEW.status='D'
	THEN NEW.user_id ELSE 0 END);
