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
require_once('common/include/cron_utils.php');

//	/path/to/svn/bin/
$svn_path='/usr/local/svn/bin';

//	Owner of files - apache
$file_owner=$sys_apache_user.':'.$sys_apache_group;

//	Where is the SVN repository?
$svn=$svndir_prefix;

//	Whether to separate directories by first letter like /m/mygroup /a/apple
$first_letter = false;

// Whether to have all projects in a single repository
$one_repository = false;

//if everything is in one repository, we need a working checkout to use
$repos_co = '/var/svn-co';

//type of repository, whether filepassthru or bdb
//$repos_type = ' --fs-type fsfs ';
$repos_type = '';

/*
	This script create the gforge dav/svn/docman repositories
*/



$err .= "Creating Groups at ". $svn."\n";

if (empty($sys_apache_user) || empty($sys_apache_group)) {
	$err .=  "Error! sys_apache_user Is Not Set Or sys_apache_group Is Not Set!";
	echo $err;
	cron_entry(21,$err);
	exit;
}

if (empty($svn) || !preg_match('/[^\\/]/',$svn)) {
	$err .=  "Error! svndir_prefix Is Not Set Or Points To The Root Directory!";
	echo $err;
	cron_entry(21,$err);
	exit;
}

$res = db_query("SELECT is_public,enable_anonscm,unix_group_name 
	FROM groups, plugins, group_plugin 
	WHERE groups.status != 'P' 
	AND groups.group_id=group_plugin.group_id
	AND group_plugin.plugin_id=plugins.plugin_id
	AND plugins.plugin_name='scmsvn'");

if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	cron_entry(21,$err);
	exit;
}

//
//	If using a single large repository, create the checkout if necessary
//


if ($one_repository && !is_dir($repos_co)) {
	$err .= "Error! Checkout Repository Does Not Exist!";
	echo $err;
	cron_entry(21,$err);
	exit;
} elseif (!is_dir($svn)) {
	passthru ("mkdir $svn");
}

while ( $row =& db_fetch_array($res) ) {	
	if ($one_repository) {
		if ($first_letter) {
			//
			//	Create the repository
			//
			passthru ("[ ! -d $repos_co/".$row["unix_group_name"][0]."/ ] && mkdir -p $repos_co/".$row["unix_group_name"][0]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"][0]."/");
			passthru ("[ ! -d $repos_co/".$row["unix_group_name"][0]."/".$row["unix_group_name"]."/ ] && mkdir -p $repos_co/".$row["unix_group_name"][0]."/".$row["unix_group_name"]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"][0]."/".$row["unix_group_name"]."/");
		} else {
			passthru ("[ ! -d $repos_co/".$row["unix_group_name"]." ] && mkdir -p $repos_co/".$row["unix_group_name"]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"]);
		}
		$cmd = 'chown -R '.$file_owner.' '.$repos_co;
		passthru ($cmd);
	} else {
		if ($first_letter) {
			//
			//	Create the repository
			//
			passthru ("[ ! -d $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $svn/".$row["unix_group_name"][0]."/ && $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			svn_hooks("$svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			addsvnmail("$svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"],$row["unix_group_name"]);
		} else {
			passthru ("[ ! -d $svn/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"]);
			svn_hooks("$svn/".$row["unix_group_name"]);
			addsvnmail("$svn/".$row["unix_group_name"],$row["unix_group_name"]);
		}	
		$cmd = 'chown -R '.$file_owner.' '.$svn;
		passthru ($cmd);
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
	$pathsvnmail = dirname($_SERVER['_']).'/commit-email.pl '.' "$REPOS" '.' "$REV" '.$unix_group_name.'-commits@'.$sys_lists_host;
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

if ($one_repository) {
	passthru ("cd $repos_co && $svn_path/svn commit -m\"\"");
}
system("chown $file_owner -R $svn");
system("cd $svn/ && find -type d -exec chmod 700 {} \;");
system("cd $svn/ && find -type f -exec chmod 600 {} \;");

cron_entry(21,$err);
?>
