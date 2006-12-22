#! /usr/bin/php4 -f
<?php

//
//	This script will read in a list existing mailing lists, then add the new lists
//	and, finally, create the lists in a /tmp/mailman-aliases file
//	The /tmp/mailman-aliases file will then be read by the mailaliases.php file
//

require ('squal_pre.php');
require ('common/include/cron_utils.php');

//
// Extract the mailing lists that already exist on the system and create
// a "list" of them for use later so we don't try to create ones that 
// already exist
//
$mailing_lists=array();
$mlists_cmd = escapeshellcmd($sys_path_to_mailman."/bin/list_lists");
$err .= "Command to be executed is $mlists_cmd\n";
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

$err .= 'Existing mailing lists : '.implode(', ', $mailing_lists)."\n";

pclose($fp);

$res=db_query("SELECT users.user_name,email,mail_group_list.list_name,
	mail_group_list.password,mail_group_list.status, 
	mail_group_list.group_list_id,mail_group_list.is_public
	FROM mail_group_list,users
	WHERE mail_group_list.list_admin=users.user_id");
$err .= db_error();

$rows=db_numrows($res);
$err .= "$rows rows returned from query\n";

$h1 = fopen("/tmp/mailman-aliases","w");

$mailingListIds = array();

for ($i=0; $i<$rows; $i++) {
	$listadmin = db_result($res,$i,'user_name');
	$email = db_result($res,$i,'email');
	$listname = strtolower(db_result($res,$i,'list_name'));
	$listpassword = db_result($res,$i,'password');
	$grouplistid = db_result($res,$i,'group_list_id');
	$public = db_result($res,$i,'is_public');
	
	// Here we assume that the privatize_list.py script is located in the same dir as this script
	$script_dir = dirname(__FILE__);
	$privatize_cmd = escapeshellcmd($sys_path_to_mailman.'/bin/config_list -i '.$script_dir.'/privatize_list.py '.$listname);
	
	if (! in_array($listname,$mailing_lists)) {		// New list?
		$err .= "Creating Mailing List: $listname\n";
		//$lcreate_cmd = $sys_path_to_mailman."/bin/newlist -q $listname@$sys_lists_host $email $listpassword &> /dev/null";
		$lcreate_cmd = $sys_path_to_mailman."/bin/newlist -q $listname $email $listpassword";
		$err .= "Command to be executed is $lcreate_cmd\n";
		passthru($lcreate_cmd, $failed);
		if($failed) {
			$err .= 'Failed to create '.$listname.", skipping\n";
echo $err;
			continue;
		} else {
			// Privatize the new list
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd,$privatizeFailed);
		}
		$mailingListIds[] = $grouplistid;
	} else {	// Old list
		// Privatize only if it is marked as private
		if (!$public) {
			$err .= "Privatizing ".$listname.": ".$privatize_cmd."\n";
			passthru($privatize_cmd,$privatizeFailed);
		}
	}
	
	if(file_exists($sys_path_to_mailman.'/mail/mailman')) {
		// Mailman 2.1
		$list_str =
$listname.':              "|'.$sys_path_to_mailman.'/mail/mailman post '.$listname.'"'."\n"
.$listname.'-admin:        "|'.$sys_path_to_mailman.'/mail/mailman admin '.$listname.'"'."\n"
.$listname.'-bounces:      "|'.$sys_path_to_mailman.'/mail/mailman bounces '.$listname.'"'."\n"
.$listname.'-confirm:      "|'.$sys_path_to_mailman.'/mail/mailman confirm '.$listname.'"'."\n"
.$listname.'-join:         "|'.$sys_path_to_mailman.'/mail/mailman join '.$listname.'"'."\n"
.$listname.'-leave:        "|'.$sys_path_to_mailman.'/mail/mailman leave '.$listname.'"'."\n"
.$listname.'-owner:        "|'.$sys_path_to_mailman.'/mail/mailman owner '.$listname.'"'."\n"
.$listname.'-request:      "|'.$sys_path_to_mailman.'/mail/mailman request '.$listname.'"'."\n"
.$listname.'-subscribe:    "|'.$sys_path_to_mailman.'/mail/mailman subscribe '.$listname.'"'."\n"
.$listname.'-unsubscribe:  "|'.$sys_path_to_mailman.'/mail/mailman unsubscribe '.$listname.'"'."\n\n"
;
	} else {
		// Mailman < 2.1
		$list_str =
$listname.':		"|'.$sys_path_to_mailman.'/mail/wrapper post '.$listname.'"'."\n"
.$listname.'-admin:	"|'.$sys_path_to_mailman.'/mail/wrapper mailowner '.$listname.'"'."\n"
.$listname.'-request:	"|'.$sys_path_to_mailman.'/mail/wrapper mailcmd '.$listname.'"'."\n"
.$listname.'-owner:	'.$listname.'-admin'."\n\n";
	}

	fwrite($h1,$list_str);
}

// Update status
//if(!empty($mailingListIds)) {
	db_query('UPDATE mail_group_list set status='.MAIL__MAILING_LIST_IS_CREATED.' WHERE status=\''.MAIL__MAILING_LIST_IS_REQUESTED.'\'');
echo db_error();
//}

fclose($h1);

//
//delete mailing lists
//
$res=db_query("SELECT mailing_list_name FROM deleted_mailing_lists WHERE isdeleted = 0;");
$err .= db_error();
$rows	 = db_numrows($res);

for($k = 0; $k < $rows; $k++) {
	$deleted_mail_list = db_result($res,$k,'mailing_list_name');
	
	passthru($sys_path_to_mailman."/bin/rmlist -a $deleted_mail_list", $failed);
	if(!$failed){
		$res1 = db_query("UPDATE mailing_list_name SET isdeleted = 1 WHERE mailing_list_name = '$deleted_group_name';" );
		$err .= db_error();
	}else{
		$err .= "Colud not remove the list $deleted_mail_list \n";
	}
}


cron_entry(18,$err);

?>
