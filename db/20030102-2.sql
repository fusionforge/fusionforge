--
--	Add a group_id column to relate docs to a group
--
ALTER TABLE doc_data ADD COLUMN group_id INT;
UPDATE doc_data SET group_id=(SELECT group_id FROM doc_groups WHERE doc_group=doc_data.doc_group);
UPDATE doc_data SET stateid=4 WHERE stateid=100;
--
--	Add fkey constraints
--
ALTER TABLE doc_data ADD CONSTRAINT docdata_groupid
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;
ALTER TABLE doc_data ADD CONSTRAINT docdata_docgroupid
	FOREIGN KEY (doc_group) REFERENCES doc_groups(doc_group);
ALTER TABLE doc_data ADD CONSTRAINT docdata_stateid
	FOREIGN KEY (stateid) REFERENCES doc_states(stateid);
ALTER TABLE doc_groups ADD CONSTRAINT docgroups_groupid
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;
--
--	Re-use old columns in the groups table
--
ALTER TABLE groups RENAME COLUMN new_task_address TO new_doc_address;
ALTER TABLE groups RENAME COLUMN send_all_tasks TO send_all_docs;
--BEGIN;
UPDATE groups SET new_doc_address='',send_all_docs='0';
--COMMIT;

--
--	Create a convenience view for selecting from docman
--
CREATE VIEW docdata_vw AS
SELECT users.user_name,users.realname,users.email,
	d.group_id,d.docid,d.stateid,d.title,d.updatedate,d.createdate,d.created_by,
	d.doc_group,d.description,d.language_id,d.filename,d.filetype,
	doc_states.name AS state_name,
	doc_groups.groupname AS group_name,
	sl.name as language_name
FROM doc_data d
NATURAL JOIN doc_states
NATURAL JOIN doc_groups
JOIN supported_languages sl ON (sl.language_id=d.language_id)
JOIN users ON (users.user_id=d.created_by);

--
--	NEW VIEW FOR TRACKER
--
CREATE VIEW artifact_group_list_vw AS
SELECT agl.*,aca.count,aca.open_count
        FROM artifact_group_list agl
        LEFT JOIN artifact_counts_agg aca USING (group_artifact_id);
