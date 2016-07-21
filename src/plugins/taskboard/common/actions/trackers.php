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

global $group_id, $pluginTaskboard, $taskboard;

session_require_perm('tracker_admin', $group_id);

if (getStringFromRequest('post_changes')) {
	$taskboard_name = getStringFromRequest('taskboard_name','');
	$taskboard_description = getStringFromRequest('taskboard_description','');
	$trackers_selected = getArrayFromRequest('use', array());
	$trackers_bgcolor  = getArrayFromRequest('bg', array());
	$release_field = getStringFromRequest('release_field','');
	$release_field_tracker = getIntFromRequest('release_field_tracker',1);
	$estimated_cost_field = getStringFromRequest('estimated_cost_field','');
	$remaining_cost_field = getStringFromRequest('remaining_cost_field','');
	$user_stories_tracker = getStringFromRequest('user_stories_tracker','');
	$user_stories_reference_field = getStringFromRequest('user_stories_reference_field','');
	$user_stories_sort_field = getStringFromRequest('user_stories_sort_field','');
	$first_column_by_default = getIntFromRequest('first_column_by_default','0');

	// try to save data
	if($taskboard->getID()) {
		$ret = $taskboard->update($taskboard_name, $taskboard_description, $trackers_selected, $trackers_bgcolor, $release_field, $release_field_tracker, $estimated_cost_field, $remaining_cost_field, $user_stories_tracker, $user_stories_reference_field, $user_stories_sort_field, $first_column_by_default);
	} else {
		$ret = $taskboard->create($taskboard_name, $taskboard_description, $trackers_selected, $trackers_bgcolor, $release_field, $release_field_tracker, $estimated_cost_field, $remaining_cost_field, $user_stories_tracker, $user_stories_reference_field, $user_stories_sort_field, $first_column_by_default);
	}

	if(!$ret) {
		$error_msg = $taskboard->getErrorMessage();
	} else {
		$feedback = _('Success on something here');
	}
}

session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard->getID().'&view=trackers', false);
