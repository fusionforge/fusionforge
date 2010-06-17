DROP INDEX docdata_groupid;
DROP INDEX forumperm_useridgroupforumid;
ALTER TABLE forum_perm DROP CONSTRAINT forum_perm_id_key;
DROP INDEX forumperm_groupforumiduserid;
DROP INDEX group_cvs_history_id_key;
ALTER TABLE project_perm DROP CONSTRAINT project_perm_id_key;
DROP INDEX projecttaskartifact_projecttask;
DROP INDEX supported_langu_language_id_key;
