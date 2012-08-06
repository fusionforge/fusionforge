#! /usr/bin/php
<?php
require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'mail/MailingList.class.php';
require_once $gfcommon.'include/Group.class.php';

//
//	Set up this script to run as the site admin
//

$res = db_query_params ('SELECT user_id FROM user_group WHERE admin_flags=$1 AND group_id=$2',
			array('A',
			'1')) ;


if (!$res) {
	echo db_error();
	exit();
}

if (db_numrows($res) == 0) {
	// There are no Admins yet, aborting without failing
	echo "SUCCESS\n";
	exit();
}

$id=db_result($res,0,0);
session_set_new($id);

$res = db_query_params ('SELECT group_id, unix_group_name
	FROM groups
	WHERE STATUS=$1 ORDER BY group_id',
			array('A')) ;


if (!$res) {
	echo "FAIL\n";
	exit();
} else {

	for ($i=0; $i<db_numrows($res); $i++) {
		$group_id   = db_result($res,$i,'group_id');
		$group_name = db_result($res,$i,'unix_group_name');

		$res2 = db_query_params ('SELECT * FROM mail_group_list WHERE group_id = $1 AND list_name = $2',
					 array ($group_id,
						$group_name."-commits'")) ;

		if (db_numrows($res2) < 1) {
			$group = new Group($group_id);
			if (!$group || !is_object($group)) {
				$was_error=true;
				echo "Could Not Get Group Object for $group_name";
			} elseif ($group->isError()) {
				$was_error=true;
				echo "Could Not Get Group Object for $group_name: ".$group->getErrorMessage();
			} else {

				$res_aux2 = db_query_params ('SELECT user_id FROM user_group WHERE admin_flags = $1 AND group_id = $2',
							     array ('A',
								    $group_id)) ;

				$group_admin = db_result($res_aux2,0,'user_id');

				echo "Will create mailing list for <b>".$group_name."-commits</b><br>\n";
				$mailing_list = new MailingList($group);
				if (!$mailing_list || !is_object($mailing_list)) {
					$was_error=true;
					echo "Could Not Get MailingList Object for $group_name";
				} elseif ($mailing_list->isError()) {
					$was_error=true;
					echo "Could Not Get MailingList Object for $group_name: ".$mailing_list->getErrorMessage();
				} else {
					if (!$mailing_list->create('commits', 'cvs commits', 1,$group_admin)) {
						$was_error=true;
						echo "Could Not Create New Mailing List for $group_name: ".$mailing_list->getErrorMessage();
					} else {
						if ($mailing_list->isError()) {
							$was_error=true;
							echo $mailing_list->getErrorMessage();
						} else {

						}
					}
				}
			}
		}
	}
	if ($was_error) {
		echo "FAIL\n";
	} else {
		echo "SUCCESS\n";
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
