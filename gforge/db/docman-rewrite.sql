--
--	Add a group_id column to relate docs to a group
--
ALTER TABLE doc_data ADD COLUMN group_id INT;
UPDATE doc_data SET group_id=(SELECT group_id FROM doc_groups WHERE doc_group=doc_data.doc_group);
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
BEGIN;
UPDATE groups SET new_doc_address='',send_all_docs='0';
COMMIT;

--
--	Create a convenience view for selecting from docman
--
CREATE VIEW docdata_vw AS
SELECT users.user_name,users.realname,users.email,doc_data.*,
	doc_states.name AS state_name,doc_groups.groupname AS group_name 
FROM doc_data 
NATURAL JOIN doc_states 
NATURAL JOIN doc_groups 
JOIN users ON (users.user_id=doc_data.created_by);
