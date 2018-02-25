<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfplugins.'taskboard/common/include/TaskBoardHtml.class.php';

global $HTML;

$group_id = getStringFromRequest('group_id');
$taskboard_id = getStringFromRequest('taskboard_id');

$pluginTaskboard = plugin_get_object('taskboard');

if (!$group_id) {
	exit_error(_('Cannot Process your request')._(': ')._('No ID specified'), 'home');
} else {
	$group = group_get_object($group_id);
	if ( !$group) {
		exit_no_group();
	}
	if ($group->isError()) {
		if($group->isPermissionDeniedError()) {
			exit_permission_denied($group->getErrorMessage(),'tracker');
		} else {
			exit_error($group->getErrorMessage(),'tracker');
		}
	}
	if ( ! ($group->usesPlugin($pluginTaskboard->name))) {//check if the group has the plugin active
		exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginTaskboard->name),'home');
	}

	if ($taskboard_id) {
		include $gfplugins.'taskboard/common/views/taskboard.php';

	} else {
		include $gfplugins.'taskboard/common/views/ind.php';

	}
}
