ALTER TABLE plugin_scmhook ADD COLUMN repository_name text;
UPDATE plugin_scmhook SET repository_name = (SELECT unix_group_name FROM groups,plugin_scmhook WHERE groups.group_id = plugin_scmhook.id_group);
