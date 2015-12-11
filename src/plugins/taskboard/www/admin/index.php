<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015, Franck Villaume - TrivialDev
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

require_once '../../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfplugins.'taskboard/common/include/TaskBoardHtml.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
$pluginTaskboard = plugin_get_object('taskboard');

if (!$group_id) {
	exit_error(_('Cannot Process your request')._(': ')._('No ID specified'), 'home');
} else {
	$group = group_get_object($group_id);
	if ( !$group) {
		exit_no_group();
	}
	if ( ! ($group->usesPlugin($pluginTaskboard->name))) {//check if the group has the plugin active
		exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $pluginTaskboard->name), 'home');
	}

	if ($group->usesTracker()) {

		session_require_perm('tracker_admin', $group_id);

		$allowedActions = array('trackers', 'columns', 'edit_column', 'down_column', 'delete_column');
		$action = getStringFromRequest('action');
		$taskboard = new TaskBoardHtml($group);

		if (in_array($action, $allowedActions)) {
			include($gfplugins.$pluginTaskboard->name.'/common/actions/'.$action.'.php');
		}

		$allowedViews = array('trackers', 'columns', 'edit_column', 'delete_column', 'init');
		$view = getStringFromRequest('view');

		if (in_array($view, $allowedViews)) {
			include($gfplugins.$pluginTaskboard->name.'/common/views/admin/'.$view.'.php');
		} else {
			include($gfplugins.$pluginTaskboard->name.'/common/views/admin/ind.php');
		}
	} else {
		$HTML->header(
			array(
				'title' => _('Taskboard for ').$group->getPublicName()._(': ')._('Administration'),
				'pagename' => _('Administration'),
				'sectionvals' => array(group_getname($group_id)),
				'group' => $group_id
			)
		);
		echo $HTML->information(_('Your project does not use tracker feature. Please contact your Administrator to turn on this feature.'));
	}
}

site_project_footer(array());
