--
--
--	WARNING - modifying on 10-22-2004 since the 0729 version was incomplete
--
--
-- Christian Bayle, finally renaming 0729 in 1022
-- Insert record in plugins table only if it doesn't already exists


INSERT INTO plugins (plugin_name,plugin_desc) (SELECT 'scmcvs','CVS Plugin' WHERE (SELECT count(*) FROM plugins WHERE plugin_name ='scmcvs')=0) ;

-- Subscribe groups that use SCM to scmcvs plugin
DELETE FROM group_plugin
	WHERE plugin_id=(SELECT plugin_id FROM plugins WHERE plugin_name='scmcvs');

INSERT INTO group_plugin (group_id,plugin_id)
	(SELECT group_id,(SELECT plugin_id FROM plugins WHERE plugin_name='scmcvs')
	FROM groups
	WHERE status != 'P');
