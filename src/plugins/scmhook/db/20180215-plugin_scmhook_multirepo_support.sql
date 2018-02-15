ALTER TABLE plugin_scmhook ADD COLUMN repository_name text;
UPDATE plugin_scmhook SET repository_name = (SELECT unix_group_name FROM groups,plugin_scmhook WHERE groups.group_id = plugin_scmhook.id_group);
ALTER TABLE plugin_scmhook_scmsvn_commitemail ADD COLUMN repository_name text;
UPDATE plugin_scmhook_scmsvn_commitemail SET repository_name = (SELECT unix_group_name FROM groups,plugin_scmhook_scmsvn_commitemail WHERE groups.group_id = plugin_scmhook_scmsvn_commitemail.group_id);
