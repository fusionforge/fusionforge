<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require_once('pre.php');

session_require(array('group'=>1,'admin_flags'=>'A'));

header ('Content-Type: text/plain');
print "Received Post. Making Query.\n";
flush();

switch ($destination) {
	case 'comm': 
		$res_mail = db_query("SELECT user_id,email,user_name 
		FROM users 
		WHERE status='A' 
		AND mail_va=1 
		AND user_id > '$first_user' 
		ORDER BY user_id ASC");
		break;
	case 'sf':
		$res_mail = db_query("SELECT user_id,email,user_name 
		FROM users 
		WHERE status='A' 
		AND mail_siteupdates=1 
		AND user_id > '$first_user' 
		ORDER BY user_id ASC");
		break;
	case 'all':
		$res_mail = db_query("SELECT user_id,email,user_name 
		FROM users 
		WHERE status='A' 
		AND user_id > '$first_user' 
		ORDER BY user_id ASC");
		break;
	case 'admin':
		$res_mail = db_query("SELECT DISTINCT ON (users.user_id,users.email) 
		users.user_id,users.email AS email,users.user_name AS user_name 
		FROM users,user_group WHERE 
		users.user_id=user_group.user_id 
		AND users.status='A' 
		AND user_group.admin_flags='A' 
		AND users.user_id > '$first_user' 
		ORDER BY user_id ASC");
		break;
	case 'sfadmin':
		$res_mail = db_query("SELECT DISTINCT ON (users.user_id,users.email) 
		users.user_id,users.email AS email,users.user_name AS user_name 
		FROM users,user_group WHERE 
		users.user_id=user_group.user_id 
		AND users.status='A' 
		AND user_group.group_id=1 
		AND users.user_id > '$first_user'
		ORDER BY user_id ASC");
		break;
	case 'devel':
		$res_mail = db_query("SELECT DISTINCT ON (users.user_id,users.email) 
		users.user_id,users.email AS email,users.user_name AS user_name 
		FROM users,user_group WHERE
		users.user_id=user_group.user_id 
		AND users.status='A' 
		AND users.user_id > '$first_user' 
		ORDER BY user_id ASC");
		break;
	default:
		exit_error('Unrecognized Post','cannot execute');
}

print "Query Complete. Beginning mailings to ".db_numrows($res_mail)."\n\n";
flush();

$rows=db_numrows($res_mail);
echo db_error();

for ($i=0; $i<$rows; $i++) {
	$tolist .= db_result($res_mail,$i,'email').', ';
	if ($i % 25 == 0) {
		echo "\nUser id: ".db_result($res_mail,$i,'user_id');
		//spawn sendmail for 25 addresses at a time
		util_send_message( '', stripslashes($mail_subject), stripslashes($mail_message), '', $tolist);

		usleep(500000);
		print "\nsending to $tolist";
		$tolist='';
		flush();
	}
}

//send the last of the messages.
//spawn sendmail for 25 addresses at a time
util_send_message( '', stripslashes($mail_subject), stripslashes($mail_message), '', $tolist);

usleep(500000);
print "\nsending to $tolist";
$tolist='';
echo "\n\n\nCOMPLETED SUCCESSFULLY";
flush();

?>
