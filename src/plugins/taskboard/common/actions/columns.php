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

global $group_id, $group, $pluginTaskboard;
session_require_perm('tracker_admin', $group_id);
$taskboard_id = getStringFromRequest('taskboard_id', '');
$taskboard = new TaskBoardHtml($group,$taskboard_id);

if (getStringFromRequest('post_changes')) {
	$column_title = getStringFromRequest('column_title', '');
	$title_bg_color = getStringFromRequest('title_bg_color', '');
	$color_bg_color = getStringFromRequest('column_bg_color', '');
	$column_max_tasks = getStringFromRequest('column_max_tasks', '');
	$resolution_by_default = getStringFromRequest('resolution_by_default', '');
	db_begin();
	$column_id = $taskboard->addColumn($column_title, $title_bg_color, $color_bg_color, $column_max_tasks);
	if ($column_id) {
		$column = new TaskBoardColumn($taskboard, $column_id);
		if ($column && $column->setDropRule(NULL, $resolution_by_default) && $column->setResolutions(array($resolution_by_default))) {
				db_commit();
				$feedback = _('Successfully Added');
		} else {
			db_rollback();
			$error_msg = _('Cannot set drop rule or resolutions for new column');
		}

	} else {
		db_rollback();
		$error_msg = _('Cannot create column');
	}
}

session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=columns', false);
