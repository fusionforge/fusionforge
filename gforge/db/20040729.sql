-- Subscribe groups that use SCM to scmcvs plugin
INSERT INTO group_plugin (group_id,plugin_id) (SELECT group_id,plugin_id FROM groups,plugins WHERE use_scm=1 AND group_id NOT IN (SELECT group_id FROM group_plugin) AND plugin_name='scmcvs');
