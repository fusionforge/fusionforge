<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
?>
<?php
/**
 * Script for creating user and group permissions
 *
 * Based on cronjobs/cvs-cron/usergroup.php from GForge 4.5.11
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
 * This script creates user and group permissions by generating
 * the /etc/passwd, /etc/shadow and /etc/group files
 */

require_once('squal_pre.php');
require ('common/include/cron_utils.php');

// Default shell
$default_shell = "/bin/bash";

// Value to add to group id
$gid_add = 10000;

$err = "";
if (file_exists ("/etc/passwd.org") == false)
{
	$err .= ", /etc/passwd.org missing!";
}
if (file_exists ("/etc/shadow.org") == false)
{
	$err .= ", /etc/shadow.org missing!";
}
if (file_exists ("/etc/group.org") == false)
{
	$err .= ", /etc/group.org missing!";
}
if (util_is_root_dir ($homedir_prefix) == true)
{
	$err .= ", homedir_prefix points to root directory!";
}

if ($err == "")
{
	//
	// Get the users from the database
	//
	$res = db_query_params ('SELECT user_name,unix_pw,unix_uid,unix_gid,realname,shell FROM users WHERE unix_status=$1',
			array('A')) ;

	$user_names = &util_result_column_to_array ($res, "user_name");
	$user_pws = &util_result_column_to_array ($res, "unix_pw");
	$user_ids = &util_result_column_to_array ($res, "unix_uid");
	$user_gids = &util_result_column_to_array ($res, "unix_gid");
	$user_realnames = &util_result_column_to_array ($res, "realname");
	$user_shells = &util_result_column_to_array ($res, "shell");
	//
	// Read the "default" users in /etc/passwd.org
	//
	$h = fopen ("/etc/passwd.org", "r");
	$passwdcontents = fread ($h, filesize ("/etc/passwd.org"));
	fclose ($h);
	$passwdlines = explode ("\n", $passwdcontents);
	//
	// Write the "default" users in /etc/passwd
	//
	$h2 = fopen ("/etc/passwd", "w");
	for ($k = 0; $k < count ($passwdlines); $k++)
	{
		$passwdline = explode (":", $passwdlines [$k]);
		$def_users [$passwdline [0]] = 1;
		fwrite ($h2, $passwdlines [$k] . "\n");
	}
	//
	// Append the users from the database
	//
	for ($i = 0; $i < count ($user_names); $i++)
	{
		if ($def_users [$user_names [$i]])
		{
			// This username was already existing in the /etc/passwd.org file
		}
		else
		{
			if ((strlen ($user_shells [$i]) > 0) && (file_exists ($user_shells [$i]) == true))
			{
				$shell = $user_shells [$i];
			}
			else
			{
				$shell = $default_shell;
			}
			$line = $user_names [$i] . ":x:" . $user_ids [$i] . ":" . $user_ids [$i] . ":" . $user_realnames [$i] . ":" . $homedir_prefix . "/" . $user_names [$i] . ":" . $shell . "\n";
			fwrite ($h2, $line);
		}

	}
	fclose($h2);

	//
	// Read the "default" users in /etc/shadow.org
	//
	$h3 = fopen ("/etc/shadow.org", "r");
	$shadowcontents = fread ($h3, filesize ("/etc/shadow.org"));
	fclose ($h3);
	$shadowlines = explode ("\n", $shadowcontents);
	//
	// Write the "default" users in /etc/shadow
	//
	$h4 = fopen("/etc/shadow","w");
	for($k = 0; $k < count ($shadowlines); $k++)
	{
		$shadowline = explode (":", $shadowlines [$k]);
		$def_shadow [$shadowline [0]] = 1;
		fwrite ($h4, $shadowlines [$k] . "\n");
	}
	//
	// Append the users from the database
	//
	for ($i = 0; $i < count ($user_names); $i++)
	{
		if ($def_shadow [$user_names [$i]])
		{
			// This username was already existing in the /etc/shadow.org file
		}
		else
		{
			$line = $user_names [$i] . ":" . $user_pws [$i] . ":12090:0:99999:7:::\n";
			fwrite ($h4, $line);
		}
	}
	fclose($h4);

	//
	// Get the groups from the database
	//
	$res = db_query_params ('SELECT unix_group_name,group_id FROM groups WHERE status=$1 AND use_scm=1',
			array('A')) ;

	$group_names = &util_result_column_to_array ($res, "unix_group_name");
	$group_ids = &util_result_column_to_array ($res, "group_id");
	//
	// Read the "default" groups in /etc/group.org
	//
	$h5 = fopen ("/etc/group.org", "r");
	$groupcontents = fread ($h5, filesize ("/etc/group.org"));
	fclose ($h5);
	$grouplines = explode ("\n", $groupcontents);
	//
	// Write the "default" groups in /etc/group
	//
	$h6 = fopen ("/etc/group", "w");
	for ($k = 0; $k < count ($grouplines); $k++)
	{
		$groupline = explode (":", $grouplines [$k]);
		$def_group [$groupline [0]] = 1;
		fwrite ($h6, $grouplines [$k] . "\n");
	}
	//
	// Add the groups from the database
	//
	for ($i = 0; $i < count ($group_names); $i++)
	{
		if ($def_group [$group_names [$i]])
		{
			// This groupname was already existing in the /etc/group.org file
		}
		else
		{
			$line = $group_names [$i] . ":x:" . ($group_ids [$i] + $gid_add) . ":";
			$resusers = db_query ("SELECT user_name"
			          . " FROM user_group,users,groups"
			          . " WHERE users.user_id=user_group.user_id"
			          . " AND groups.group_id=user_group.group_id"
			          . " AND groups.status='A'::bpchar"
			          . " AND groups.use_scm=1"
			          . " AND groups.unix_group_name='" . $group_names [$i] . "'"
			          . " AND users.status='A'::bpchar"
			          . " AND users.unix_status='A'::bpchar");
			$members = &util_result_column_to_array ($resusers, "user_name");
			if (count ($members) > 0)
			{
				$line .= implode (",", $gmembers) . ",";
			}
			$line .= $sys_apache_user . "\n";
			fwrite ($h6, $line);
		}
	}
	fclose($h6);
}

if ($err != "")
{
	$err = "Error" . $err;
}
cron_entry (16, $err);

?>
