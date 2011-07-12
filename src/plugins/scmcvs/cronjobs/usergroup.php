#! /usr/bin/php
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/*
This file creates user / group permissions by editing
the /etc/passwd /etc/shadow and /etc/group files
*/
require_once dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

//error variable
$err = '';

//
//	Default values for the script
//
define('DEFAULT_SHELL','/bin/cvssh.pl'); //use /bin/grap for cvs-only
define('FILE_EXTENSION','.new'); // use .new when testing

if (util_is_root_dir(forge_get_config('groupdir_prefix'))) {
	$err .=  "Error! groupdir_prefix Points To Root Directory!";
	echo $err;
	cron_entry(16,$err);
	exit;
}

$res = db_query_params ('SELECT group_id FROM groups WHERE status=$1',
			array('A')) ;
$err .= db_error();
$groups = group_get_objects (util_result_column_to_array($res,'group_id'));

$res = db_query_params ('SELECT user_id FROM users WHERE unix_status=$1',
			array('A')) ;
$err .= db_error();
$users = user_get_objects (util_result_column_to_array($res,'group_id'));

// Create the entries for the GForge users
$gforge_lines_passwd = array();
$gforge_lines_shadow = array();
$gforge_lines_groups = array();

// These will be the entries that already exist
$unmanaged_lines_passwd = array();
$unmanaged_lines_shadow = array();
$unmanaged_lines_group = array();

// user description is something like "MyGForge user"
$user_description = preg_replace('/[^[:alnum:] _-]/', '', forge_get_config ('forge_name'));
$user_description .= " user";

/*************************************************************************
 * Step 1: Process /etc/passwd
 *************************************************************************/

// Read the passwd file line by line
$passwd_orig = file("/etc/passwd");
for ($i=0; $i < count($passwd_orig); $i++) {
	$line = trim($passwd_orig[$i]);

	// Skip the GForge users (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($passwd_orig[$i]);
		} while ($i < count($passwd_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));

		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file, but
		// it's not a fatal error
		if ($i >= (count($passwd_orig)-1)) break;

		// read next line
		$i++;
		$line = trim($passwd_orig[$i]);
	}

	// Here, we're outside the #GFORGE markers
	$entries = explode(":", $line);
	if (!empty($entries[0])) {
		$username = $entries[0];
		$unmanaged_usernames[] = $username;
	}

	$unmanaged_lines_passwd[] = $line;
}

// Now, check which of the GForge users were found outside the #GFORGE markers. In that
// case, the user must not be written inside the markers (means the user is managed by
// the sysadmin)
foreach ($users as $u) {
	$username = $u->getUnixName() ;
	$managed_by_gforge = !in_array($username, $unmanaged_usernames);

	if ($managed_by_gforge) {
		$user_unix_uid = $u->getUnixUID() ;
		$user_unix_gid = $u->getUnixGID() ;
		$shell = DEFAULT_SHELL;
		$unix_passwd = $u->getUnixPasswd() ;

		$line_passwd =	$username.":x:".$user_unix_uid.":".$user_unix_gid.":".
						$user_description.":/home/".$username.":".$shell;
		$gforge_lines_passwd[] = $line_passwd;
	}
}

// Generate the contents of /etc/passwd
$passwd_contents = implode("\n", $unmanaged_lines_passwd);
$passwd_contents .= "\n";
$passwd_contents .= "#GFORGEBEGIN\n";
$passwd_contents .= implode("\n", $gforge_lines_passwd);
$passwd_contents .= "\n#GFORGEEND\n";

/*************************************************************************
 * Step 2: Process /etc/shadow
 *************************************************************************/

// Read the shadow file line by line
$shadow_orig = file("/etc/shadow");
for ($i=0; $i < count($shadow_orig); $i++) {
	$line = trim($shadow_orig[$i]);

	// Skip the GForge users (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($shadow_orig[$i]);
		} while ($i < count($shadow_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));

		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file, but
		// it's not a fatal error
		if ($i >= (count($shadow_orig)-1)) break;

		// read next line
		$i++;
		$line = trim($shadow_orig[$i]);
	}

	// Here, we're outside the #GFORGE markers
	$entries = explode(":", $line);
	if (!empty($entries[0])) {
		$username = $entries[0];
		$unmanaged_usernames[] = $username;
	}

	$unmanaged_lines_shadow[] = $line;
}
foreach ($users as $u) {
	$username = $u->getUnixName() ;
	$managed_by_gforge = !in_array($username, $unmanaged_usernames);

	if ($managed_by_gforge) {
		$unix_passwd = $user_pws[$i];

		$line_shadow = $username.":".$unix_passwd.":12090:0:99999:7:::";
		$gforge_lines_shadow[] = $line_shadow;
	}
}

// Generate the contents of /etc/shadow
$shadow_contents = implode("\n", $unmanaged_lines_shadow);
$shadow_contents .= "\n";
$shadow_contents .= "#GFORGEBEGIN\n";
$shadow_contents .= implode("\n", $gforge_lines_shadow);
$shadow_contents .= "\n#GFORGEEND\n";

/*************************************************************************
 * Step 3: Parse /etc/group
 *************************************************************************/
$group_orig = file("/etc/group");

//	Add the groups from the gforge database
for ($i=0; $i < count($group_orig); $i++) {
	$line = trim($group_orig[$i]);
	// Skip the GForge groups (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($group_orig[$i]);
		} while ($i < count($group_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));

		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file
		if ($i >= (count($group_orig)-1)) break;

		// read next line
		$i++;
		$line = trim($group_orig[$i]);
	}

	$entries = explode(":", $line);
	if (!empty($entries[0])) {
		$groupname = $entries[0];
		if (!in_array($groupname, $gforge_groups)) {
			// write the user only if it's not a gforge user
			$unmanaged_lines_group[] = $line;
		}
	} else {
		// blank line or comment
		$unmanaged_lines_group[] = $line;
	}
}

// Now process the GForge groups
// Note that we FORCE the GForge groups to be managed by GForge. This is different than the
// users, where the administrator could move a user outside the #GFORGE markers and manually
// manage the user. This is done because we must add the users to the group, and for this we
// must manage them.

foreach ($groups as $g) {
	$group_name = $g->getUnixName() ;
	$unix_gid = $g->getID() + 50000;	// 50000: hardcoded value (for now).

	$line = $group_name.":x:".$unix_gid.":";

	/* we need to get the project object to check if a project
	 * has a private CVS repository - in which case we need to add
	 * the apache user to the group so that ViewCVS can be used
	 */

	$group_id = $g->getID() ;

	$gmembers =  array () ;
	foreach (RBACEngine::getInstance()->getUsersByAllowedAction ('scm', $group_id, 'write') as $committer) {
		$gmembers[] = $committer->getUnixName() ;
	}
	if ($g->enableAnonSCM()) {
		$gmembers[] = 'anonymous';
	} else {
		$gmembers[] = forge_get_config('apache_user');
	}

	$line .= implode(',', $gmembers);
	$gforge_lines_group[] = $line;
}

// Generate the contents of /etc/group
$group_contents = implode("\n", $unmanaged_lines_group);
$group_contents .= "\n";
$group_contents .= "#GFORGEBEGIN\n";
$group_contents .= implode("\n", $gforge_lines_group);
$group_contents .= "\n#GFORGEEND\n";

/*************************************************************************
 * Step 4: Write all the data
 *************************************************************************/

// Write /etc/passwd
$passwd_file = fopen("/etc/passwd".FILE_EXTENSION, "w");
if ($passwd_file) {
	fwrite($passwd_file, $passwd_contents);
	fclose($passwd_file);
}

// Write /etc/shadow
$shadow_file = fopen("/etc/shadow".FILE_EXTENSION, "w");
if ($shadow_file) {
	fwrite($shadow_file, $shadow_contents);
	fclose($shadow_file);
}

// Write /etc/group
$group_file = fopen("/etc/group".FILE_EXTENSION, "w");
if ($group_file) {
	fwrite($group_file, $group_contents);
	fclose($group_file);
}

cron_entry(16,$err);

?>
