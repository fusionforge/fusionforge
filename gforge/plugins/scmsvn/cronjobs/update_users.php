#! /usr/bin/php5 -f
<?php
/*
 * update_users.php 
 *
 * Robert Nelson <robertn@the-nelsons.org>
 *
 *
 * 	This script creates/updates the users and passwords associated
 *	with the gforge subversion repositories.
 *
 * @version   $Id
 */

require ('squal_pre.php');
require_once('common/include/cron_utils.php');

//	Where is the SVN repository?
$svn=$svndir_prefix;

//the name of the access_file
$access_file = "$svn/%s.access";
$password_file = "$svn/%s.passwd";
$access_root = "/";
$per_group_access = True;

$err = "Creating accounts for subversion repositories at ". $svn."\n";

if (empty($svn) || util_is_root_dir($svn)) {
	$err .=  "Error! svndir_prefix Is Not Set Or Points To The Root Directory!";
	echo $err;
	cron_entry(26,$err);
	exit;
}

$res = db_query("SELECT is_public,enable_anonscm,unix_group_name,groups.group_id 
	FROM groups, plugins, group_plugin 
	WHERE groups.status != 'P' 
	AND groups.group_id=group_plugin.group_id
	AND group_plugin.plugin_id=plugins.plugin_id
	AND plugins.plugin_name='scmsvn'");

if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	cron_entry(26,$err);
	exit;
}

// The content of the access file used by svn authz apache2 module
$access_file_content = '';

while ( $group_row =& db_fetch_array($res) ) {	
	$access_file_content .= add2AccessFile($group_row['group_id']);
	if ($per_group_access) {
		writeAccessFile(sprintf($access_file, $group_row['unix_group_name']), $access_file_content);
		$access_file_content = '';

		// Now generate the contents for the password file
		$res = db_query('SELECT user_name,unix_pw FROM users NATURAL JOIN user_group WHERE group_id=\''.$group_row['group_id'].'\'');
		if (!$res) {
			$err .=  "Error! Database Query Failed: ".db_error();
			echo $err;
			cron_entry(26,$err);
			exit;
		}

		$password_file_content = '';
		while ( $user_row =& db_fetch_array($res) ) {
			if (!empty($user_row['unix_pw']))
				$password_file_content .= $user_row['user_name'].':'.$user_row['unix_pw']."\n";
		}
		writePasswordFile(sprintf($password_file, $group_row['unix_group_name']), $password_file_content);
	}
}

if (!$per_group_access) {
	// Now generate the contents for the password file
	$password_file_contents = '';
	$res = db_query("SELECT * FROM users WHERE user_id IN (SELECT DISTINCT user_id FROM user_group ug, group_plugin gp, plugins p
		WHERE ug.group_id=gp.group_id AND gp.plugin_id=p.plugin_id AND p.plugin_name='scmsvn')");
	if (!$res) {
		$err .=  "Error! Database Query Failed: ".db_error();
		echo $err;
		cron_entry(26,$err);
		exit;
	}

	while ( $row =& db_fetch_array($res) ) {
		if (!empty($row["unix_pw"]))
			$password_file_contents .= $row["user_name"].":".$row["unix_pw"]."\n";
	}

	writeAccessFile($access_file, $access_file_content);
	writePasswordFile($password_file, $password_file_contents);
}

function add2AccessFile($group_id) {
	$result = "";
	$project = &group_get_object($group_id);
	$result = "[". $project->getUnixName(). ":/]\n";
	$users = &$project->getMembers();
	foreach($users as $user ) {
		$perm = &$project->getPermission($user);
		if ( $perm->isCVSWriter() ) {
			$result.= $user->getUnixName() . "= rw\n";
		} else if ( $perm->isCVSReader() ) {
			$result.= $user->getUnixName() . "= r\n";
		}
	}
	if ( $project->enableAnonSCM() ) {
		$result.="anonsvn= r\n";
		$result.="* = r\n";

	}
	$result.="\n";
	return $result;
}

function writeAccessFile($fileName, $access_file_content) {
	$myFile= fopen( $fileName, "w" );
	fwrite ( $myFile, $access_file_content );
	fclose($myFile);
}

function writePasswordFile($fileName, $password_file_contents) {
	$myFile = fopen( $fileName, "w" );
	fwrite ( $myFile, $password_file_contents );
	fwrite ( $myFile, 'anonsvn:$apr1$Kfr69/..$J08mbyNpD81y42x7xlFDm.'."\n");
	fclose($myFile);
}

cron_entry(26,$err);
?>
