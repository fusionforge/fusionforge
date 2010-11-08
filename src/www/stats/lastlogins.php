<?php
/**
 * Page to view latest logins to the site
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

session_require_global_perm ('forge_admin');

$res_logins = db_query_params ('SELECT us.user_id AS user_id,
	us.ip_addr AS ip_addr,
	us.time AS time,
	users.user_name AS user_name FROM user_session us,users 
	WHERE us.user_id=users.user_id AND 
	us.user_id>0 AND us.time>0 ORDER BY us.time DESC',
			       array (),
			       50);

if (!$res_logins || db_numrows($res_logins) < 1) {
	exit_error(_('No records found","Database error: "').db_error());
}

$HTML->header(array('title'=>_('Most Recent Opened Sessions')));
print '<h1>'._('Most Recent Opened Sessions').'</h1>';

?>

<table  width="100%" cellspacing="0" cellpadding="0">
<tr class="tableheading">
<th><?php echo _('Date'); ?></th>
<th><?php echo _('Username'); ?></th>
<th><?php echo _('Source IP'); ?></th>
</tr>

<?php

$alt=true;
$i=0;
while ($row_logins = db_fetch_array($res_logins)) {
	print ' <tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>';
	print '<td >'.date(_('Y-m-d H:i'), $row_logins['time']).'</td>';
	print '<td >'.$row_logins['user_name'].'</td>';
	print '<td >'.$row_logins['ip_addr'].'</td>';
	print '</tr>';
}
?>

</table>
<?php
$HTML->footer(array());
?>
