<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $gfwww;
global $gfcommon;

require_once '../../../env.inc.php';
//require_once $gfwww."env.inc.php";
require_once $gfcommon.'include/pre.php';

global $gfplugins;
require_once $gfplugins.'taskboard/common/include/TaskBoardHtml.class.php';

$sysdebug_enable = false;

$group_id = getIntFromPost('group_id');
$taskboard_id = getIntFromPost('taskboard_id');
$action = getStringFromPost('action');

if (!$group_id) {
	echo  json_encode( array( 'message' => _('Cannot Process your request')._(': ')._('No ID specified')));
	exit();
} else {
	$group = group_get_object($group_id);
	if (!$group) {
		echo  json_encode( array('message' => _('Group is not found')));
		exit();
	}

	$taskboard = new TaskBoardHtml( $group, $taskboard_id ) ;
	$allowedActions = array('get_trackers_fields');

	if(in_array($action, $allowedActions)) {
		include($gfplugins.'taskboard/common/actions/ajax_'.$action.'.php' );
	} else {
		echo  json_encode(array('message' => _('OK')));
	}
}
