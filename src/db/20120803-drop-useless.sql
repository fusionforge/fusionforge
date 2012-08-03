ALTER TABLE trove_group_link DROP COLUMN trove_group_id;
DROP SEQUENCE trove_group_link_pk_seq;
ALTER TABLE trove_group_link ADD FOREIGN KEY (trove_cat_root) REFERENCES trove_cat(trove_cat_id);

ALTER TABLE group_plugin DROP COLUMN group_plugin_id;
DROP SEQUENCE group_plugin_pk_seq;
CREATE INDEX groupplugin_pluginid ON group_plugin (plugin_id);
