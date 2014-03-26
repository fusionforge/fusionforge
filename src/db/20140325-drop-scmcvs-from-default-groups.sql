-- Don't enable the 'cvs' tool for the default projects:
-- - this can't be disabled unless you install & activate scmcvs
-- - scmcvs confusingly overrides scmsvn in the builtin viewvc browsing
DELETE FROM group_plugin
  WHERE plugin_id=(SELECT plugin_id FROM plugins WHERE plugin_name='scmcvs')
    AND group_id IN (SELECT group_id FROM groups WHERE unix_group_name IN ('siteadmin','newsadmin','stats','peerrating'));
