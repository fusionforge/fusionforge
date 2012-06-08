DROP TABLE user_group;
DROP TABLE role_setting ;
DROP TABLE role;

DROP VIEW artifact_group_list_vw;
CREATE VIEW artifact_group_list_vw AS
  SELECT agl.group_artifact_id, agl.group_id, agl.name, agl.description,
    agl.email_all_updates, agl.email_address,
    agl.due_period, agl.submit_instructions, agl.browse_instructions,
    agl.browse_list, agl.datatype, agl.status_timeout, agl.custom_status_field,
    agl.custom_renderer, aca.count, aca.open_count
  FROM artifact_group_list agl
  LEFT JOIN artifact_counts_agg aca USING (group_artifact_id);

ALTER TABLE artifact_group_list DROP COLUMN is_public;
ALTER TABLE artifact_group_list DROP COLUMN allow_anon;

DROP VIEW forum_group_list_vw;
CREATE VIEW forum_group_list_vw AS
SELECT forum_group_list.group_forum_id, forum_group_list.group_id, forum_group_list.forum_name, forum_group_list.description, forum_group_list.send_all_posts_to, forum_agg_msg_count.count AS total, (SELECT max(forum.post_date) AS recent FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id)) AS recent, (SELECT count(*) AS count FROM (SELECT forum.thread_id FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id) GROUP BY forum.thread_id) tmp) AS threads FROM (forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id));

ALTER TABLE forum_group_list DROP COLUMN is_public;
ALTER TABLE forum_group_list DROP COLUMN allow_anonymous;
ALTER TABLE forum_group_list DROP COLUMN moderation_level;

DROP VIEW project_group_list_vw;
ALTER TABLE project_group_list DROP COLUMN is_public;
CREATE VIEW project_group_list_vw AS SELECT * FROM project_group_list NATURAL JOIN project_counts_agg;

ALTER TABLE groups DROP COLUMN is_public;
ALTER TABLE groups DROP COLUMN enable_anonscm;
-- ALTER TABLE groups DROP COLUMN enable_pserver;

DROP SEQUENCE foundry_news_pk_seq;
