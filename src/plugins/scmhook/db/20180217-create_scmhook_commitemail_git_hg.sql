-- Store destination e-mail for commit notifications
CREATE TABLE plugin_scmhook_scmgit_postreceiveemail (
  group_id int REFERENCES groups ON DELETE CASCADE,
  dest text NOT NULL,
  repository_name text
);

-- Store destination e-mail for commit notifications
CREATE TABLE plugin_scmhook_scmhg_commitemail (
  group_id int REFERENCES groups ON DELETE CASCADE,
  dest text NOT NULL,
  repository_name text
);

-- This will only work with the first plugin found...
ALTER TABLE plugin_scmhook ADD COLUMN scm_plugin text;
UPDATE plugin_scmhook SET scm_plugin = (SELECT plugin_name FROM plugins, plugin_scmhook, group_plugin WHERE group_plugin.group_id = plugin_scmhook.id_group AND plugins.plugin_id = group_plugin.plugin_id LIMIT 1);
