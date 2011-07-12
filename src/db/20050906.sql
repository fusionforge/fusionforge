--
-- WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING
--
-- WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING
--
-- WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING - WARNING
--
-- This SQL file is going to delete every mailing_list from the db that is not associated with
-- an existing group. Please check that this does really is not deleting sensible information

DELETE FROM mail_group_list WHERE group_id NOT IN (SELECT group_id FROM groups);

ALTER TABLE ONLY mail_group_list
   	ADD CONSTRAINT mail_group_list_group_id_fkey
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;

