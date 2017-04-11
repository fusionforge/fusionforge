<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

$ret = array(
	'message' => '',
	'action' => 'reload'
);

$task_id = getStringFromRequest('task_id');
$target_phase_id = getStringFromRequest('target_phase_id');

$task = $taskboard->TrackersAdapter->getTask($task_id);
if ($task) {

	$ret['task'] = $taskboard->getMappedTask($task);
	$source_phase_id = $ret['task']['phase_id'];

	$drop_rule = taskboard_column_source_get_object($source_phase_id, $target_phase_id);
	if (!$drop_rule->getID()) {
		$drop_rule = taskboard_default_column_source_get_object($target_phase_id);
	}

	if (!$drop_rule->getID()) {
		$ret['alert'] = _('Drop rule is not defined for this target column');
	} else {
		db_begin();
		$cannot_drop_msg = $drop_rule->drop($task);
		if (!$cannot_drop_msg) {
			db_commit();
			if ($drop_rule->getAlertText()) {
				$ret['alert'] = $drop_rule->getAlertText();
			}
			$ret['task'] = $taskboard->getMappedTask($task);
		} else {
			db_rollback();
			$ret['alert'] =  $cannot_drop_msg;
		}
	}
} else {
	$ret['alert'] = _('Task is not found. Task Board will be reloaded.');
}

echo json_encode($ret);
