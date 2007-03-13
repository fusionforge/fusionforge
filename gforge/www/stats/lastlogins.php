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

require_once('../env.inc.php');
require_once('pre.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

$res_logins = db_query("SELECT us.user_id AS user_id,
	us.ip_addr AS ip_addr,
	us.time AS time,
	users.user_name AS user_name FROM user_session us,users 
	WHERE us.user_id=users.user_id AND 
	us.user_id>0 AND us.time>0 ORDER BY us.time DESC",50);

if (!$res_logins || db_numrows($res_logins) < 1) {
	exit_error('Error',_('No records found","Database error: "').db_error());
}

$HTML->header(array('title'=>_('Last Logins')));

print '<h3>'._('Most Recent Opened Sessions').'</h3>';

?>

<table  width="100%" cellspacing="0" cellpadding="0">
<tr class="tableheading">
<th><?php echo _('Date'); ?></th>
<th><?php echo _('Username'); ?></th>
<th><?php echo _('Source IP'); ?></th>
</tr>

<?php

$alt=true;
$ii=0;
while ($row_logins = db_fetch_array($res_logins)) {
	$ii++;

print ' <tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>';
	print '<td >'.date($sys_datefmt, $row_logins['time']).'</td>';
	print '<td >'.$row_logins['user_name'].'</td>';
	print '<td >'.$row_logins['ip_addr'].'</td>';
	print '</tr>';
}
?>

</table>
<?php
$HTML->footer(array());
?>
