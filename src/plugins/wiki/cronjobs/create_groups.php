#! /usr/bin/php -f
<?php
/**
 * create_groups.php
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 *
 * @version   $Id
 */

require dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

//	Owner of files - apache
$file_owner=forge_get_config('apache_user').':'.forge_get_config('apache_group');

//	Whether to separate directories by first letter like /m/mygroup /a/apple
$first_letter = false;

//      Populate the groups with the files present in the directory
//      Comment this to have it created empty.
$template_groups = 'template_groups';

/*

	This script create the gforge/upload directory for groups

*/

if ($argc < 2 ) {
	echo "Usage ".$argv[0]." <path> <-f>\n";
	echo "-f  First Letter activated... do groups/m/myprojec\n";
	exit (0);
}
if (isset($argv[2]) && $argv[2]=='-f' ) {
   $first_letter = true;
}

$upload_path = $argv[1];
$basedir = dirname($argv[0]);

$err = "Creating Groups at ". $upload_path."\n";

if (forge_get_config('apache_user') == '' || forge_get_config('apache_group') == '') {
	$err .=  "Error! sys_apache_user Is Not Set Or sys_apache_group Is Not Set!";
	echo $err;
	cron_entry(23,$err);
	exit;
}

$res = db_query_params ('SELECT unix_group_name FROM groups WHERE status != $1 AND status != $2;',
			array('P',
				'D'));
if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	cron_entry(23,$err);
	exit;
}

$groups_dir = "$upload_path/groups";

if (!is_dir($groups_dir))
	system("mkdir -p $groups_dir");

function populate_initial_wiki($params) {
	$template_groups = $params['template_groups'];
	$name = $params['name'];
	$groups_dir = $params['groups_dir'];

	if (isset($template_groups) && !empty($template_groups))
		system("(cd $basedir/$template_groups ; tar cf - --exclude=.svn *) |" .
		       " (cd $groups_dir/$name; tar xf -)");
}

$userinfo = posix_getpwnam($file_owner);
while ( $row = db_fetch_array($res) ) {
	if ($first_letter) {
		$name = $row["unix_group_name"][0]."/".$row["unix_group_name"];
	} else {
		$name = $row["unix_group_name"];
	}

	if (!is_dir("$groups_dir/$name")) {
		system("mkdir -p $groups_dir/$name");
		system("chown $file_owner $groups_dir/$name");

		$params = array();
		$params['template_groups'] = $template_groups;
		$params['name'] = $name;
		$params['groups_dir'] = $groups_dir;
		
		util_sudo_effective_user($file_owner,
					 "populate_initial_wiki",
					 $params);
	}
}

util_sudo_effective_user($file_owner,
			 function() use ($groups_dir) {
				 system("find $groups_dir -type d -exec chmod 700 {} \;");
				 system("find $groups_dir -type f -exec chmod 600 {} \;");
			 });

cron_entry(901,$err);
?>
