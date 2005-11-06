ALTER TABLE ONLY mail_group_list
   	ADD CONSTRAINT mail_group_list_group_id_fkey 
	FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE;

