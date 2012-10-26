ALTER TABLE project_assigned_to DROP COLUMN project_assigned_id;
DROP INDEX projectassigned_assignedtotaskid;
DROP SEQUENCE project_assigned_to_pk_seq;

ALTER TABLE forum_saved_place DROP COLUMN saved_place_id;
DROP SEQUENCE forum_saved_place_pk_seq;
ALTER TABLE forum_saved_place ADD FOREIGN KEY (forum_id) REFERENCES forum ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE filemodule_monitor DROP COLUMN id;
DROP SEQUENCE filemodule_monitor_pk_seq;
ALTER TABLE filemodule_monitor ADD FOREIGN KEY (filemodule_id) REFERENCES frs_package ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE prdb_dbs ADD FOREIGN KEY (created_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE trove_treesums DROP COLUMN trove_treesums_id;
DROP SEQUENCE trove_treesums_pk_seq;

ALTER TABLE prdb_dbs ADD FOREIGN KEY (created_by) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE mail_group_list ADD FOREIGN KEY (list_admin) REFERENCES users ON DELETE CASCADE ON UPDATE CASCADE;
