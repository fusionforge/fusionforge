INSERT INTO plugins (plugin_name,plugin_desc) VALUES ('ldapextauth','LDAP Auth. Plugin');
INSERT INTO group_plugin (group_id, plugin_id) VALUES ('1',(SELECT plugin_id FROM plugins WHERE plugin_name='ldapextauth'));
