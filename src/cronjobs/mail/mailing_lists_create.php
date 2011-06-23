#! /usr/bin/php
<?php

/**
 * Mailing List Creation Cronjob
 *
 * Copyright 2000-2010, Fusionforge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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

//
//	This script will read in a list existing mailing lists, then add the new lists
//	and, finally, create the lists in a /var/lib/gforge/dumps/mailman-aliases file
//	The /var/lib/gforge/dumps/mailman-aliases file will then be read by the mailaliases.php file
//

require dirname(__FILE__).'/../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err = '';

if (is_dir(forge_get_config('mailman_path'))) {
	$path_to_mailman=forge_get_config('mailman_path');
} elseif (is_dir("/usr/lib/mailman")) {
	$path_to_mailman="/usr/lib/mailman";
} else {
	echo "\npath_to_mailman path is not set right for this script!!";
}

//
// Extract the mailing lists that already exist on the system and create
// a "list" of them for use later so we don't try to create ones that
// already exist
//
$mailing_lists=array();
$mlists_cmd = escapeshellcmd($path_to_mailman."/bin/list_lists");
//$err .= "Command to be executed is $mlists_cmd\n";
$fp = popen($mlists_cmd, "r");
while (!feof($fp)) {
	$mlist = fgets($fp, 4096);
	if (stristr($mlist,"matching mailing lists") !== FALSE) {
		continue;
	}
	$mlist = trim($mlist);
	if ($mlist <> "") {
		list($listname, $listdesc) = explode(" ", $mlist);
		$mailing_lists[] = strtolower($listname);
	}
}

// $err .= 'Existing mailing lists : '.implode(', ', $mailing_lists)."\n";

pclose($fp);

$res = db_query_params('SELECT users.user_name,email,mail_group_list.list_name,
			mail_group_list.password,mail_group_list.status,
			mail_group_list.group_list_id,mail_group_list.is_public
			FROM mail_group_list,users
			WHERE mail_group_list.list_admin=users.user_id',
			array ());
$err .= db_error();

$rows = db_numrows($res);
//$err .= "$rows rows returned from query\n";

$h1 = fopen(forge_get_config('data_path').'/dumps/mailman-aliases', "w");

$mailingListIds = array();

for ($i=0; $i<$rows; $i++) {
	$listadmin = db_result($res,$i,'user_name');
	$email = db_result($res,$i,'email');
	$listname = strtolower(db_result($res,$i,'list_name'));
	$listpassword = db_result($res,$i,'password');
	$grouplistid = db_result($res,$i,'group_list_id');
	$public = db_result($res,$i,'is_public');
	$status = db_result($res,$i,'status');

	$listname = trim($listname);
	if (!$listname) {
		$err .= "Empty name for a mailing list in 'mail_group_list' table\n";
		break;
	}
	if (!preg_match('/^[a-z0-9\-_\.]*$/', $listname) || $listname == '.' || $listname == '..') {
		$err .= 'Invalid List Name: ' . $listname;
		break;
	}

	$is_commits_list = preg_match('/-commits$/', $listname);

	// Hack to Disable auto-public of listname.
	$is_commits_list = false;

	// Here we assume that the privatize_list.py script is located in the same dir as this script
	$script_dir = dirname(__FILE__);
	$privatize_cmd = escapeshellcmd($path_to_mailman.'/bin/config_list -i '.$script_dir.'/privatize_list.py '.$listname);
	$publicize_cmd = escapeshellcmd($path_to_mailman.'/bin/config_list -i '.$script_dir.'/publicize_list.py '.$listname);

	if (!in_array($listname,$mailing_lists)) {	// New list?
		$err .= "Creating Mailing List: $listname\n";
		//$lcreate_cmd = $path_to_mailman."/bin/newlist -q $listname@".forge_get_config('lists_host')." $email $listpassword &> /dev/null";
		$lcreate_cmd = $path_to_mailman."/bin/newlist -q $listname $email $listpassword";
		$err .= "Command to be executed is $lcreate_cmd\n";
		passthru($lcreate_cmd, $failed);
		if ($failed) {
			$err .= 'Failed to create '.$listname.", skipping\n";
			echo $err;
			continue;
		} else {
			if ($is_commits_list || $public) {
				// Make the *-commits list public
				$err .= "Making ".$listname." public: ".$publicize_cmd."\n";
				passthru($publicize_cmd, $publicizeFailed);
			} else {
				// Privatize the new list
				$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
				passthru($privatize_cmd, $privatizeFailed);
			}
		}
		$mailingListIds[] = $grouplistid;
	} elseif ($status == MAIL__MAILING_LIST_IS_UPDATED) {
		// For already created list, update only if status was changed on the forge to
		// avoid anwanted reset of parameters.

		// Get the mailman info on public/private to change
		if ($is_commits_list || $public) {
			$err .= "Making ".$listname." public: ".$publicize_cmd."\n";
			passthru($publicize_cmd,$publicizeFailed);
		} elseif (!$public) {
			// Privatize only if it is marked as private
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd,$privatizeFailed);
		}
	} elseif ($status == MAIL__MAILING_LIST_PW_RESET_REQUESTED) {
		$change_pw_cmd = escapeshellcmd($path_to_mailman.'/bin/change_pw -l '.$listname);
		$err .= "Resetting password of ".$listname."\n";
		passthru($change_pw_cmd, $failed);
		if ($failed) {
			$err .= 'Failed to reset password of '.$listname."\n";
		}
	} else {	// Old list
		if (!$public) {
			// Privatize only if it is marked as private
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd,$privatizeFailed);
		}
	}

	if(file_exists($path_to_mailman.'/mail/mailman')) {
		// Mailman 2.1
		$list_str =
$listname.':              "|'.$path_to_mailman.'/mail/mailman post '.$listname.'"'."\n"
.$listname.'-admin:        "|'.$path_to_mailman.'/mail/mailman admin '.$listname.'"'."\n"
.$listname.'-bounces:      "|'.$path_to_mailman.'/mail/mailman bounces '.$listname.'"'."\n"
.$listname.'-confirm:      "|'.$path_to_mailman.'/mail/mailman confirm '.$listname.'"'."\n"
.$listname.'-join:         "|'.$path_to_mailman.'/mail/mailman join '.$listname.'"'."\n"
.$listname.'-leave:        "|'.$path_to_mailman.'/mail/mailman leave '.$listname.'"'."\n"
.$listname.'-owner:        "|'.$path_to_mailman.'/mail/mailman owner '.$listname.'"'."\n"
.$listname.'-request:      "|'.$path_to_mailman.'/mail/mailman request '.$listname.'"'."\n"
.$listname.'-subscribe:    "|'.$path_to_mailman.'/mail/mailman subscribe '.$listname.'"'."\n"
.$listname.'-unsubscribe:  "|'.$path_to_mailman.'/mail/mailman unsubscribe '.$listname.'"'."\n\n"
;
	} else {
		// Mailman < 2.1
		$list_str =
$listname.':		"|'.$path_to_mailman.'/mail/wrapper post '.$listname.'"'."\n"
.$listname.'-admin:	"|'.$path_to_mailman.'/mail/wrapper mailowner '.$listname.'"'."\n"
.$listname.'-request:	"|'.$path_to_mailman.'/mail/wrapper mailcmd '.$listname.'"'."\n"
.$listname.'-owner:	'.$listname.'-admin'."\n\n";
	}

	fwrite($h1, $list_str);
}

db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2',
		 array(MAIL__MAILING_LIST_IS_CREATED,
			MAIL__MAILING_LIST_IS_REQUESTED));
echo db_error();

fclose($h1);

//
//delete mailing lists
//
$res = db_query_params('SELECT mailing_list_name FROM deleted_mailing_lists WHERE isdeleted = 0',
			array());
$err .= db_error();
$rows = db_numrows($res);

for($k = 0; $k < $rows; $k++) {
	$deleted_mail_list = db_result($res, $k, 'mailing_list_name');

	$deleted_mail_list = trim($deleted_mail_list);
	if (!$deleted_mail_list) {
		$err .= "Empty name for a mailing list in 'deleted_mailing_lists' table\n";
		break;
	}
	if (!preg_match('/^[a-z0-9\-_\.]*$/', $deleted_mail_list) || $deleted_mail_list == '.' || $deleted_mail_list == '..') {
		$err .= 'Invalid List Name: ' . $deleted_mail_list;
		break;
	}

	exec($path_to_mailman."/bin/rmlist -a '$deleted_mail_list'", $output);
	$success = false;
	foreach ($output as $line) {
		// Mailman 2.1.x
		if (preg_match("/to finish removing/i", $line)) {
			$success = true;
			break;
		}
		// Mailman 2.1.0
		if (preg_match("/removing list info/i", $line)) {
			$success = true;
			break;
		}
	}
	if($success) {
		$res1 = db_query_params('UPDATE deleted_mailing_lists SET isdeleted = 1 WHERE mailing_list_name = $1',
					array($deleted_mail_list));
		$err .= db_error();
	} else {
		$err .= "Could not remove the list $deleted_mail_list \n";
	}
}

cron_entry(18,$err);

?>
