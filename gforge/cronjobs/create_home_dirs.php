#! /usr/bin/php4 -f
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id: usergroup.php,v 1.24.2.3 2005/12/05 12:47:48 danper Exp $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*

This file creates blank user home directories and 
creates group home directories with a template in it.

*/

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfwww.'include/squal_pre.php';
require $gfcommon.'/include/cron_utils.php';

//
//	Default values for the script
//
define('GROUP_ID_ADD',50000);

$err = "";
if (util_is_root_dir($groupdir_prefix))
{
	$err .= ", groupdir_prefix points to root directory!";
}
if ($err != "")
{
	cron_entry(16,"Error" . $err);
	exit;
}

//
// Get the users from the gforge database
//
$res = db_query_params ('SELECT distinct users.user_name FROM users,user_group,groups WHERE users.user_id=user_group.user_id AND user_group.group_id=groups.group_id AND groups.status=$1 AND user_group.cvs_flags=$2 AND users.unix_status=$3',
			array('A',
			'1',
			'A')) ;

$users = &util_result_column_to_array ($res, 'user_name');

//
// Get the groups from the gforge database
//
$res = db_query_params ('SELECT unix_group_name FROM groups WHERE status=$1 AND type_id=$2',
			array('A',
			'1')) ;

$groups = &util_result_column_to_array ($res, 'unix_group_name');

//
// Create home directories for users
//
foreach($users as $user)
{
	if (is_dir($homedir_prefix."/".$user) == false)
	{
		@mkdir($homedir_prefix."/".$user);
	}
	system ("chown " . $user . ":" . $user . " " . $homedir_prefix . "/" . $user);
	system ("chmod 0750 " . $homedir_prefix . "/" . $user);
}

//
// Create home directories for groups
//
$reload_apache = false;
foreach($groups as $group)
{
	//create an FTP upload dir for this project
	if (forge_get_config('use_ftpuploads'))
	{ 
		if (!is_dir($sys_ftp_upload_dir.'/'.$group))
		{
			@mkdir($sys_ftp_upload_dir.'/'.$group); 
		}
	}
	if (is_dir($groupdir_prefix."/".$group) == false)
	{
		$reload_apache = true;
		@mkdir($groupdir_prefix."/".$group);
		@mkdir($groupdir_prefix."/".$group."/htdocs");
		@mkdir($groupdir_prefix."/".$group."/cgi-bin");
		$g = &group_get_object_by_name($group);
		//
		//	Read in the template file
		//
		$contents = "";
		if (is_file ($sys_custom_path . "/project_homepage_template.php") == true)
		{
			$fo = fopen ($sys_custom_path . "/project_homepage_template.php", "r");
			if ($fo)
			{
				while (!feof ($fo))
				{
    					$contents .= fread ($fo, 8192);
				}
				fclose($fo);
			}
		}
		if (strlen ($contents) <= 0)
		{
			$contents = '<html><head><title>Default page for project not found</title></head><body><p><div align="center">Default page for project not found, please create a homepage for your project.</div></body></html>';
			$err .= "Project homepage template " . $sys_custom_path . "/project_homepage_template.php not found";
		}
		//
		//	Change some defaults in the template file
		//
		$contents = str_replace ("<domain>", forge_get_config('web_host'), $contents);
		$contents = str_replace ("<project_description>", $g->getDescription (), $contents);
		$contents = str_replace ("<project_name>", $g->getPublicName (), $contents);
		$contents = str_replace ("<group_id>", $g->getID (), $contents);
		$contents = str_replace ("<group_name>", $g->getUnixName (), $contents);
		//
		//	Write the file back out to the project home dir
		//
		$fw = fopen ($groupdir_prefix . "/" . $group . "/htdocs/index.php", "w");
		fwrite ($fw, $contents);
		fclose ($fw);
		
	}
	system ("chown -R root:" . $group. " " . $groupdir_prefix . "/" . $group);
	system ("chmod -R ug+rw " . $groupdir_prefix . "/" . $group);
	system ("find " . $groupdir_prefix . "/" . $group . " -type d -exec chmod g+s {} \;");
	system ("chmod -R o-rwx " . $groupdir_prefix . "/" . $group);
}
if (($reload_apache == true)
&&  (is_file ($sys_apache_pid_file) == true))
{
	$apache_pid = intval (file_get_contents ($sys_apache_pid_file));
	if ((is_integer ($apache_pid) == true) && ($apache_pid > 0))
	{
		if (posix_kill ($apache_pid, 1) == false) // SIGHUP
		{
			$err .= "Failed to send SIGHUP to PID " . $apache_pid;
		}
	}
}

cron_entry(16,$err);

?>
