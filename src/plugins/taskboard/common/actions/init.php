<?php
/**
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

if (getStringFromRequest('post_changes')) {
	$taskboard_id = getIntFromRequest('taskboard_id');
	$taskboard_name = getStringFromRequest('taskboard_name', '');
	$taskboard_description = getStringFromRequest('taskboard_description', '');
	db_begin();
	if ($taskboard_id) {
		$taskboard = new TaskBoard($group,$taskboard_id);
	} else {
		$taskboard = new TaskBoard($group);
	}
	if($taskboard->isError()) {
		$error_msg = $taskboard->getErrorMessage();
		db_rollback();
		session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id, false);
	} else {
		if ($taskboard_id) {
			if ($taskboard_name!=$taskboard->getName() || $taskboard_description!=$taskboard->getDescription()) {
				$ret = $taskboard->update($taskboard_name,$taskboard_description);
			} else {
				$ret = true;
			}
		} else {
			$ret = $taskboard->create($taskboard_name,$taskboard_description);
		}
		if(!$ret) {
			$error_msg = $taskboard->getErrorMessage();
			db_rollback();
			if ($taskboard_id) {
				session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id'.$taskboard_id, false);
			} else {
				session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id, false);
			}
		} else {
			db_commit();
			if ($taskboard_id) {
				$feedback = _('Taskboard successfully updated');
			} else {
				$feedback = _('Taskboard successfully created');
			}
			session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard->getID(), false);
		}
	}
}