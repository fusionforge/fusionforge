#! /usr/bin/php
<?php

/**
 * Mailing List Creation Cronjob
 *
 * Copyright 2000-2010, Fusionforge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
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
	$path_to_mailman = forge_get_config('mailman_path');
} elseif (is_dir("/usr/lib/mailman")) {
	$path_to_mailman = "/usr/lib/mailman";
} else {
	$err .= "\npath_to_mailman path is not set right for this script!!";
	cron_entry(18, $err);
	exit;
}

$custom_file = forge_get_config('custom_path').'/mailman-config_list.conf';

$res = db_query_params('SELECT users.user_name,email,mail_group_list.list_name,
			mail_group_list.password,mail_group_list.status,
			mail_group_list.group_list_id,mail_group_list.is_public,
			mail_group_list.description
			FROM mail_group_list,users
			WHERE mail_group_list.list_admin=users.user_id
			AND mail_group_list.status != $1',
			array (MAIL__MAILING_LIST_IS_CONFIGURED));
$err .= db_error();

$rows = db_numrows($res);
//$err .= "$rows rows returned from query\n";

if (!is_dir(forge_get_config('data_path').'/dumps')) {
	mkdir(forge_get_config('data_path').'/dumps', 0755, true);
}
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
	$description = db_result($res, $i, 'description');

	$listname = trim($listname);
	if (!$listname) {
		$err .= "Empty name for a mailing list in 'mail_group_list' table\n";
		break;
	}
	if (!preg_match('/^[a-z0-9\-_\.]*$/', $listname) || $listname == '.' || $listname == '..') {
		$err .= 'Invalid List Name: ' . $listname;
		break;
	}

	if ($status == MAIL__MAILING_LIST_IS_REQUESTED) {	// New list?
		$err .= "Creating Mailing List: $listname\n";
		//$lcreate_cmd = $path_to_mailman."/bin/newlist -q $listname@".forge_get_config('lists_host')." $email $listpassword &> /dev/null";
		$lcreate_cmd = $path_to_mailman."/bin/newlist -q $listname $email $listpassword >/dev/null";
		$err .= "Command to be executed is $lcreate_cmd\n";
		passthru($lcreate_cmd, $failed);
		if ($failed) {
			$err .= 'Failed to create '.$listname.", skipping\n";
			continue;
		} else {
			db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2 and group_list_id=$3',
					array(MAIL__MAILING_LIST_IS_CREATED,
						MAIL__MAILING_LIST_IS_REQUESTED,
						$grouplistid));
			echo db_error();
			$tmp = tempnam(forge_get_config('data_path'), "tmp");
			$fh = fopen($tmp,'w');
			$listConfig = "description = \"$description\"\n" ;
			$listConfig .= "host_name = '".forge_get_config('lists_host')."'\n" ;
			if (!$public) {
				$listConfig .= "archive_private = True\n" ;
				$listConfig .= "advertised = False\n" ;
				$listConfig .= "subscribe_policy = 3\n" ;
				## Reject mails sent by non-members
				$listConfig .= "generic_nonmember_action = 2\n";
				## Do not forward auto discard message
				$listConfig .= "forward_auto_discards = 0\n";
			} else {
				$listConfig .= "archive_private = False\n" ;
				$listConfig .= "advertised = True\n" ;
				$listConfig .= "subscribe_policy = 1\n" ;
			}
			fwrite($fh, $listConfig);
			if (is_readable($custom_file)) fwrite($fh, file_get_contents($custom_file));
			fclose($fh);
			$config_cmd = escapeshellcmd($path_to_mailman."/bin/config_list -i $tmp $listname");
			passthru($config_cmd, $failed);
			unlink($tmp);
			if ($failed) {
				$err .= 'Failed to configure '.$listname.", skipping\n";
				continue;
			}
			$fixurl_cmd = escapeshellcmd($path_to_mailman."/bin/withlist -l -r fix_url $listname -u ".forge_get_config('lists_host'));
			passthru("$fixurl_cmd >/dev/null 2>/dev/null", $failed);
			if (!$failed) {
				db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2 and group_list_id=$3',
						array(MAIL__MAILING_LIST_IS_CONFIGURED,
							MAIL__MAILING_LIST_IS_CREATED,
							$grouplistid));
				echo db_error();
			} else {
				$err .= 'Failed to configure '.$listname."\n";
				continue;
			}
		}
		$mailingListIds[] = $grouplistid;
	} elseif ($status == MAIL__MAILING_LIST_IS_CREATED) {
		$tmp = tempnam(forge_get_config('data_path'), "tmp");
		$fh = fopen($tmp,'w');
		$listConfig = "description = \"$description\"\n" ;
		$listConfig .= "host_name = '".forge_get_config('lists_host')."'\n";
		if (!$public) {
			$listConfig .= "archive_private = True\n";
			$listConfig .= "advertised = False\n";
			$listConfig .= "subscribe_policy = 3\n";
			## Reject mails sent by non-members
			$listConfig .= "generic_nonmember_action = 2\n";
			## Do not forward auto discard message
			$listConfig .= "forward_auto_discards = 0\n";
		} else {
			$listConfig .= "archive_private = False\n";
			$listConfig .= "advertised = True\n";
			$listConfig .= "subscribe_policy = 1\n";
		}
		if (is_readable($custom_file)) fwrite($fh, file_get_contents($custom_file));
		fwrite($fh, $listConfig);
		fclose($fh);
		$config_cmd = escapeshellcmd($path_to_mailman."/bin/config_list -i $tmp $listname");
		passthru($config_cmd, $failed);
		unlink($tmp);
		if (!$failed) {
			db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2 and group_list_id=$3',
					array(MAIL__MAILING_LIST_IS_CONFIGURED,
						MAIL__MAILING_LIST_IS_CREATED,
						$grouplistid));
			echo db_error();
		} else {
			$err .= 'Failed to configure '.$listname."\n";
			continue;
		}
	} elseif ($status == MAIL__MAILING_LIST_IS_UPDATED) {
		$tmp = tempnam(forge_get_config('data_path'), "tmp");
		$tmp = tempnam(forge_get_config('data_path'), "tmp");
		$fh = fopen($tmp,'w');
		$listConfig = "description = \"$description\"\n" ;
		$listConfig .= "host_name = '".forge_get_config('lists_host')."'\n" ;
		if (!$public) {
			$listConfig .= "archive_private = True\n" ;
			$listConfig .= "advertised = False\n" ;
			$listConfig .= "subscribe_policy = 3\n" ;
			## Reject mails sent by non-members
			$listConfig .= "generic_nonmember_action = 2\n";
			## Do not forward auto discard message
			$listConfig .= "forward_auto_discards = 0\n";
		} else {
			$listConfig .= "archive_private = False\n" ;
			$listConfig .= "advertised = True\n" ;
			$listConfig .= "subscribe_policy = 1\n" ;
		}
		fwrite($fh, $listConfig);
		fclose($fh);
		$config_cmd = escapeshellcmd($path_to_mailman."/bin/config_list -i $tmp $listname");
		passthru($config_cmd, $failed);
		unlink($tmp);
		if (!$failed) {
			db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2 and group_list_id=$3',
					array(MAIL__MAILING_LIST_IS_CONFIGURED,
						MAIL__MAILING_LIST_IS_UPDATED,
						$grouplistid));
			echo db_error();
		} else {
			$err .= 'Failed to configure '.$listname."\n";
			continue;
		}
	} elseif ($status == MAIL__MAILING_LIST_PW_RESET_REQUESTED) {
		$change_pw_cmd = escapeshellcmd($path_to_mailman.'/bin/change_pw -l '.$listname);
		$err .= "Resetting password of ".$listname."\n";
		exec($change_pw_cmd, $returnnewpasswd, $failed);
		if ($failed) {
			$err .= 'Failed to reset password of '.$listname."\n";
		} else {
			$arrayReturnNewPasswd = explode(' ', $returnnewpasswd[0]);
			$newpasswd = trim(end($arrayReturnNewPasswd));
			db_query_params('UPDATE mail_group_list set (status, password) = ($1, $2)  WHERE status=$3 and group_list_id=$4',
					array(MAIL__MAILING_LIST_IS_CONFIGURED,
						$newpasswd,
						MAIL__MAILING_LIST_PW_RESET_REQUESTED,
						$grouplistid));
			echo db_error();
		}
	} else {	// Old list
		if (!$public) {
			// Privatize only if it is marked as private
			$err .= "Privatizing ".$listname."\n";
			$tmp = tempnam(forge_get_config('data_path'), "tmp");
			$fh = fopen($tmp,'w');
			$listConfig = "description = \"$description\"\n" ;
			$listConfig .= "host_name = '".forge_get_config('lists_host')."'\n" ;
			$listConfig .= "archive_private = True\n" ;
			$listConfig .= "advertised = False\n" ;
			$listConfig .= "subscribe_policy = 3\n" ;
			## Reject mails sent by non-members
			$listConfig .= "generic_nonmember_action = 2\n";
			## Do not forward auto discard message
			$listConfig .= "forward_auto_discards = 0\n";
			fwrite($fh, $listConfig);
			fclose($fh);
			$privatize_cmd = escapeshellcmd($path_to_mailman."/bin/config_list -i $tmp $listname");
			passthru($privatize_cmd, $privatizeFailed);
			if ($privatizeFailed) {
				$err .= 'Failed to privatize '.$listname."\n";
			}
			unlink($tmp);
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

	$rm_cmd = forge_get_config('mailman_path')."/bin/rmlist -a $deleted_mail_list >/dev/null";
	$err .= "Command to be executed is $rm_cmd";
	passthru($rm_cmd, $failed);
	if($failed) {
		$err .= 'Failed to remove '.$listname.", skipping\n";
		echo $err;
		continue;
	}
	$success = false;
	if (!file_exists(forge_get_config('mailman_data_path')."/lists/$deleted_mail_list")) {
		$success = true;
	}

	if($success) {
		$res1 = db_query_params('UPDATE deleted_mailing_lists SET isdeleted = 1 WHERE mailing_list_name = $1',
			array($deleted_mail_list));
		$err .= db_error();
	} else {
		$err .= "Could not remove the list $deleted_mail_list \n";
	}
}

cron_entry(18, $err);

?>
