<?php

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$svn_access_filename = '/etc/gforge/plugins/scmsvn/svn.access';
$svn_users_filename = '/etc/gforge/plugins/scmsvn/svn.users';

$err = '';

$res = db_query("SELECT groups.group_id,is_public,enable_anonscm,unix_group_name
	FROM groups, plugins, group_plugin
	WHERE groups.status != 'P'
	AND groups.group_id=group_plugin.group_id
	AND group_plugin.plugin_id=plugins.plugin_id
	AND plugins.plugin_name='scmsvn'");

if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	exit();
}

$svn_access_groups = "[groups]\n";
$svn_access_repositories = '';
$svn_users = '';

while ( $row =& db_fetch_array($res) ) {
	$res_users = db_query("SELECT distinct users.user_name,users.unix_pw,users.user_id
		FROM users, user_group, groups
		WHERE users.user_id=user_group.user_id
		AND user_group.group_id=groups.group_id
		AND groups.status='A'
		AND user_group.cvs_flags='1'
		AND users.status='A'
		AND groups.group_id=".$row['group_id']."
		ORDER BY user_id ASC");
	$svn_access_repositories .= '['.$row['unix_group_name'].':/]'."\n";
	if($row['enable_anonscm'] == 't') {
		$svn_access_repositories .= '* = r'."\n";
	}
	$svn_access_repositories .= '@'.$row['unix_group_name'].' = rw'."\n";

	$group_users = array();
	while ( $row_users =& db_fetch_array($res_users) ) {
		$group_users[] = $row_users['user_name'];
		$svn_users .= $row_users['user_name'].':'.$row_users['unix_pw']."\n";
	}
	$svn_access_groups .= $row['unix_group_name'].' = '.implode(', ', $group_users)."\n";
}

writeFile($svn_access_filename, $svn_access_groups."\n\n".$svn_access_repositories);
writeFile($svn_users_filename, $svn_users);

function writeFile($filePath, $content) {
	$file = fopen($filePath, 'a');
	flock($file, LOCK_EX);
	ftruncate($file, 0);
	rewind($file);
	if(!empty($content)) {
		fwrite($file, $content);
	}
	flock($file, LOCK_UN);
	fclose($file);
	chown($filePath, 'root');
	chgrp($filePath, 'apache');
	chmod($filePath, 0640);
}

// TODO : cron entry
//cron_entry(21,$err);

?>
