#! /usr/bin/php4 -f
<?php

//
//	This script will read in a list existing mailing lists, then add the new lists
//	and, finally, create the lists in a /tmp/mailman-aliases file
//	The /tmp/mailman-aliases file will then be read by the mailaliases.php file
//

//
//	DEFINE VARS FOR USING THIS SCRIPT
//
define('MAILMAN_DIR','/var/mailman/');

require ('squal_pre.php');
//
// Extract the mailing lists that already exist on the system and create
// a "list" of them for use later so we don't try to create ones that 
// already exist
//
$mailing_lists=array();
$mlists_cmd = escapeshellcmd(MAILMAN_DIR."bin/list_lists");
echo "Command to be executed is $mlists_cmd\n";
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
		echo "Existing mailing List $listname found\n";	
	}
}
pclose($fp);

$res=db_query("SELECT users.user_name,email,mail_group_list.list_name,
        mail_group_list.password,mail_group_list.status 
		FROM mail_group_list,users
        WHERE mail_group_list.list_admin=users.user_id");
echo db_error();

$rows=db_numrows($res);
echo "$rows rows returned from query\n";

$h1 = fopen("/tmp/mailman-aliases","w");

for ($i=0; $i<$rows; $i++) {
	echo "Processing row $i\n";
	$listadmin = db_result($res,$i,'user_name');
	$email = db_result($res,$i,'email');
	$listname = strtolower(db_result($res,$i,'list_name'));
	$listpassword = db_result($res,$i,'password');
	if (! in_array($listname,$mailing_lists)) {
		echo "Creating Mailing List: $listname\n";
		$lcreate_cmd = MAILMAN_DIR."bin/newlist -q $listname $email $listpassword";
		echo "Command to be executed is $lcreate_cmd\n";
		$fp = popen($lcreate_cmd,"r");
	}
	$list_str="$listname:       \"|/var/mailman/mail/wrapper post $listname\"
$listname-admin: \"|/var/mailman/mail/wrapper mailowner $listname\"
$listname-request: \"|/var/mailman/mail/wrapper mailcmd $listname\"\n";

	fwrite($h1,$list_str);
//
//		Get the results of the command so we can add the aliases
//		needed by the new list
/*
		while (!feof($fp)) {
			$resline = fgets($fp, 4096);
			$resline = trim($resline);
			if ($resline == "") {
				continue;
			}
			if (stristr($resline,"Entry for aliases file") !== FALSE) {
				continue;
			}
			echo "New alias line - $resline\n";
			fwrite($h1,$resline."\n");
		}
		pclose($fp);
	}
*/
}

fclose($h1);

db_free_result($res);

?>
