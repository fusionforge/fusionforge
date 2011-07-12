#! /usr/bin/php
<?php
/**
 * GForge Mail Aliases Facility
 *
 * Copyright 2002-2004 GForge, LLC
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require dirname(__FILE__).'/../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

define('FILE_EXTENSION','.new'); // use .new when testing

/*

NOTE - THIS SQL CAN BE USED ON A SECOND SERVER TO GRANT ONLY THE NEEDED PERMS


CREATE USER listsuser WITH ENCRYPTED PASSWORD 'password';

GRANT SELECT ON mail_group_list, users, deleted_mailing_lists, forum_group_list, groups, artifact_group_list TO listsuser;

GRANT INSERT, UPDATE ON deleted_mailing_lists TO listsuser;

GRANT UPDATE ON mail_group_list TO listsuser;

GRANT ALL ON project_sums_agg TO listsuser;

*/

// This works only if this file is in cronjobs/mail/
$path_to_cronjobs = dirname(dirname(__FILE__));

// You should also modify this to the correct PHP path and extra configuration (if needed)
$php_command = "/usr/bin/php -d include_path=".ini_get("include_path");


$aliases_orig = file("/etc/aliases");
$aliases = array();
$err = '';

for ($i=0; $i < count($aliases_orig); $i++) {
	$line = trim($aliases_orig[$i]);
	// Skip the GForge aliases (will be written later)
	if (preg_match("/^[[:blank:]]*#GFORGEBEGIN/", $line)) {
		do {
			$i++;
			$line = trim($aliases_orig[$i]);
		} while ($i < count($aliases_orig) && !preg_match("/^[[:blank:]]*#GFORGEEND/", $line));

		// Got to end of file (shouldn't happen, means #GFORGEEND wasn't found on file
		if ($i >= (count($aliases_orig)-1)) break;

		// read next line
		$i++;
		$line = trim($aliases_orig[$i]);
	}

	// empty line or comment
	if (empty($line) || preg_match('/^#/', $line)) continue;

	list($alias_name, $alias) = explode(':', $line, 2);
	$alias_name = trim($alias_name);
	$alias = trim($alias);
	$aliases[$alias_name] = $alias;
}

// Now generate the GForge aliases
$gforge_aliases = array();

//
//	Set up the forum aliases
//
if (forge_get_config('use_forum')) {
	$resforum = db_query_params ('SELECT groups.unix_group_name,lower(fgl.forum_name) AS forum_name
		FROM forum_group_list fgl,groups
		WHERE fgl.group_id=groups.group_id
		AND groups.status=$1',
			array ('A'));
	for ($forres=0; $forres<db_numrows($resforum); $forres++) {
		$forname=strtolower(db_result($resforum,$forres,'unix_group_name').'-'.strtolower(db_result($resforum,$forres,'forum_name')));

		if (array_key_exists($forname, $aliases)) {
			// A GForge alias was found outside the markers
			unset($aliases[$forname]);
		}

		$gforge_aliases[$forname] = '"|'.$php_command." ".$path_to_cronjobs."/forum_gateway.php ".db_result($resforum,$forres,'unix_group_name')." ".strtolower(db_result($resforum,$forres,'forum_name')).'"';
	}
}


//
//	Set up the tracker aliases
//
if (forge_get_config('use_tracker')) {
	$restracker = db_query_params ('SELECT groups.unix_group_name,lower(agl.name) AS tracker_name,group_artifact_id
		FROM artifact_group_list agl, groups
		WHERE agl.group_id=groups.group_id
		AND groups.status=$1',
			array ('A'));
	for ($forres=0; $forres<db_numrows($restracker); $forres++) {
		// first we remove non-alphanumeric characters (spaces and other stuff)
		$formatted_tracker_name = preg_replace('/[^[:alnum:]]/','',db_result($restracker,$forres,'tracker_name'));
		$formatted_tracker_name = strtolower($formatted_tracker_name);

		$trackername=strtolower(db_result($restracker,$forres,'unix_group_name'))."-".$formatted_tracker_name;
		// enclose tracker name with quotes if it has whitespaces
		if (strpos($trackername, ' ') !== false) {
			$trackername = '"'.$trackername.'"';
		}

		if (array_key_exists($trackername, $aliases)) {
			// A GForge alias was found outside the markers
			unset($aliases[$trackername]);
		}

		$gforge_aliases[$trackername] = '"|'.$php_command." ".$path_to_cronjobs."/tracker_gateway.php ".db_result($restracker,$forres,'unix_group_name')." ".strtolower(db_result($restracker,$forres,'group_artifact_id')).'"';
	}
}

if (forge_get_config('use_mail') && file_exists(forge_get_config('data_path').'/dumps/mailman-aliases')
	&& filesize(forge_get_config('data_path').'/dumps/mailman-aliases') > 0) {
	//
	//	Read in the mailman aliases
	//
	$h2 = fopen(forge_get_config('data_path').'/dumps/mailman-aliases',"r");
	$mailmancontents = fread($h2,filesize(forge_get_config('data_path').'/dumps/mailman-aliases'));
	$mailmanlines = explode("\n",$mailmancontents);
	for	($k = 0; $k < count($mailmanlines); $k++) {
		$mailmanline = explode(":",$mailmanlines[$k], 2);

		$alias = trim($mailmanline[0]);
		if (empty($alias)) continue;
		$command = trim($mailmanline[1]);

		if (array_key_exists($alias, $aliases)) {
			// A GForge alias was found outside the markers
			unset($aliases[$alias]);
		}

		$gforge_aliases[$alias] = $command;
	}
	$err .= "\n$k Mailman Lines";
	fclose($h2);
}

//
//	Write out the user aliases
//
$res = db_query_params ('SELECT user_name,email FROM users WHERE status = $1 AND email != $2',
			array ('A',
				''));
$err .= db_error();

$rows=db_numrows($res);


for ($i=0; $i<$rows; $i++) {
	$user = db_result($res,$i,0);
	if (preg_match('/@/', $user)) {
		continue;
	}
	$email = db_result($res,$i,1);

	if (array_key_exists($user, $aliases)) {
		// A GForge alias was found outside the markers
		unset($aliases[$user]);
	}

	$gforge_aliases[$user] = $email;
}


//
// Now write all the aliases
//
$fh = fopen("/etc/aliases".FILE_EXTENSION, "w");
foreach ($aliases as $aliasname => $alias) {
	fwrite($fh, "$aliasname: \t\t $alias\n");
}
fputs($fh, "#GFORGEBEGIN\n");
foreach ($gforge_aliases as $aliasname => $alias) {
	fwrite($fh, "$aliasname: \t\t $alias\n");
}
fputs($fh, "#GFORGEEND\n");
fclose($fh);


db_free_result($res);
$ok = `newaliases`;
$err .= $ok;

cron_entry(17,$err);

?>
