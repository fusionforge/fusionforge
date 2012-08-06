ALTER TABLE ONLY doc_data ADD CONSTRAINT docdata_languageid_fk FOREIGN KEY (language_id) REFERENCES supported_languages(language_id) MATCH FULL;
ALTER TABLE ONLY forum ADD CONSTRAINT forum_posted_by_fk FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL;

DROP TABLE forum_attachment_type;
DROP FUNCTION forums_search(text, integer, text, boolean);
DROP TABLE foundry_data;
DROP TABLE foundry_news;
DROP TABLE foundry_preferred_projects;
DROP TABLE foundry_project_downloads_agg;
DROP TABLE foundry_project_rankings_agg;
DROP TABLE foundry_projects;
DROP TABLE group_type;

ALTER TABLE ONLY project_task_external_order DROP CONSTRAINT roject_task_external_order_pkey;
ALTER TABLE ONLY project_task_external_order ADD CONSTRAINT project_task_external_order_pkey PRIMARY KEY (project_task_id);

DROP SEQUENCE foundry_projects_pk_seq;
DROP SEQUENCE foundry_preferred_projec_pk_seq;
DROP SEQUENCE artifact_perm_id_seq;

ALTER TABLE forum_attachment ALTER COLUMN mimetype SET NOT NULL;
ALTER TABLE forum_pending_attachment ALTER COLUMN mimetype SET NOT NULL;

ALTER TABLE project_category ALTER COLUMN category_id SET DEFAULT nextval('project_categor_category_id_seq'::regclass);
ALTER TABLE activity_log ALTER COLUMN ver SET DEFAULT 0::double precision;

ALTER TABLE users DROP COLUMN sys_state;
ALTER TABLE groups DROP COLUMN sys_state;
ALTER TABLE user_group DROP COLUMN sys_state;
ALTER TABLE user_group DROP COLUMN dead1;
ALTER TABLE user_group DROP COLUMN dead2;
ALTER TABLE user_group DROP COLUMN dead3;
ALTER TABLE user_group DROP COLUMN sys_cvs_state;

ALTER TABLE project_task ALTER COLUMN hours SET DEFAULT 0::double precision;
ALTER TABLE survey_rating_aggregate ALTER COLUMN response SET DEFAULT 0::double precision;

ALTER TABLE user_metric ALTER COLUMN avg_raters_importance SET DEFAULT 0::double precision;
ALTER TABLE user_metric ALTER COLUMN avg_rating SET DEFAULT 0::double precision;
ALTER TABLE user_metric ALTER COLUMN importance_factor SET DEFAULT 0::double precision;
ALTER TABLE user_metric ALTER COLUMN metric SET DEFAULT 0::double precision;
ALTER TABLE user_metric ALTER COLUMN percentile SET DEFAULT 0::double precision;

ALTER TABLE user_metric0 ALTER COLUMN avg_raters_importance SET DEFAULT 0::double precision;
ALTER TABLE user_metric0 ALTER COLUMN avg_rating SET DEFAULT 0::double precision;
ALTER TABLE user_metric0 ALTER COLUMN importance_factor SET DEFAULT 0::double precision;
ALTER TABLE user_metric0 ALTER COLUMN metric SET DEFAULT 0::double precision;
ALTER TABLE user_metric0 ALTER COLUMN percentile SET DEFAULT 0::double precision;

DROP INDEX group_unix_uniq;
CREATE UNIQUE INDEX group_unix_uniq ON groups USING btree (unix_group_name);

CREATE UNIQUE INDEX project_messa_project_messa_key ON project_messages USING btree (project_message_id);
