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

//
//	IMPORTANT - modify this to your correct cron path
//
//define("CRON_PATH","/path/to/gforge/cronjobs");
define("CRON_PATH","/var/www/gforge3/cronjobs");

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
			fwrite($fp,"$forname:	|\"".CRON_PATH."/forum_gateway.php ".db_result($resforum,$forres,'unix_group_name')." ".strtolower(db_result($resforum,$forres,'forum_name'))."\"\n");
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
