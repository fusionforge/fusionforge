DROP VIEW nss_passwd;
DROP VIEW activity_vw;
DROP VIEW docdata_vw;
DROP VIEW project_task_vw;
DROP VIEW forum_pending_user_vw;
DROP VIEW forum_user_vw;
DROP VIEW artifact_vw;
DROP VIEW project_message_user_vw;
DROP VIEW project_history_user_vw;
DROP VIEW artifact_message_user_vw;
DROP VIEW artifact_file_user_vw;

ALTER TABLE users ALTER COLUMN realname TYPE VARCHAR(121);

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

CREATE VIEW artifact_file_user_vw AS
  SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname
  FROM artifact_file af, users WHERE (af.submitted_by = users.user_id);

CREATE VIEW artifact_message_user_vw AS
  SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname
  FROM artifact_message am, users WHERE (am.submitted_by = users.user_id);

CREATE VIEW project_history_user_vw AS
  SELECT users.realname, users.email, users.user_name, project_history.project_history_id, project_history.project_task_id, project_history.field_name, project_history.old_value, project_history.mod_by, project_history.mod_date
  FROM users, project_history WHERE (project_history.mod_by = users.user_id);


CREATE VIEW project_message_user_vw AS
  SELECT users.realname, users.email, users.user_name, project_messages.project_message_id, project_messages.project_task_id, project_messages.body, project_messages.posted_by, project_messages.postdate
  FROM users, project_messages WHERE (project_messages.posted_by = users.user_id);

CREATE VIEW artifact_vw AS
  SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact.last_modified_date
  FROM users u, users u2, artifact_status, artifact
  WHERE (((artifact.assigned_to = u.user_id) AND (artifact.submitted_by = u2.user_id)) AND (artifact.status_id = artifact_status.id));

CREATE VIEW forum_user_vw AS
  SELECT forum.msg_id, forum.group_forum_id, forum.posted_by, forum.subject, forum.body, forum.post_date, forum.is_followup_to, forum.thread_id, forum.has_followups, forum.most_recent_date, users.user_name, users.realname
  FROM forum, users WHERE (forum.posted_by = users.user_id);

CREATE VIEW forum_pending_user_vw AS
  SELECT forum_pending_messages.msg_id, forum_pending_messages.group_forum_id, forum_pending_messages.posted_by, forum_pending_messages.subject, forum_pending_messages.body, forum_pending_messages.post_date, forum_pending_messages.is_followup_to, forum_pending_messages.thread_id, forum_pending_messages.has_followups, forum_pending_messages.most_recent_date, users.user_name, users.realname
  FROM forum_pending_messages, users
  WHERE (forum_pending_messages.posted_by = users.user_id);

CREATE VIEW project_task_vw AS
  SELECT project_task.*, project_category.category_name, project_status.status_name, users.user_name, users.realname, project_task_external_order.external_id
  FROM project_task
  FULL JOIN project_category ON (project_category.category_id = project_task.category_id)
  FULL JOIN users ON (users.user_id = project_task.created_by)
  FULL JOIN project_task_external_order ON (project_task_external_order.project_task_id = project_task.project_task_id)
  NATURAL JOIN project_status;

CREATE VIEW docdata_vw AS
  SELECT users.user_name, users.realname, users.email, d.group_id, d.docid, d.stateid, d.title, d.updatedate, d.createdate, d.created_by, d.doc_group, d.description, docman_dlstats_doctotal_agg.downloads AS download, d.filename, d.filetype, d.filesize, d.reserved, d.reserved_by, d.locked, d.locked_by, d.lockdate, doc_states.name AS state_name, doc_groups.groupname AS group_name
  FROM doc_data d, users, doc_groups, doc_states, docman_dlstats_doctotal_agg
  WHERE d.created_by = users.user_id and doc_groups.doc_group = d.doc_group and doc_states.stateid = d.stateid and d.docid = docman_dlstats_doctotal_agg.docid;

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
    frsr.release_id as subref_id, frsp.name::text||' - '||frsr.name::text AS description, frsr.release_date AS activity_date,
    u.user_id, u.user_name, u.realname FROM frs_package frsp JOIN frs_release frsr USING (package_id), users u WHERE
    u.user_id=frsr.released_by
  UNION
  SELECT
    fgl.group_id, 'forumpost'::text as section,fgl.group_forum_id as ref_id, forum.msg_id
    as subref_id, forum.subject AS description, forum.post_date AS activity_date, u.user_id,
    u.user_name, u.realname FROM forum_group_list fgl JOIN forum USING (group_forum_id), users u WHERE
    u.user_id=forum.posted_by
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
    p.project_task_id AS subref_id,	p.summary AS description, p.last_modified_date AS activity_date,
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
