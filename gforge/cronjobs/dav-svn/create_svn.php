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
$svn_path='/usr/bin';

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

if (empty($svn) || util_is_root_dir($svn)) {
	$err .=  "Error! svndir_prefix Is Not Set Or Points To The Root Directory!";
	echo $err;
	cron_entry(21,$err);
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
		$project = &group_get_object($row["group_id"]); // get the group object for the current group
		if ( (!$project) || (!is_object($project))  )  {
			echo "Error Getting Group." . " Id : " . $row["group_id"] . " , Name : " . $row["unix_group_name"];
			break; // continue to the next project
		}		
		if ($first_letter) {
			//
			//	Create the repository
			//
			passthru ("[ ! -d $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $svn/".$row["unix_group_name"][0]."/ && $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			if ($project->usesPlugin('svncommitemail')) {
 				check_svn_mail($row["unix_group_name"], $svn."/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			}
 			if ($project->usesPlugin('svntracker')) {
 				check_svn_tracker($row["unix_group_name"], $svn."/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
 			}
		} else {
			passthru ("[ ! -d $svn/".$row["unix_group_name"]." ] &&  $svn_path/svnadmin create $repos_type $svn/".$row["unix_group_name"]);
			$cmd = 'chown -R '.$file_owner.' '.$svn.'/'.$row["unix_group_name"];
			passthru($cmd); // svn dir owned by apache or viewcvs doesn´t work 
			if ($project->usesPlugin('svncommitemail')) {
 				check_svn_mail($row["unix_group_name"], $svn."/".$row["unix_group_name"]);
			}
			if ($project->usesPlugin('svntracker')) {
				check_svn_tracker($row["unix_group_name"], $svn."/".$row["unix_group_name"]);
			}
		}	
		$cmd = 'chown -R '.$file_owner.' '.$svn;
		passthru ($cmd);
	}
}

function check_svn_tracker($project, $repos) {
	
	$contents = @file_get_contents($repos."/hooks/post-commit");	
	if ( strstr($contents, "svntracker") == FALSE ) {
		add_svn_tracker_to_repository($project,$repos);
	}
}

function add_svn_tracker_to_repository($project,$repos) {
	global $sys_plugins_path,$file_owner;
	
	if (file_exists($repos.'/hooks/post-commit')) {
		$FOut = fopen($repos.'/hooks/post-commit', "a+");
	} else {
		$FOut = fopen($repos.'/hooks/post-commit', "w");
		$Line = '#!/bin/sh'."\n"; // add this line to first line or else the script fails
	}
	if($FOut) {
		$Line .= '
#begin added by svntracker'.
"\n/usr/bin/php -d include_path=".ini_get('include_path').
				" ".$sys_plugins_path. "/svntracker/bin/post.php".  ' "'.$repos.'" "$2"
#end added by svntracker';
		fwrite($FOut,$Line);
		`chmod +x $repos'/hooks/post-commit'`;
		`chmod 700 $repos'/hooks/post-commit'`;
		`chown $file_owner $repos'/hooks/post-commit'`;
		fclose($FOut);
	}
}

function check_svn_mail($project, $repos) {
	$contents = @file_get_contents($repos."/hooks/post-commit");
	if ( strstr($contents, "svncommitemail") == FALSE ) {
		add_svn_mail_to_repository($project,$repos);
	}
}

function add_svn_mail_to_repository($unix_group_name,$repos) {
	global $sys_lists_host,$file_owner,$sys_plugins_path;
	
	if (file_exists($repos.'/hooks/post-commit')) {
		$FOut = fopen($repos.'/hooks/post-commit', "a+");
	} else {
		$FOut = fopen($repos.'/hooks/post-commit', "w");
		$Line = '#!/bin/sh'."\n"; // add this line to first line or else the script fails
	}
	
	if($FOut) {
		$Line .= '
#begin added by svncommitemail
'.$sys_plugins_path.'/svncommitemail/bin/commit-email.pl '.$repos.' "$2" '.$unix_group_name.'-commits@'.$sys_lists_host.'
#end added by svncommitemail';
		fwrite($FOut,$Line);
		`chmod +x $repos'/hooks/post-commit'`;
		`chmod 700 $repos'/hooks/post-commit'`;
		`chown $file_owner $repos'/hooks/post-commit'`;
		fclose($FOut);
	}
}

if ($one_repository) {
	passthru ("cd $repos_co && $svn_path/svn commit -m\"\"");
}
system("chown $file_owner -R $svn");
#system("cd $svn/ && find -type d -exec chmod 700 {} \;");
#system("cd $svn/ && find -type f -exec chmod 600 {} \;");

cron_entry(21,$err);
?>
