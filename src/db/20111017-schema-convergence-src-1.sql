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
ALTER SEQUENCE group_cvs_history_id_seq RENAME TO group_cvs_history_pk_seq;
ALTER SEQUENCE project_messa_project_messa_seq RENAME TO project_messages_project_message_id_seq;
ALTER TABLE activity_log ALTER COLUMN ver SET DEFAULT 0::double precision;
ALTER TABLE artifact_extra_field_data ALTER COLUMN data_id SET DEFAULT nextval('artifact_extra_field_data_data_id_seq'::regclass);
ALTER TABLE artifact_extra_field_list ALTER COLUMN extra_field_id SET DEFAULT nextval('artifact_extra_field_list_extra_field_id_seq'::regclass);
ALTER TABLE db_images ALTER COLUMN upload_date SET DEFAULT 0;
ALTER TABLE db_images ALTER COLUMN version SET DEFAULT 0;
