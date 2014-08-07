ALTER TABLE artifact_group_list ADD CONSTRAINT artifactgroup_groupid_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

ALTER TABLE artifact_perm ADD CONSTRAINT artifactperm_userid_fk
        FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL;
DELETE from artifact_perm
	where not exists (select group_artifact_id
	from artifact_group_list
	where artifact_perm.group_artifact_id=artifact_group_list.group_artifact_id);
ALTER TABLE artifact_perm ADD CONSTRAINT artifactperm_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact_category ADD CONSTRAINT artifactcategory_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;
ALTER TABLE artifact_category ADD CONSTRAINT artifactcategory_autoassignto_fk
        FOREIGN KEY (auto_assign_to) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_group ADD CONSTRAINT artifactgroup_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;

ALTER TABLE artifact ADD CONSTRAINT artifact_groupartifactid_fk
        FOREIGN KEY (group_artifact_id) REFERENCES artifact_group_list(group_artifact_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_statusid_fk
        FOREIGN KEY (status_id) REFERENCES artifact_status(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_categoryid_fk
        FOREIGN KEY (category_id) REFERENCES artifact_category(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_artifactgroupid_fk
        FOREIGN KEY (artifact_group_id) REFERENCES artifact_group(id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_assignedto_fk
        FOREIGN KEY (assigned_to) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE artifact ADD CONSTRAINT artifact_resolutionid_fk
        FOREIGN KEY (resolution_id) REFERENCES artifact_resolution(id) MATCH FULL;

DELETE FROM artifact_history WHERE NOT EXISTS
	(SELECT artifact_id FROM artifact WHERE artifact.artifact_id=artifact_history.artifact_id);
ALTER TABLE artifact_history ADD CONSTRAINT artifacthistory_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_history ADD CONSTRAINT artifacthistory_modby_fk
        FOREIGN KEY (mod_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_file ADD CONSTRAINT artifactfile_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_file ADD CONSTRAINT artifactfile_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

ALTER TABLE artifact_message ADD CONSTRAINT artifactmessage_artifactid_fk
        FOREIGN KEY (artifact_id) REFERENCES artifact(artifact_id) MATCH FULL;
ALTER TABLE artifact_message ADD CONSTRAINT artifactmessage_submittedby_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

SELECT setval('artifact_group_list_group_artifact_id_seq',(SELECT max(group_artifact_id) FROM artifact_group_list));
--SELECT setval('artifact_perm_id_seq',(SELECT max(id) FROM artifact_perm));
SELECT setval('artifact_category_id_seq',(SELECT max(id) FROM artifact_category));
SELECT setval('artifact_group_id_seq',(SELECT max(id) FROM artifact_group));
--SELECT setval('artifact_status_id_seq',(SELECT max(id) FROM artifact_status));
SELECT setval('artifact_artifact_id_seq',(SELECT max(artifact_id) FROM artifact));
--SELECT setval('artifact_history_id_seq',(SELECT max(id) FROM artifact_history));
--SELECT setval('artifact_file_id_seq',(SELECT max(id) FROM artifact_file));
--SELECT setval('artifact_message_id_seq',(SELECT max(id) FROM artifact_message));
--SELECT setval('artifact_monitor_id_seq',(SELECT max(id) FROM artifact_monitor));

