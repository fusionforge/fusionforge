#! /usr/bin/php4 -f
<?php
/**
 * create_docman.php 
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 *
 * @version   $Id
 */

require ('squal_pre.php');
require_once('/etc/gforge/local.inc');

//	/path/to/svn/bin/
$svn_path='/usr/local/svn/bin';

//	Owner of files - apache
$file_owner='nobody:nogroup';

//	Where is the SVN repository?
$svn='/var/svn';

//	Whether to separate directories by first letter like /m/mygroup /a/apple
$first_letter = false;

/*
	This script create the gforge dav/svn/docman repositories
*/

echo "Creating Groups at ". $svn."\n";

$res = db_query("SELECT is_public,enable_anonscm,unix_group_name 
	FROM groups WHERE status != 'P';");

if (!$res) {
	echo "Error!\n";
}

system("[ ! -d ".$svn." ] && mkdir $svn"); 

while ( $row =& db_fetch_array($res) ) {
	echo "Name:".$row["unix_group_name"]." \n";
	if ($first_letter) {
		//
		//	Create the docman repository for versioning of docs
		//
		system ("[ ! -d $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $svn/".$row["unix_group_name"][0]."/ && $svn_path/svnadmin create $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 		svn_hooks("$svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 		addsvnmail("$svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"],$row["unix_group_name"]);
	} else {
		system ("[ ! -d $svn/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $svn/".$row["unix_group_name"]);
		svn_hooks("$svn/".$row["unix_group_name"]);
		addsvnmail("$svn/".$row["unix_group_name"],$row["unix_group_name"]);
	}
}

/**
* addsvnmail($filePath,$unix_group_name)
* This function add the commit-email.pl into post-commit
* The commit-email.pl must be in same directory of this script
* Copyright 2004 (c) GForge
* @autor Luis Alberto Hurtado Alvarado <luis@gforgegroup.com>
* @param $filePath The path to svn repository
* @param $unix_group_name The project name.
*/
function addsvnmail($filePath,$unix_group_name) {
	global $sys_lists_host;
	$pathsvnmail = dirname($_SERVER['SCRIPT_FILENAME']).'/commit-email.pl '.' "$REPOS" '.' "$REV" '.$unix_group_name.'-commits@'.$sys_lists_host;
	writeFile($filePath.'/hooks/post-commit',$pathsvnmail);
}

/**
* svn_hooks($filePath)
* This function create the post-commit file in svn hooks
* Copyright 2004 (c) GForge
* @autor Luis Alberto Hurtado Alvarado <luis@gforgegroup.com>
* @param $filePath The path to svn repository
*/
function svn_hooks($filePath) {
	system ("cp $filePath/hooks/post-commit.tmpl $filePath/hooks/post-commit");
	system("chmod +x ".$filePath."/hooks/post-commit");
}

/**
* writeFile($filePath, $content)
* This function add the mail
* Copyright 2004 (c) GForge
* @autor Luis Alberto Hurtado Alvarado <luis@gforgegroup.com>
* @param $filePath The path to svn repository
* @param $content The mail
*/
function writeFile($filePath, $content) {
	$file = fopen($filePath, 'a');
	flock($file, LOCK_EX);
	ftruncate($file, 0);
	rewind($file);
	if(!empty($content)) {
		fwrite($file, '#!/bin/sh'."\n");
		fwrite($file, 'REPOS="$1"'."\n");
		fwrite($file, 'REV="$2"'."\n");
		fwrite($file, $content);
	}
	flock($file, LOCK_UN);
	fclose($file);
}

system("chown $file_owner -R $svn");
system("cd $svn/ ; find -type d -exec chmod 700 {} \;");
system("cd $svn/ ; find -type f -exec chmod 600 {} \;");

?>
