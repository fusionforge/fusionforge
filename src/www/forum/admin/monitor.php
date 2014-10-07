<?php
/**
 * Forum Facility
 *
 * Copyright 1999-2001 (c) Tim Perdue - VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) Franck Villaume - Capgemini
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumAdmin.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';
require_once $gfcommon.'include/User.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
$group_forum_id = getIntFromRequest('group_forum_id');
$g = group_get_object($group_id);
$f = new Forum($g, $group_forum_id);
if (!$f || !is_object($f)) {
	exit_error(_('Could Not Get Forum Object'), 'forums');
} elseif ($f->isError()) {
	exit_error($f->getErrorMessage(), 'forums');
}

session_require_perm('forum_admin', $f->Group->getID());

forum_header(array('title'=>sprintf(_('Forum %s Monitoring Users'), $f->getName())));

$MonitorElementObject = new MonitorElement('forum');
$monitorUsersIdArray = $MonitorElementObject->getMonitorUsersIdsInArray($group_forum_id);
if (!$monitorUsersIdArray) {
	echo $HTML->error_msg($MonitorElementObject->getErrorMessage());
	forum_footer();
	exit;
} elseif (count($monitorUsersIdArray) == 0) {
	echo $HTML->information(_('No Monitoring Users'));
	forum_footer();
	exit;
}

$tableHeaders = array(_('User'), _('Email'), _('Real Name'));
echo $HTML->listTableTop($tableHeaders);

foreach ($monitorUsersIdArray as $monitorUsersId) {
	$userObject = user_get_object($monitorUsersId);
	$cells = array();
	$cells[][] = $userObject->getUnixName();
	$cells[][] = $userObject->getEmail();
	$cells[][] = $userObject->getRealName();
	echo $HTML->multiTableRow(array(), $cells);
}
echo $HTML->listTableBottom();
forum_footer();
