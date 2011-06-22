<?php
/**
 * Forum Facility
 *
 * Copyright 1999-2001 (c) Tim Perdue - VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) Franck Villaume - Capgemini
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumAdmin.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
$group_forum_id = getIntFromRequest('group_forum_id');
$g=group_get_object($group_id);
$f = new Forum ($g,$group_forum_id);
if (!$f || !is_object($f)) {
	exit_error(_('Could Not Get Forum Object'),'forums');
} elseif ($f->isError()) {
	exit_error($f->getErrorMessage(),'forums');
}

session_require_perm ('forum_admin', $f->Group->getID()) ;

forum_header(array('title'=>_('Monitoring Users')));

$res = db_query_params ('select users.user_id,users.user_name, users.email, users.realname from
users,forum_monitored_forums fmf where fmf.user_id=users.user_id and
fmf.forum_id =$1 order by users.user_id',
			array ($group_forum_id));

if ($res && db_numrows($res) == 0) {
	echo '<p class="information">'._('No Monitoring Users').'</p>';
	forum_footer(array());
	exit;
}

$tableHeaders = array(_('User'), _('Email'), _('Realname'));

$j=0;

echo $HTML->listTableTop($tableHeaders);

while ($arr=db_fetch_array($res)) {

	echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '><td>'.$arr['user_name'].'</td>
	<td>'.$arr['email'].'</td>
	<td>'.$arr['realname'].'</td></tr>';

}
echo $HTML->listTableBottom();

forum_footer(array());

?>
