ALTER FUNCTION frs_dlstats_filetotal_insert_ag() RENAME TO frs_dlstats_filetotal_insert_agg;

ALTER TABLE ONLY artifact_type_monitor ADD CONSTRAINT artifact_type_monitor_group_artifact_id_fkey FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) ON DELETE CASCADE;
ALTER TABLE ONLY artifact_type_monitor ADD CONSTRAINT artifact_type_monitor_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(user_id);

ALTER TABLE artifact_type_monitor DROP CONSTRAINT "$1";
ALTER TABLE artifact_type_monitor DROP CONSTRAINT "$2";

ALTER TABLE ONLY forum_attachment ADD CONSTRAINT forum_attachment_msg_id_fkey FOREIGN KEY (msg_id) REFERENCES forum(msg_id) ON DELETE CASCADE;
ALTER TABLE ONLY forum_attachment ADD CONSTRAINT forum_attachment_userid_fkey FOREIGN KEY (userid) REFERENCES users(user_id) ON DELETE SET DEFAULT;

ALTER TABLE forum_attachment DROP CONSTRAINT "$1";
ALTER TABLE forum_attachment DROP CONSTRAINT "$2";
