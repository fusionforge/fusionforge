#! /usr/bin/php -f
<?php

require ('squal_pre.php');
//
// Extract the mailing lists that already exist on the system and create
// a "list" of them for use later so we don't try to create ones that 
// already exist
//
$mlists_cmd = escapeshellcmd("/usr/bin/ssh $sys_lists_host -l mailman bin/list_lists");
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
		$mailing_lists[] = $listname;
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

for ($i=0; $i<$rows; $i++) {
	echo "Processing row $i\n";
	$listadmin = db_result($res,$i,'user_name');
	$email = db_result($res,$i,'email');
	$listname = db_result($res,$i,'list_name');
	$listpassword = db_result($res,$i,'password');
	if (! in_array($listname,$mailing_lists)) {
		echo "Creating Mailing List: $listname\n";
		$lcreate_cmd = "/usr/bin/ssh $sys_lists_host -l mailman 'bin/newlist -q $listname $email $listpassword'";
		echo "Command to be executed is $lcreate_cmd\n";
		$fp = popen($lcreate_cmd,"r");
//
//		Get the results of the command so we can add the aliases
//		needed by the new list
//
		while (!feof($fp)) {
			$resline = fgets($fp, 4096);
			$resline = trim($resline);
			if ($resline == "") {
				continue;
			}
			if (stristr($resline,"Entry for aliases file") !== FALSE) {
				contrinue;
			}
			echo "New alias line - $resline\n";			
//
//			~mailman/bin/add_alias is a local script that will add
//			an alias from standard input to the local aliases. This
//			is mail system dependent. This is left as an
//			external script because Mailman makes no attempt to
//			add the aliases so we have to solve it anyway.
//
			$lcreate_cmd = "echo '$resline' | /usr/bin/ssh $sys_lists_host -l mailman bin/add_alias.php";
			$alias_added = `$lcreate_cmd`;
		}
		pclose($fp);
	}
}
db_free_result($res);
$update_cmd = "/usr/bin/ssh $sys_lists_host -l mailman /usr/bin/newaliases";
$aliases_updated = `$update_cmd`;
?>
