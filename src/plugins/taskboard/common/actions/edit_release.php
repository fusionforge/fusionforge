<?php
/**
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

global $group_id, $taskboard;

session_require_perm('tracker_admin', $group_id) ;

$start_date_unixtime = NULL;
$end_date_unixtime = NULL;
$error_msg = '';
$release_id = getIntFromRequest('release_id', NULL);

$release = new TaskBoardRelease( $taskboard, $release_id);

if (getStringFromRequest('post_changes')) {
	$element_id = getIntFromRequest('_release', '');
	$start_date = getStringFromRequest('start_date', '');
	$end_date = getStringFromRequest('end_date', '');
	$goals = getStringFromRequest('goals', '');
	$page_url = getStringFromRequest('page_url', '');


	$start_date_unixtime = strtotime($start_date);
	$end_date_unixtime = strtotime($end_date);

	if( $end_date_unixtime < $start_date_unixtime ) {
		$start_date_unixtime = NULL;
		$end_date_unixtime = NULL;
		$error_msg = _('End date should be later then the start date');
	}
} else {
	$element_id = $release->getElementID();
	$start_date = date( 'Y-m-d', $release->getStartDate() );
	$end_date = date( 'Y-m-d', $release->getEndDate() );
	$goals = $release->getGoals();
	$page_url = $release->getPageUrl();
}

if ($element_id && $start_date_unixtime && $end_date_unixtime) {
	db_begin();
	if ($release->update($element_id, $start_date_unixtime, $end_date_unixtime, $goals, $page_url)) {
		db_commit();
		$feedback .= _('Successfully Updated');
	} else {
		db_rollback();
		$error_msg = $release->getErrorMessage();
	}
}
session_redirect('/plugins/taskboard/releases/?group_id='.$group_id.'&taskboard_id='.$taskboard->getID(), false);
