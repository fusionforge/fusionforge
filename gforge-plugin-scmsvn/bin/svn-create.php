#! /usr/bin/php4 -f
<?php
/**
 *	This file will create blank SVN repositories for the SVN-over-ssh 
 *  authentication method.
 *
 * Copyright 2003 (c) GForge, LLC
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');

//	/path/to/svn/bin/
$svn_path='/usr/bin';

//	Where is the SVN repository?
$svn='/var/svn';

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

$res = db_query("SELECT is_public,enable_anonscm,unix_group_name 
	FROM groups, plugins, group_plugin 
	WHERE groups.status != 'P' 
	AND groups.group_id=group_plugin.group_id
	AND group_plugin.plugin_id=plugins.plugin_id
	AND plugins.plugin_name='scmsvn'");

if (!$res) {
	$err .=  "Error! Database Query Failed: ".db_error();
	echo $err;
	exit;
}

//
//	If using a single large repository, create the checkout if necessary
//
if ($one_repository && !is_dir($repos_co)) {
	$err .= "Error! Checkout Repository Does Not Exist!";
	echo $err;
	exit;
} elseif (!is_dir($svn)) {
	passthru ("mkdir $svn");
}

while ( $row =& db_fetch_array($res) ) {
	if ($one_repository) {
		passthru ("[ ! -d $repos_co/".$row["unix_group_name"]." ] && mkdir -p $repos_co/".$row["unix_group_name"]."/ && $svn_path/svn add $repos_co/".$row["unix_group_name"]);
	} else {
		passthru ("[ ! -d $svn/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"]);
		svn_hooks("$svn/".$row["unix_group_name"]);
		addsvnmail("$svn/".$row["unix_group_name"],$row["unix_group_name"]);
		passthru("chown ".$row["unix_group_name"].":".$row["unix_group_name"]." -R $svn/".$row["unix_group_name"]."/");
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

cron_entry(21,$err);
?>
