#! /usr/bin/php4 -f
<?php
require_once('www/include/squal_pre.php');
require_once('common/mail/MailingList.class');
require_once('common/include/Group.class');

$res = db_query("SELECT group_id, group_name FROM groups ORDER BY group_id");

$arr = util_result_column_to_array($res);

for ($i=0; $i<count($arr); $i++) {
	$group_id   = $arr[$i][0];
	$group_name = $arr[$i][1];

	$res_aux2 = db_query("SELECT * FROM mail_group_list WHERE group_id = '".$group_id."' AND list_name = '".$group_name."-commits'");

	if (db_numrows($res_aux) > 0) {
		$group = new Group($group_id);

		$res_aux2 = db_query("SELECT user_id FROM user_group WHERE admin_flags = 'A' AND group_id = '".$group_id."'");

		$group_admin = db_result($res,0,'user_id');

		echo "Will create mailing list for <b>".$group_name."-commits</b><br>\n";
		$mailing_list = new MailingList($group);
		$mailing_list->create($group_name.'-commits', 'cvs commits', 1,$group_admin);
		echo $mailing_list->getErrorMessage();
	}
}

db_commit();
?>
