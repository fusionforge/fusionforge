DROP VIEW IF EXISTS users_with_cvsflags_vw;
DROP VIEW IF EXISTS groups_with_svn_vw;
DROP TABLE user_group;
DROP SEQUENCE user_group_pk_seq;
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

CREATE OR REPLACE FUNCTION tmp_migrate_tracker_allow_anon_to_rbac () RETURNS integer AS $$
DECLARE
	agl artifact_group_list%ROWTYPE ;
	tid integer := 0;
BEGIN
	UPDATE pfo_role_setting SET perm_val = perm_val|8 WHERE perm_val > 0 AND role_id != 1 AND (section_name = 'tracker' OR section_name = 'new_tracker');
	FOR agl IN SELECT * FROM artifact_group_list WHERE allow_anon = 1
	LOOP
		tid = agl.group_artifact_id ;
		UPDATE pfo_role_setting SET perm_val = perm_val|8 WHERE perm_val > 0 AND role_id = 1 AND section_name = 'tracker' AND ref_id = tid;
	END LOOP ;
	RETURN 0;
END ;
$$ LANGUAGE plpgsql ;

SELECT tmp_migrate_tracker_allow_anon_to_rbac ();

DROP FUNCTION tmp_migrate_tracker_allow_anon_to_rbac () ;

ALTER TABLE artifact_group_list DROP COLUMN is_public;
ALTER TABLE artifact_group_list DROP COLUMN allow_anon;

DROP VIEW forum_group_list_vw;
CREATE VIEW forum_group_list_vw AS
SELECT forum_group_list.group_forum_id, forum_group_list.group_id, forum_group_list.forum_name, forum_group_list.description, forum_group_list.send_all_posts_to, forum_agg_msg_count.count AS total, (SELECT max(forum.post_date) AS recent FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id)) AS recent, (SELECT count(*) AS count FROM (SELECT forum.thread_id FROM forum WHERE (forum.group_forum_id = forum_group_list.group_forum_id) GROUP BY forum.thread_id) tmp) AS threads FROM (forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id));

DROP TRIGGER IF EXISTS fmsg_agg_trig ON forum;
DROP TRIGGER IF EXISTS fora_agg_trig ON forum_group_list;

CREATE OR REPLACE FUNCTION project_sums () RETURNS TRIGGER AS $$
DECLARE
	num integer;
	curr_group integer;
	found integer;
BEGIN
	---
	--- Get number of things this group has now
	---
	IF TG_ARGV[0]='surv' THEN
		IF TG_OP='DELETE' THEN
			SELECT INTO num count(*) FROM surveys WHERE OLD.group_id=group_id AND is_active=1;
			curr_group := OLD.group_id;
		ELSE
			SELECT INTO num count(*) FROM surveys WHERE NEW.group_id=group_id AND is_active=1;
			curr_group := NEW.group_id;
		END IF;
	END IF;
	IF TG_ARGV[0]='mail' THEN
		IF TG_OP='DELETE' THEN
			SELECT INTO num count(*) FROM mail_group_list WHERE OLD.group_id=group_id AND is_public=1;
			curr_group := OLD.group_id;
		ELSE
			SELECT INTO num count(*) FROM mail_group_list WHERE NEW.group_id=group_id AND is_public=1;
			curr_group := NEW.group_id;
		END IF;
	END IF;
	---
	--- See if this group already has a row in project_sums_agg for these things
	---
	SELECT INTO found count(group_id) FROM project_sums_agg WHERE curr_group=group_id AND type=TG_ARGV[0];

	IF found=0 THEN
		---
		--- Create row for this group
		---
		INSERT INTO project_sums_agg
			VALUES (curr_group, TG_ARGV[0], num);
	ELSE
		---
		--- Update count
		---
		UPDATE project_sums_agg SET count=num
		WHERE curr_group=group_id AND type=TG_ARGV[0];
	END IF;

	IF TG_OP='DELETE' THEN
		RETURN OLD;
	ELSE
		RETURN NEW;
	END IF;
END;
$$ LANGUAGE plpgsql;
DELETE FROM project_sums_agg WHERE type='fora' OR type='fmsg';

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
