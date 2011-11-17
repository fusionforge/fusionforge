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

DROP INDEX plugins_plugin_name_key;
ALTER TABLE plugins ADD CONSTRAINT plugins_plugin_name_key UNIQUE (plugin_name);
ALTER TABLE project_category ADD CONSTRAINT project_category_pkey PRIMARY KEY (category_id);
ALTER TABLE project_tags ADD CONSTRAINT project_tags_group_id_fkey FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;
ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f FOREIGN KEY (group_project_id) REFERENCES project_group_list(group_project_id) MATCH FULL;

ALTER TABLE project_task_external_order DROP CONSTRAINT "$1";
ALTER TABLE project_task_external_order ADD CONSTRAINT project_task_external_order_project_task_id_fkey FOREIGN KEY (project_task_id) REFERENCES project_task(project_task_id) MATCH FULL ON DELETE CASCADE;

ALTER TABLE project_weekly_metric ADD CONSTRAINT project_weekly_metric_pkey PRIMARY KEY (ranking);

ALTER TABLE role DROP CONSTRAINT "$1";
ALTER TABLE role ADD CONSTRAINT role_group_id_fkey FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;

ALTER TABLE ONLY themes ADD CONSTRAINT themes_pkey PRIMARY KEY (theme_id);
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
