CREATE OR REPLACE VIEW activity_vw AS
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
frsp.group_id, 'frsrelease'::text as section,frsp.package_id as ref_id,
frsr.release_id as subref_id, frsr.name AS description, frsr.release_date AS activity_date,
u.user_id, u.user_name, u.realname FROM frs_package frsp JOIN frs_release frsr USING (package_id), users u WHERE
u.user_id=frsr.released_by
UNION
SELECT
fgl.group_id, 'forumpost'::text as section,fgl.group_forum_id as ref_id, forum.msg_id
as subref_id, forum.subject AS description, forum.post_date AS activity_date, u.user_id,
u.user_name, u.realname FROM forum_group_list fgl JOIN forum USING (group_forum_id), users u WHERE
u.user_id=forum.posted_by AND fgl.group_forum_id NOT IN (SELECT nb.forum_id FROM news_bytes nb)
UNION
SELECT group_id, 'docmannew'::text AS section, doc_group AS ref_id, docid AS subref_id,
filename AS description, createdate AS activity_date, created_by as user_id,
user_name, realname FROM docdata_vw
UNION
SELECT group_id, 'docmanupdate'::text AS section, doc_group AS ref_id, docid AS subref_id,
filename AS description, updatedate AS activity_date, created_by as user_id,
user_name, realname FROM docdata_vw
UNION
SELECT doc_groups.group_id, 'docgroupnew'::text AS section, doc_groups.parent_doc_group AS ref_id, doc_groups.doc_group AS subref_id,
doc_groups.groupname AS description,  doc_groups.createdate AS activity_date, doc_groups.created_by as user_id,
users.user_name, users.realname FROM doc_groups, users WHERE doc_groups.created_by = users.user_id
UNION
SELECT news_bytes.group_id,'news' AS section,news_bytes.id AS ref_id,news_bytes.forum_id AS subref_id,
news_bytes.summary AS description, news_bytes.post_date AS activity_date, u.user_id, u.user_name, u.realname
FROM news_bytes, users u WHERE u.user_id = news_bytes.submitted_by
UNION
SELECT pgl.group_id, 'taskopen'::text AS section, p.group_project_id AS ref_id,
p.project_task_id AS subref_id, p.summary AS description, p.last_modified_date AS activity_date,
u.user_id, u.user_name, u.realname
FROM project_task p
JOIN project_group_list pgl USING (group_project_id), users u
WHERE u.user_id = p.created_by AND p.status_id = 1
UNION
SELECT pgl.group_id, 'taskclose'::text AS section, p.group_project_id AS ref_id,p.project_task_id AS subref_id,
p.summary AS description, p.last_modified_date AS activity_date, u.user_id,
u.user_name, u.realname
FROM project_task p
JOIN project_group_list pgl USING (group_project_id), users u
WHERE u.user_id = p.created_by AND p.status_id = 2
UNION
SELECT pgl.group_id, 'taskdelete'::text AS section, p.group_project_id AS ref_id,
p.project_task_id AS subref_id, p.summary AS description, p.last_modified_date AS activity_date,
u.user_id, u.user_name, u.realname
FROM project_task p
JOIN project_group_list pgl USING (group_project_id), users u
WHERE u.user_id = p.created_by AND p.status_id = 3;
