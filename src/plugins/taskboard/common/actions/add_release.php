<?php
/**
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

session_require_perm('tracker_admin', $group_id);

global $taskboard, $pluginTaskboard;

$start_date_unixtime = NULL;
$end_date_unixtime = NULL;
$error_msg = '';
$element_id = getIntFromRequest('_release', NULL);
$start_date = getStringFromRequest('start_date', '');
$end_date = getStringFromRequest('end_date', '');
$goals = getStringFromRequest('goals', '');
$page_url = getStringFromRequest('page_url', '');
if (getStringFromRequest('post_changes')) {
	$start_date_unixtime = strtotime($start_date);
	$end_date_unixtime = strtotime($end_date);

	if($end_date_unixtime < $start_date_unixtime) {
		$start_date_unixtime = NULL;
		$end_date_unixtime = NULL;
		$error_msg = _('End date should be later then the start date');
	}
}

if ($element_id && $start_date_unixtime && $end_date_unixtime) {
	db_begin();

	$release = new TaskBoardRelease(
		$taskboard,
		array(
			'taskboard_id' => $taskboard->getID(),
			'element_id' => $element_id,
			'start_date' => $start_date_unixtime,
			'end_date' => $end_date_unixtime,
			'goals' => $goals,
			'page_url' => $page_url
		)
	);

	if ($release->create($element_id, $start_date_unixtime, $end_date_unixtime, $goals, $page_url)) {
		db_commit();
		$feedback .= _('Successfully Created');
	} else {
		db_rollback();
		$error_msg = $release->getErrorMessage();
	}
} else {
	$warning_msg = _('Something missing here');
}
session_redirect('/plugins/'.$pluginTaskboard->name.'/releases/?group_id='.$group_id.'&taskboard_id='.$taskboard->getID(),false);
