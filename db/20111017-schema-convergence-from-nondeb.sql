ALTER FUNCTION frs_dlstats_filetotal_insert_ag() RENAME TO frs_dlstats_filetotal_insert_agg;

ALTER TABLE ONLY artifact_type_monitor ADD CONSTRAINT artifact_type_monitor_group_artifact_id_fkey FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) ON DELETE CASCADE;
ALTER TABLE ONLY artifact_type_monitor ADD CONSTRAINT artifact_type_monitor_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(user_id);

ALTER TABLE artifact_type_monitor DROP CONSTRAINT "$1";
ALTER TABLE artifact_type_monitor DROP CONSTRAINT "$2";

ALTER TABLE ONLY forum_attachment ADD CONSTRAINT forum_attachment_msg_id_fkey FOREIGN KEY (msg_id) REFERENCES forum(msg_id) ON DELETE CASCADE;
ALTER TABLE ONLY forum_attachment ADD CONSTRAINT forum_attachment_userid_fkey FOREIGN KEY (userid) REFERENCES users(user_id) ON DELETE SET DEFAULT;

ALTER TABLE forum_attachment DROP CONSTRAINT "$1";
ALTER TABLE forum_attachment DROP CONSTRAINT "$2";

ALTER SEQUENCE supported_langu_language_id_seq RENAME TO supported_languages_pk_seq;
ALTER TABLE supported_languages ALTER COLUMN language_id SET DEFAULT nextval(('supported_languages_pk_seq'::text)::regclass);
ALTER SEQUENCE group_cvs_history_id_seq RENAME TO group_cvs_history_pk_seq;
ALTER TABLE group_cvs_history ALTER COLUMN id SET DEFAULT nextval(('group_cvs_history_pk_seq'::text)::regclass);
ALTER TABLE group_cvs_history ADD CONSTRAINT group_cvs_history_pkey PRIMARY KEY (id);

ALTER SEQUENCE project_messa_project_messa_seq RENAME TO project_messages_project_message_id_seq;
ALTER TABLE activity_log ALTER COLUMN ver SET DEFAULT 0::double precision;
ALTER TABLE artifact_extra_field_data ALTER COLUMN data_id SET DEFAULT nextval('artifact_extra_field_data_data_id_seq'::regclass);
ALTER TABLE artifact_extra_field_list ALTER COLUMN extra_field_id SET DEFAULT nextval('artifact_extra_field_list_extra_field_id_seq'::regclass);
ALTER TABLE artifact_extra_field_elements ALTER COLUMN element_id SET DEFAULT nextval('artifact_extra_field_elements_element_id_seq'::regclass);

ALTER TABLE db_images ALTER COLUMN upload_date SET DEFAULT 0;
ALTER TABLE db_images ALTER COLUMN version SET DEFAULT 0;

ALTER TABLE group_join_request DROP CONSTRAINT "$1";
ALTER TABLE group_join_request DROP CONSTRAINT "$2";
ALTER TABLE group_join_request ADD CONSTRAINT group_join_request_group_id_fkey FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;
ALTER TABLE group_join_request ADD CONSTRAINT group_join_request_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(user_id);
ALTER TABLE groups ADD CONSTRAINT groups_license FOREIGN KEY (license) REFERENCES licenses(license_id) MATCH FULL;
ALTER TABLE groups ALTER COLUMN unix_box SET DEFAULT 'shell'::character varying;
ALTER TABLE users ALTER COLUMN unix_box SET DEFAULT 'shell'::character varying;

DROP INDEX plugins_plugin_name_key;
ALTER TABLE plugins ADD CONSTRAINT plugins_plugin_name_key UNIQUE (plugin_name);
ALTER TABLE project_tags ADD CONSTRAINT project_tags_group_id_fkey FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;
ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f FOREIGN KEY (group_project_id) REFERENCES project_group_list(group_project_id) MATCH FULL;

ALTER TABLE project_task_external_order DROP CONSTRAINT "$1";
ALTER TABLE project_task_external_order ADD CONSTRAINT project_task_external_order_project_task_id_fkey FOREIGN KEY (project_task_id) REFERENCES project_task(project_task_id) MATCH FULL ON DELETE CASCADE;

ALTER TABLE project_weekly_metric ADD CONSTRAINT project_weekly_metric_pkey PRIMARY KEY (ranking);
DROP SEQUENCE project_metric_wee_ranking1_seq;

ALTER TABLE role DROP CONSTRAINT "$1";
ALTER TABLE role ADD CONSTRAINT role_group_id_fkey FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;

ALTER TABLE project_messages ALTER COLUMN project_message_id SET DEFAULT nextval('project_messages_project_message_id_seq'::regclass);
ALTER SEQUENCE project_messages_project_message_id_seq OWNED BY project_messages.project_message_id;

DROP SEQUENCE themes_pk_seq;
ALTER SEQUENCE themes_theme_id_seq RENAME TO themes_pk_seq;
ALTER TABLE themes ALTER COLUMN theme_id SET DEFAULT nextval(('themes_pk_seq'::text)::regclass);

DROP SEQUENCE trove_treesums_pk_seq;
ALTER SEQUENCE trove_treesum_trove_treesum_seq RENAME TO trove_treesums_pk_seq;
ALTER TABLE trove_treesums ALTER COLUMN trove_treesums_id SET DEFAULT nextval(('trove_treesums_pk_seq'::text)::regclass);

ALTER SEQUENCE group_cvs_history_pk_seq MAXVALUE 2147483647;
ALTER SEQUENCE supported_languages_pk_seq MAXVALUE 2147483647;
ALTER SEQUENCE themes_pk_seq MAXVALUE 2147483647;

ALTER TABLE project_category ALTER COLUMN category_id SET DEFAULT nextval('project_categor_category_id_seq'::regclass);

CREATE VIEW mta_users AS SELECT users.user_name AS login, users.email FROM users WHERE (users.status = 'A'::bpchar);
DROP VIEW nss_shadow;
CREATE VIEW nss_shadow AS SELECT users.user_name AS login, users.unix_pw AS passwd, 'n'::character(1) AS expired, 'n'::character(1) AS pwchange FROM users WHERE (users.unix_status = 'A'::bpchar);

INSERT INTO "project_status" (status_id, status_name) VALUES ('3', 'Deleted');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('13', 'Esperanto', 'Esperanto.class', 'Esperanto', 'eo ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('15', 'Polish', 'Polish.class', 'Polish', 'pl ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('18', 'Portuguese', 'Portuguese.class', 'Portuguese', 'pt ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('19', 'Greek', 'Greek.class', 'Greek', 'el ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('21', 'Indonesian', 'Indonesian.class', 'Indonesian', 'id ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('25', 'Latin', 'Latin.class', 'Latin', 'la ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('3', 'Hebrew', 'Hebrew.class', 'Hebrew', 'iw ');
INSERT INTO "supported_languages" (language_id, name, filename, classname, language_code) VALUES ('9', 'Norwegian', 'Norwegian.class', 'Norwegian', 'no ');

INSERT INTO "artifact_status" (id, status_name) VALUES ('3', 'Deleted');

INSERT INTO "people_skill" (skill_id, name) VALUES ('1', 'Ada');
INSERT INTO "people_skill" (skill_id, name) VALUES ('2', 'C');
INSERT INTO "people_skill" (skill_id, name) VALUES ('3', 'C++');
INSERT INTO "people_skill" (skill_id, name) VALUES ('4', 'HTML');
INSERT INTO "people_skill" (skill_id, name) VALUES ('5', 'LISP');
INSERT INTO "people_skill" (skill_id, name) VALUES ('6', 'Perl');
INSERT INTO "people_skill" (skill_id, name) VALUES ('7', 'PHP');
INSERT INTO "people_skill" (skill_id, name) VALUES ('8', 'Python');
INSERT INTO "people_skill" (skill_id, name) VALUES ('9', 'SQL');

ALTER TABLE frs_dlstats_filetotal_agg ALTER COLUMN file_id SET DEFAULT 0;
ALTER TABLE frs_dlstats_filetotal_agg ALTER COLUMN downloads SET DEFAULT 0;
ALTER TABLE frs_dlstats_filetotal_agg ALTER COLUMN downloads SET NOT NULL;

CREATE INDEX projecttaskartifact_projecttaskid ON project_task_artifact USING btree (project_task_id);
ALTER TABLE project_tags ALTER COLUMN name SET DEFAULT ''::text;

ALTER TABLE user_preferences DROP COLUMN dead1;

ALTER TABLE user_group ALTER COLUMN artifact_flags SET DEFAULT 0;

DROP TABLE IF EXISTS project_metric_tmp1;
DROP SEQUENCE IF EXISTS project_metric_tmp1_pk_seq;

ALTER TABLE user_preferences ADD COLUMN set_date_new integer DEFAULT 0 NOT NULL;
UPDATE user_preferences SET set_date_new = set_date;
ALTER TABLE user_preferences DROP COLUMN set_date;
ALTER TABLE user_preferences RENAME COLUMN set_date_new TO set_date;

ALTER TABLE project_task DROP CONSTRAINT project_task_category_id_fkey;
DROP INDEX project_categor_category_id_key;
ALTER TABLE project_category ADD CONSTRAINT project_category_pkey PRIMARY KEY (category_id);
ALTER TABLE project_task ADD CONSTRAINT project_task_category_id_fkey FOREIGN KEY (category_id) REFERENCES project_category(category_id);

ALTER TABLE users DROP CONSTRAINT users_themeid;
DROP INDEX themes_theme_id_key;
ALTER TABLE themes ADD CONSTRAINT themes_pkey PRIMARY KEY (theme_id);
ALTER TABLE users ADD CONSTRAINT users_themeid FOREIGN KEY (theme_id) REFERENCES themes(theme_id) MATCH FULL;


CREATE OR REPLACE VIEW stats_project_all_vw AS SELECT stats_project_months.group_id, (avg(stats_project_months.developers))::integer AS developers, (avg(stats_project_months.group_ranking))::integer AS group_ranking, avg(stats_project_months.group_metric) AS group_metric, sum(stats_project_months.logo_showings) AS logo_showings, sum(stats_project_months.downloads) AS downloads, sum(stats_project_months.site_views) AS site_views, sum(stats_project_months.subdomain_views) AS subdomain_views, sum(stats_project_months.page_views) AS page_views, sum(stats_project_months.file_releases) AS file_releases, sum(stats_project_months.msg_posted) AS msg_posted, (avg(stats_project_months.msg_uniq_auth))::integer AS msg_uniq_auth, sum(stats_project_months.bugs_opened) AS bugs_opened, sum(stats_project_months.bugs_closed) AS bugs_closed, sum(stats_project_months.support_opened) AS support_opened, sum(stats_project_months.support_closed) AS support_closed, sum(stats_project_months.patches_opened) AS patches_opened, sum(stats_project_months.patches_closed) AS patches_closed, sum(stats_project_months.artifacts_opened) AS artifacts_opened, sum(stats_project_months.artifacts_closed) AS artifacts_closed, sum(stats_project_months.tasks_opened) AS tasks_opened, sum(stats_project_months.tasks_closed) AS tasks_closed, sum(stats_project_months.help_requests) AS help_requests, sum(stats_project_months.cvs_checkouts) AS cvs_checkouts, sum(stats_project_months.cvs_commits) AS cvs_commits, sum(stats_project_months.cvs_adds) AS cvs_adds FROM stats_project_months GROUP BY stats_project_months.group_id;
CREATE OR REPLACE VIEW stats_project_vw AS SELECT spd.group_id, spd.month, spd.day, spd.developers, spm.ranking AS group_ranking, spm.percentile AS group_metric, salbg.count AS logo_showings, fdga.downloads, sasbg.count AS site_views, ssp.pages AS subdomain_views, (COALESCE(sasbg.count, 0) + COALESCE(ssp.pages, 0)) AS page_views, sp.file_releases, sp.msg_posted, sp.msg_uniq_auth, sp.bugs_opened, sp.bugs_closed, sp.support_opened, sp.support_closed, sp.patches_opened, sp.patches_closed, sp.artifacts_opened, sp.artifacts_closed, sp.tasks_opened, sp.tasks_closed, sp.help_requests, scg.checkouts AS cvs_checkouts, scg.commits AS cvs_commits, scg.adds AS cvs_adds FROM (((((((stats_project_developers spd LEFT JOIN stats_project sp USING (month, day, group_id)) LEFT JOIN stats_project_metric spm USING (month, day, group_id)) LEFT JOIN stats_cvs_group scg USING (month, day, group_id)) LEFT JOIN stats_agg_site_by_group sasbg USING (month, day, group_id)) LEFT JOIN stats_agg_logo_by_group salbg USING (month, day, group_id)) LEFT JOIN stats_subd_pages ssp USING (month, day, group_id)) LEFT JOIN frs_dlstats_group_vw fdga USING (month, day, group_id));

CREATE TABLE artifact_idx ( artifact_id integer, group_artifact_id integer, vectors tsvector );
CREATE TABLE artifact_message_idx ( id integer, artifact_id integer, vectors tsvector );
CREATE TABLE doc_data_idx ( docid integer, group_id integer, vectors tsvector );
CREATE TABLE forum_idx ( msg_id integer, group_id integer, vectors tsvector );
CREATE TABLE frs_file_idx ( file_id integer, release_id integer, vectors tsvector );
CREATE TABLE frs_release_idx ( release_id integer, vectors tsvector );
CREATE TABLE groups_idx ( group_id integer, vectors tsvector );
CREATE TABLE news_bytes_idx ( id integer, vectors tsvector );
CREATE TABLE project_task_idx ( project_task_id integer, vectors tsvector );
CREATE TABLE skills_data_idx ( skills_data_id integer, vectors tsvector );
CREATE TABLE users_idx ( user_id integer, vectors tsvector );

CREATE INDEX artifact_idxfti ON artifact_idx USING gist (vectors);
CREATE INDEX artifact_message_idxfti ON artifact_message_idx USING gist (vectors);
CREATE INDEX doc_data_idxfti ON doc_data_idx USING gist (vectors);
CREATE INDEX forum_idxfti ON forum_idx USING gist (vectors);
CREATE INDEX frs_file_idxfti ON frs_file_idx USING gist (vectors);
CREATE INDEX frs_release_idxfti ON frs_release_idx USING gist (vectors);
CREATE INDEX groups_idxfti ON groups_idx USING gist (vectors);
CREATE INDEX news_bytes_idxfti ON news_bytes_idx USING gist (vectors);
CREATE INDEX project_task_idxfti ON project_task_idx USING gist (vectors);
CREATE INDEX skills_data_idxfti ON skills_data_idx USING gist (vectors);
CREATE INDEX users_idxfti ON users_idx USING gist (vectors);

CREATE TRIGGER artifact_ts_update AFTER INSERT OR DELETE OR UPDATE ON artifact FOR EACH ROW EXECUTE PROCEDURE update_vectors('artifact');
CREATE TRIGGER artifactmessage_ts_update AFTER INSERT OR DELETE OR UPDATE ON artifact_message FOR EACH ROW EXECUTE PROCEDURE update_vectors('artifact_message');
CREATE TRIGGER doc_data_ts_update AFTER INSERT OR DELETE OR UPDATE ON doc_data FOR EACH ROW EXECUTE PROCEDURE update_vectors('doc_data');
CREATE TRIGGER forum_update AFTER INSERT OR DELETE OR UPDATE ON forum FOR EACH ROW EXECUTE PROCEDURE update_vectors('forum');
CREATE TRIGGER frs_file_ts_update AFTER INSERT OR DELETE OR UPDATE ON frs_file FOR EACH ROW EXECUTE PROCEDURE update_vectors('frs_file');
CREATE TRIGGER frs_release_ts_update AFTER INSERT OR DELETE OR UPDATE ON frs_release FOR EACH ROW EXECUTE PROCEDURE update_vectors('frs_release');
CREATE TRIGGER groups_ts_update AFTER INSERT OR DELETE OR UPDATE ON groups FOR EACH ROW EXECUTE PROCEDURE update_vectors('groups');
CREATE TRIGGER news_bytes_ts_update AFTER INSERT OR DELETE OR UPDATE ON news_bytes FOR EACH ROW EXECUTE PROCEDURE update_vectors('news_bytes');
CREATE TRIGGER project_task_ts_update AFTER INSERT OR DELETE OR UPDATE ON project_task FOR EACH ROW EXECUTE PROCEDURE update_vectors('project_task');
CREATE TRIGGER skills_data_ts_update AFTER INSERT OR DELETE OR UPDATE ON skills_data FOR EACH ROW EXECUTE PROCEDURE update_vectors('skills_data');
CREATE TRIGGER users_ts_update AFTER INSERT OR DELETE OR UPDATE ON users FOR EACH ROW EXECUTE PROCEDURE update_vectors('users');

CREATE TYPE artifact_results AS ( group_artifact_id integer, artifact_id integer, summary text, open_date integer, realname character varying(32));
CREATE TYPE doc_data_results AS ( docid integer, title text, description text, groupname character varying(255));
CREATE TYPE export_groups_results AS ( group_name text, unix_group_name text, type_id integer, group_id integer, short_description text, license integer, register_time integer );
CREATE TYPE forum_results AS ( msg_id integer, subject text, post_date integer, realname character varying(32));
CREATE TYPE forums_results AS ( msg_id integer, subject text, post_date integer, realname character varying(32), forum_name text );
CREATE TYPE frs_results AS ( package_name text, release_name text, release_date integer, release_id integer, realname character varying(32));
CREATE TYPE groups_results AS ( group_name text, unix_group_name text, type_id integer, group_id integer, short_description text );
CREATE TYPE news_bytes_results AS ( summary text, post_date integer, forum_id integer, realname text );
CREATE TYPE project_task_results AS ( project_task_id integer, summary text, percent_complete integer, start_date integer, end_date integer, realname text, project_name text, group_project_id integer );
CREATE TYPE skills_data_results AS ( skills_data_id integer, type integer, title text, start integer, finish integer, keywords text );
CREATE TYPE trackers_results AS ( artifact_id integer, group_artifact_id integer, summary text, open_date integer, realname character varying(32), name text );
CREATE TYPE users_results AS ( user_name text, user_id integer, realname text );

DELETE FROM project_messages_idx;

INSERT INTO project_messages_idx (id, project_task_id, vectors)
SELECT project_message_id, project_task_id, to_tsvector(coalesce(body,'')) AS vectors
FROM project_messages ORDER BY project_message_id;
