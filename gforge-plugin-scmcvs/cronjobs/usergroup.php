#! /usr/bin/php4 -f
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
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
This file creates user / group permissions by editing 
the /etc/passwd /etc/shadow and /etc/group files
*/
require_once('squal_pre.php');
require ('common/include/cron_utils.php');

//
//	Default values for the script
//
define('DEFAULT_SHELL','/bin/cvssh.pl'); //use /bin/grap for cvs-only
define('FILE_EXTENSION','.new'); // use .new when testing

if (util_is_root_dir($groupdir_prefix)) {
	$err .=  "Error! groupdir_prefix Points To Root Directory!";
	echo $err;
	cron_entry(16,$err);
	exit;
}

//
//	Get the users' unix_name and password out of the database
//   ONLY USERS WITH CVS READ AND COMMIT PRIVS ARE ADDED
//
$res = db_query("SELECT distinct users.user_name,users.unix_pw,users.unix_uid,users.unix_gid,users.user_id
	FROM users,user_group,groups
	WHERE users.user_id=user_group.user_id 
	AND user_group.group_id=groups.group_id
	AND groups.status='A'
	AND user_group.cvs_flags IN ('0','1')
	AND users.status='A'
	ORDER BY users.user_id ASC");
$err .= db_error();

$gforge_users    =& util_result_column_to_array($res,'user_name');
$user_unix_uids =& util_result_column_to_array($res,'unix_uid');
$user_unix_gids =& util_result_column_to_array($res,'unix_gid');
$user_pws =& util_result_column_to_array($res,'unix_pw');

// Create the entries for the GForge users
$gforge_lines = array();

// user description is something like "MyGForge user"
$user_description = preg_replace('/[^[:alnum:] -_]/', '', $sys_name);
$user_description .= " user";

for ($i=0; $i < count($gforge_users); $i++) {
	$username = $gforge_users[$i];
	$user_unix_uid = $user_unix_uids[$i];
	$user_unix_gid = $user_unix_gids[$i];
	$shell = DEFAULT_SHELL;
	$unix_passwd = $user_pws[$i];
	
	$line_passwd =	$username.":x:".$user_unix_uid.":".$user_unix_gid.":".
					$user_description.":/home/".$username.":".$shell;
	$line_shadow = $username.":".$unix_passwd.":12090:0:99999:7:::";
	
	$gforge_lines_passwd[] = $line_passwd;
	$gforge_lines_shadow[] = $line_shadow;
	
}

/*************************************************************************
 * Step 1: Process /etc/passwd
 *************************************************************************/

// Read the passwd file line by line
$passwd_orig = file("/etc/passwd", "r");

// Now write the file with the gforge users at the end
$passwd = fopen("/etc/passwd".FILE_EXTENSION, "w");
$etc_users = array();
for ($i=0; $i < count($passwd_orig); $i++) {
	$line = trim($passwd_orig[$i]);
	// Skip the GForge users (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($passwd_orig[$i]);
		} while ($i < count($passwd_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));
		
		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file
		if ($i >= (count($passwd_orig)-1)) break;
		
		// read next line
		$i++;
		$line = trim($passwd_orig[$i]);
	}
	
	$entries = explode(":", $line);
	if (!empty($entries[0])) {
		$username = $entries[0];
		$etc_users[] = $username;		// this is currently not used, but it may be used in the future
		if (!in_array($username, $gforge_users)) {
			// write the user only if it's not a gforge user
			fwrite($passwd, $line."\n");
		}
	} else {
		// blank line or comment
		fwrite($passwd, $line."\n");
	}
}

/*************************************************************************
 * Step 2: Process /etc/shadow
 *************************************************************************/

// Read the shadow file line by line
$passwd_orig = file("/etc/shadow", "r");

// Now write the file with the gforge users at the end
$shadow = fopen("/etc/shadow".FILE_EXTENSION, "w");
for ($i=0; $i < count($passwd_orig); $i++) {
	$line = trim($passwd_orig[$i]);
	// Skip the GForge users (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($passwd_orig[$i]);
		} while ($i < count($passwd_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));
		
		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file
		if ($i >= (count($passwd_orig)-1)) break;
		
		// read next line
		$i++;
		$line = trim($passwd_orig[$i]);
	}
	
	$entries = explode(":", $line);
	if (!empty($entries[0])) {
		$username = $entries[0];
		if (!in_array($username, $gforge_users)) {
			// write the user only if it's not a gforge user
			fwrite($shadow, $line."\n");
		}
	} else {
		// blank line or comment
		fwrite($shadow, $line."\n");
	}
}


/*************************************************************************
 * Step 3: Write the GForge users to /etc/passwd and /etc/shadow
 *************************************************************************/

// now write the GForge users
fwrite($passwd, "#GFORGEBEGIN\n");
fwrite($shadow, "#GFORGEBEGIN\n");
assert(count($gforge_lines_passwd) == count($gforge_lines_shadow));
for ($i=0; $i < count($gforge_lines_passwd); $i++) {
	$line_passwd = $gforge_lines_passwd[$i];
	$line_shadow = $gforge_lines_shadow[$i];
	
	fwrite($passwd, $line_passwd."\n");
	fwrite($shadow, $line_shadow."\n");
}
fwrite($passwd, "#GFORGEEND\n");
fwrite($shadow, "#GFORGEEND\n");


fclose($passwd);
fclose($shadow);

/*************************************************************************
 * Step 4: Parse /etc/group
 *************************************************************************/
$group_orig = file("/etc/group");
$group = fopen("/etc/group".FILE_EXTENSION, "w");

//	Add the groups from the gforge database
$group_res = db_query("SELECT group_id, unix_group_name, unix_gid, (is_public=1 AND enable_anonscm=1 AND type_id=1) AS enable_pserver FROM groups WHERE status='A' AND type_id='1'");
$err .= db_error();
for($i = 0; $i < db_numrows($group_res); $i++) {
    $gforge_groups[] = db_result($group_res,$i,'unix_group_name');
    $gids[db_result($group_res,$i,'unix_group_name')] = db_result($group_res,$i,'unix_gid');
}

for ($i=0; $i < count($group_orig); $i++) {
	$line = trim($group_orig[$i]);
	// Skip the GForge groups (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($passwd_orig[$i]);
		} while ($i < count($passwd_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));
		
		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file
		if ($i >= (count($passwd_orig)-1)) break;
		
		// read next line
		$i++;
		$line = trim($passwd_orig[$i]);
	}
	
	$entries = explode(":", $line);
	if (!empty($entries[0])) {
		$groupname = $entries[0];
		if (!in_array($groupname, $gforge_groups)) {
			// write the user only if it's not a gforge user
			fwrite($group, $line."\n");
		}
	} else {
		// blank line or comment
		fwrite($group, $line."\n");
	}
}

// Now write the GForge groups
fwrite($group, "#GFORGEBEGIN\n");

for ($i = 0; $i < count($gforge_groups); $i++) {
	$group_name = $gforge_groups[$i];
	$unix_gid = $gids[$group_name];
	
	$line = $group_name.":x:".$unix_gid.":";
	
	/* we need to get the project object to check if a project
	 * has a private CVS repository - in which case we need to add
	 * the apache user to the group so that ViewCVS can be used
	 */
	 
	$group_id = db_result($group_res, $i, 'group_id');
	$project = &group_get_object($group_id);
	$enable_pserver = (db_result($group_res, $i, 'enable_pserver') == 't');	

	$resusers = db_query("SELECT user_name 
		FROM users,user_group,groups 
		WHERE groups.group_id=user_group.group_id 
		AND users.user_id=user_group.user_id
		AND user_group.cvs_flags IN ('0','1')
		AND users.status='A'
		AND groups.unix_group_name='".$group_name."'");
	$gmembers = util_result_column_to_array($resusers,'user_name');
	if ($enable_pserver) $gmembers[] = 'anonymous';
	if (!$project->enableAnonSCM()) {
		$gmembers[] = $sys_apache_user;
	}
	
	$line .= implode(',', $gmembers);
	
	fwrite($group, $line."\n");
		
}

fwrite($group, "#GFORGEEND\n");
fclose($group);

cron_entry(16,$err);

?>
