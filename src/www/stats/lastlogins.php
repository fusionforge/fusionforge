<?php
/**
 * Page to view latest sessions to the site
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

session_require_global_perm ('forge_admin');

$res = db_query_params ('SELECT us.user_id AS user_id,
	us.ip_addr AS ip_addr,
	us.time AS time,
	users.user_name AS user_name, users.realname AS realname FROM user_session us,users
	WHERE us.user_id=users.user_id AND
	us.user_id>0 AND us.time>0 ORDER BY us.time DESC',
			       array (),
			       50);

if (!$res || db_numrows($res) < 1) {
	exit_error(_('No records found. Database error: ').db_error());
}

$HTML->header(array('title'=>_('Most Recent Opened Sessions')));

?>

<table class="fullwidth">
<tr class="tableheading">
	<th><?php echo _('Date'); ?></th>
	<th><?php echo _('User Name'); ?></th>
	<th><?php echo _('Source IP'); ?></th>
</tr>

<?php

$alt=true;
$i=0;
while ($row = db_fetch_array($res)) {
	print ' <tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>';
	print '<td >'.date(_('Y-m-d H:i'), $row['time']).'</td>';
	print '<td >'.util_display_user($row['user_name'], $row['user_id'], $row['realname']).'</td>';
	print '<td >'.$row['ip_addr'].'</td>';
	print '</tr>';
}
?>

</table>
<?php
$HTML->footer(array());
