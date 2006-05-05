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

$res_logins = db_query("SELECT us.user_id AS user_id,
	us.ip_addr AS ip_addr,
	us.time AS time,
	users.user_name AS user_name FROM user_session us,users 
	WHERE us.user_id=users.user_id AND 
	us.user_id>0 AND us.time>0 ORDER BY us.time DESC",50);

if (!$res_logins || db_numrows($res_logins) < 1) {
	exit_error('Error',$Language->getText('stats_lastlogins','no_records').db_error());
}

$HTML->header(array('title'=>$Language->getText('stats_lastlogins','last_logins')));

print '<h3>'.$Language->getText('stats_lastlogins','most_recent_open').'</h3>';

?>

<table  width="100%" cellspacing="0" cellpadding="0">
<th><?php echo $Language->getText('stats_lastlogins','date'); ?></th>
<th><?php echo $Language->getText('stats_lastlogins','username'); ?></th>
<th><?php echo $Language->getText('stats_lastlogins','source_ip'); ?></th>

<?php

$alt=true;
while ($row_logins = db_fetch_array($res_logins)) {
	$class="alt1";
	if ($alt == true) {
		$class="alt2";
	}
	$alt = !$alt;

	print '<tr>';
	print '<td class="'.$classr.'">'.date($sys_datefmt, $row_logins['time']).'</td>';
	print '<td class="'.$class.'">'.$row_logins['user_name'].'</td>';
	print '<td class="'.$class.'>'.$row_logins['ip_addr'].'</td>';
	print '</tr>';
}
?>

</table>
<?php
$HTML->footer(array());
?>
