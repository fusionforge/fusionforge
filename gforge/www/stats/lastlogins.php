<?php
/**
  *
  * Page to view latest logins to the site
  *
  * WARNING: this should probably be moved to /stats/ for consistency
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

$res_logins = db_query("SELECT session.user_id AS user_id,"
	. "session.ip_addr AS ip_addr,"
	. "session.time AS time,"
	. "users.user_name AS user_name FROM session,users "
	. "WHERE session.user_id=users.user_id AND "
	. "session.user_id>0 AND session.time>0 ORDER BY session.time DESC",50);

if (!$res_logins || db_numrows($res_logins) < 1) {
	exit_error("No records found","Database error: ".db_error());
}

$HTML->header(array('title'=>"Last Logins"));

print '<h3>Most Recent Opened Sessions</h3>';

$title=array();
$title[]='Date';
$title[]='Username';
$title[]='Source IP';

echo html_build_list_table_top($title);

while ($row_logins = db_fetch_array($res_logins)) {
	print '<TR>';
	print '<TD>'.date($sys_datefmt, $row_logins['time']).'</TD>';
	print '<TD>'.$row_logins['user_name'].'</TD>';
	print '<TD>'.$row_logins['ip_addr'].'</TD>';
	print '</TR>';
}

print '</table>';

$HTML->footer(array());

?>
