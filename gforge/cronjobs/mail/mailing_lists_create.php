#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');

/*
$res=db_query("SELECT users.user_name,mail_group_list.list_name,
        mail_group_list.password,mail_group_list.status 
		FROM mail_group_list,users
        WHERE mail_group_list.list_admin=users.user_id");
echo db_error();

$rows=db_numrows($res);

for ($i=1; $i<$rows; $i++) {

	($listadmin, $listname, $listpassword, $liststatus) = split(":", $ln);

	$list_dir = "$mailman_dir/lists/$listname";

	if (! -d $list_dir) {
		print ("Creating Mailing List: $listname\n");

		system("$mailman_dir/bin/newlist $listname $listadmin\@users.sourceforge.net $listpassword >/dev/null 2>&1");

	}

}
*/
?>
