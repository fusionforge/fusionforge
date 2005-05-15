#! /usr/bin/php4 -f
<?php
/**
 * GForge Mail Aliases Facility
 *
 * Copyright 2002-2004 GForge, LLC
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

require ('squal_pre.php');
require ('common/include/cron_utils.php');


/*

NOTE - THIS SQL CAN BE USED ON A SECOND SERVER TO GRANT ONLY THE NEEDED PERMS


CREATE USER listsuser WITH ENCRYPTED PASSWORD 'password';

GRANT SELECT ON mail_group_list, users, deleted_mailing_lists, forum_group_list, groups, artifact_group_list TO listsuser;

GRANT INSERT, UPDATE ON deleted_mailing_lists TO listsuser;

*/

// This works only if this file is in cronjobs/mail/
$path_to_cronjobs = dirname(dirname(__FILE__));

// You should also modify this to the correct PHP path and extra configuration (if needed)
$php_command = "/usr/bin/php4 -d include_path=".ini_get("include_path");


if (!file_exists('/etc/aliases.org')) {
	$err .= "CANNOT PROCEED - you must first backup your /etc/aliases file";
	exit;
}

//
//	Write out all the aliases
//
$fp = fopen("/etc/aliases","w");
if (!($fp)) {
	$err .= ("ERROR: unable to open target file\n");
	exit;
}

//
//	Read in the "default" aliases
//
$h = fopen("/etc/aliases.org","r");
$aliascontents = fread($h,filesize("/etc/aliases.org"));
$aliaslines = explode("\n",$aliascontents);
for($k = 0; $k < count($aliaslines); $k++) {
	$aliasline = explode(":",$aliaslines[$k]);
	$def_aliases[strtolower($aliasline[0])]=1;
	fwrite($fp,$aliaslines[$k]."\n");
}
$err .= "\n$k Alias Lines";
fclose($h);

//
//	Set up the forum aliases
//
if ($sys_use_forum) {
	$resforum=db_query("SELECT groups.unix_group_name,lower(fgl.forum_name) AS forum_name
		FROM forum_group_list fgl,groups
		WHERE fgl.group_id=groups.group_id
		AND groups.status='A'");
	for ($forres=0; $forres<db_numrows($resforum); $forres++) {
		$forname=strtolower(db_result($resforum,$forres,'unix_group_name').'-'.strtolower(db_result($resforum,$forres,'forum_name')));
		if ($def_aliases[$forname]) {
			//alias is already taken - perhaps by default
		} else {
			$def_aliases[$forname]=1;
			fwrite($fp,"$forname:	|\"".$php_command." ".$path_to_cronjobs."/forum_gateway.php ".db_result($resforum,$forres,'unix_group_name')." ".strtolower(db_result($resforum,$forres,'forum_name'))."\"\n");
		}
	}
}


//
//	Set up the tracker aliases
//
if ($sys_use_tracker) {
	$restracker=db_query("SELECT groups.unix_group_name,lower(agl.name) AS tracker_name,group_artifact_id
		FROM artifact_group_list agl, groups
		WHERE agl.group_id=groups.group_id
		AND groups.status='A'");
	for ($forres=0; $forres<db_numrows($restracker); $forres++) {
		// first we remove non-alphanumeric characters (spaces and other stuff)
		$formatted_tracker_name = preg_replace('/[^[:alnum:]]/','',db_result($restracker,$forres,'tracker_name'));
		$formatted_tracker_name = strtolower($formatted_tracker_name);
		
		$trackername=strtolower(db_result($restracker,$forres,'unix_group_name'))."-".$formatted_tracker_name;
		// enclose tracker name with quotes if it has whitespaces
		if (strpos($trackername, ' ') !== false) {
			$trackername = '"'.$trackername.'"';
		}
		if ($def_aliases[$trackername]) {
			//alias is already taken - perhaps by default
		} else {
			$def_aliases[$trackername]=1;
			fwrite($fp,"$trackername:	|\"".$php_command." ".$path_to_cronjobs."/tracker_gateway.php ".db_result($restracker,$forres,'unix_group_name')." ".strtolower(db_result($restracker,$forres,'group_artifact_id'))."\"\n");
		}
	}
}

if ($sys_use_mail) {
	//
	//	Read in the mailman aliases
	//
	$h2 = fopen("/tmp/mailman-aliases","r");
	$mailmancontents = fread($h2,filesize("/tmp/mailman-aliases"));
	$mailmanlines = explode("\n",$mailmancontents);
	for	($k = 0; $k < count($mailmanlines); $k++) {
		$mailmanline = explode(":",$mailmanlines[$k]);
		if ($def_aliases[strtolower($mailmanline[0])]) {
			//alias is already taken - perhaps by default
		} else {
			$def_aliases[strtolower($mailmanline[0])]=1;
			fwrite($fp,$mailmanlines[$k]."\n");
		}
	}
	$err .= "\n$k Mailman Lines";
	fclose($h2);
}

//
//	Write out the user aliases
//
$res=db_query("SELECT user_name,email FROM users WHERE status = 'A' AND email != ''");
$err .= db_error();

$rows=db_numrows($res);


for ($i=0; $i<$rows; $i++) {
	$user = db_result($res,$i,0);
    $email = db_result($res,$i,1);
	if ($def_aliases[$user]) {
		//alias is already taken - perhaps by default or by a mailing list
	} else {
		fwrite($fp, $user . ": " . $email . "\n");
	}
}

fclose($fp);

db_free_result($res);
$ok = `newaliases`;
$err .= $ok;

cron_entry(17,$err);

?>
