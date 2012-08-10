#! /usr/bin/php
<?php

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
} elseif (is_dir("/usr/lib/mailman")) {
	$sys_path_to_mailman="/usr/lib/mailman";
} else {
    echo "\nsys_path_to_mailman path is not set right for this script!!";
}

//
// Extract the mailing lists that already exist on the system and create
// a "list" of them for use later so we don't try to create ones that 
// already exist
//
$mailing_lists=array();
$mlists_cmd = escapeshellcmd(forge_get_config('mailman_path')."/bin/list_lists");
//$err .= "Command to be executed is $mlists_cmd\n";
$fp = popen ($mlists_cmd,"r");
while (!feof($fp)) {
	$mlist = fgets($fp, 4096);
	if (stristr($mlist,"matching mailing lists") !== FALSE) {
		continue;
	}
	$mlist = trim($mlist);
	if ($mlist <> "") {
		list($listname, $listdesc) = explode(" ",$mlist);	
		$mailing_lists[] = strtolower($listname);
	}
}

// $err .= 'Existing mailing lists : '.implode(', ', $mailing_lists)."\n";

pclose($fp);

$res = db_query_params ('SELECT users.user_name,email,mail_group_list.list_name,
	mail_group_list.password,mail_group_list.status, 
	mail_group_list.group_list_id,mail_group_list.is_public
	FROM mail_group_list,users
	WHERE mail_group_list.list_admin=users.user_id',
			array ());
$err .= db_error();

$rows=db_numrows($res);
//$err .= "$rows rows returned from query\n";

$h1 = fopen(forge_get_config('data_path').'/dumps/mailman-aliases',"w");

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
	$privatize_cmd = escapeshellcmd(forge_get_config('mailman_path').'/bin/config_list -i '.$script_dir.'/privatize_list.py '.$listname);
	$publicize_cmd = escapeshellcmd(forge_get_config('mailman_path').'/bin/config_list -i '.$script_dir.'/publicize_list.py '.$listname);
	
	if (! in_array($listname,$mailing_lists)) {		// New list?
		$err .= "Creating Mailing List: $listname\n";
		//$lcreate_cmd = forge_get_config('mailman_path')."/bin/newlist -q $listname@".forge_get_config('lists_host')." $email $listpassword &> /dev/null";
		$lcreate_cmd = forge_get_config('mailman_path')."/bin/newlist -q $listname $email $listpassword";
		$err .= "Command to be executed is $lcreate_cmd\n";
		passthru($lcreate_cmd, $failed);
		if($failed) {
			$err .= 'Failed to create '.$listname.", skipping\n";
			continue;
		} else {
			db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2 and group_list_id=$3',
					array(MAIL__MAILING_LIST_IS_CREATED,
						MAIL__MAILING_LIST_IS_REQUESTED,
						$grouplistid));
			echo db_error();
			if ($is_commits_list || $public) {
				// Make the *-commits list public
				$err .= "Making ".$listname." public: ".$publicize_cmd."\n";
				passthru($publicize_cmd,$failed);
			} else {
				// Privatize the new list
				$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
				passthru($privatize_cmd,$failed);
			}
			$fixurl_cmd = escapeshellcmd(forge_get_config('mailman_path')."/bin/withlist -l -r fix_url $listname -u ".forge_get_config('lists_host'));
			passthru($fixurl_cmd,$failed);
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
		if ($is_commits_list || $public) {
			// Make the *-commits list public
			$err .= "Making ".$listname." public: ".$publicize_cmd."\n";
			passthru($publicize_cmd,$failed);
		} else {
			// Privatize the new list
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd,$failed);
		}
		$fixurl_cmd = escapeshellcmd(forge_get_config('mailman_path')."/bin/withlist -l -r fix_url $listname -u ".forge_get_config('lists_host'));
		passthru($fixurl_cmd,$failed);
		if (!failed) {
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
		// For already created list, update only if status was changed on the forge to
		// avoid anwanted reset of parameters.

		// Get the mailman info on public/private to change
		if ($is_commits_list || $public) {
			$err .= "Making ".$listname." public: ".$publicize_cmd."\n";
			passthru($publicize_cmd, $failed);
		} elseif (!$public) {
			// Privatize only if it is marked as private
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd, $failed);
		}
		if ($failed) {
			$err .= 'Failed to update '.$listname."\n";
		} else {
			db_query_params('UPDATE mail_group_list set status=$1 WHERE status=$2 and group_list_id=$3',
					array(MAIL__MAILING_LIST_IS_CONFIGURED,
						MAIL__MAILING_LIST_IS_UPDATED,
						$grouplistid));
			echo db_error();
		}
	} elseif ($status == MAIL__MAILING_LIST_PW_RESET_REQUESTED) {
		$change_pw_cmd = escapeshellcmd(forge_get_config ('mailman_path').'/bin/change_pw -l '.$listname);
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
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd,$privatizeFailed);
		}
	}
	
	if(file_exists(forge_get_config('mailman_path').'/mail/mailman')) {
		// Mailman 2.1
		$list_str =
$listname.':              "|'.forge_get_config('mailman_path').'/mail/mailman post '.$listname.'"'."\n"
.$listname.'-admin:        "|'.forge_get_config('mailman_path').'/mail/mailman admin '.$listname.'"'."\n"
.$listname.'-bounces:      "|'.forge_get_config('mailman_path').'/mail/mailman bounces '.$listname.'"'."\n"
.$listname.'-confirm:      "|'.forge_get_config('mailman_path').'/mail/mailman confirm '.$listname.'"'."\n"
.$listname.'-join:         "|'.forge_get_config('mailman_path').'/mail/mailman join '.$listname.'"'."\n"
.$listname.'-leave:        "|'.forge_get_config('mailman_path').'/mail/mailman leave '.$listname.'"'."\n"
.$listname.'-owner:        "|'.forge_get_config('mailman_path').'/mail/mailman owner '.$listname.'"'."\n"
.$listname.'-request:      "|'.forge_get_config('mailman_path').'/mail/mailman request '.$listname.'"'."\n"
.$listname.'-subscribe:    "|'.forge_get_config('mailman_path').'/mail/mailman subscribe '.$listname.'"'."\n"
.$listname.'-unsubscribe:  "|'.forge_get_config('mailman_path').'/mail/mailman unsubscribe '.$listname.'"'."\n\n"
;
	} else {
		// Mailman < 2.1
		$list_str =
$listname.':		"|'.forge_get_config('mailman_path').'/mail/wrapper post '.$listname.'"'."\n"
.$listname.'-admin:	"|'.forge_get_config('mailman_path').'/mail/wrapper mailowner '.$listname.'"'."\n"
.$listname.'-request:	"|'.forge_get_config('mailman_path').'/mail/wrapper mailcmd '.$listname.'"'."\n"
.$listname.'-owner:	'.$listname.'-admin'."\n\n";
	}

	fwrite($h1,$list_str);
}
fclose($h1);

//
//delete mailing lists
//
$res = db_query_params ('SELECT mailing_list_name FROM deleted_mailing_lists WHERE isdeleted = 0',
			array ());
$err .= db_error();
$rows	 = db_numrows($res);

for($k = 0; $k < $rows; $k++) {
	$deleted_mail_list = db_result($res,$k,'mailing_list_name');

	$deleted_mail_list = trim($deleted_mail_list);
	if (!$deleted_mail_list) {
		$err .= "Empty name for a mailing list in 'deleted_mailing_lists' table\n";
		break;
	}
	if (!preg_match('/^[a-z0-9\-_\.]*$/', $deleted_mail_list) || $deleted_mail_list == '.' || $deleted_mail_list == '..') {
		$err .= 'Invalid List Name: ' . $deleted_mail_list;
		break;
	}

	exec(forge_get_config('mailman_path')."/bin/rmlist -a '$deleted_mail_list'", $output);
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
	if($success){
		$res1 = db_query_params ('UPDATE deleted_mailing_lists SET isdeleted = 1 WHERE mailing_list_name = $1',
			array ($deleted_mail_list));
		$err .= db_error();
	}else{
		$err .= "Could not remove the list $deleted_mail_list \n";
	}
}


cron_entry(18,$err);

?>
