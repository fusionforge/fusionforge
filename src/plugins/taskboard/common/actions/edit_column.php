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

global $group_id, $pluginTaskboard;

session_require_perm('tracker_admin', $group_id);
$column_id = getStringFromRequest('column_id', '');
$taskboard_id = getStringFromRequest('taskboard_id', '');
$column = &taskboard_column_get_object($column_id);

if (getStringFromRequest('post_changes')) {
	$resolutions  = getArrayFromRequest('resolutions', array());
	$column_title = getStringFromRequest('column_title', '');
	$title_bg_color = getStringFromRequest('title_bg_color', '');
	$color_bg_color = getStringFromRequest('column_bg_color', '');
	$column_max_tasks = getStringFromRequest('column_max_tasks', '');

	$resolution_by_default =  getStringFromRequest('resolution_by_default', '');
	$alert = getStringFromRequest('alert','');
	$autoassign = getIntFromRequest('autoassign',0);

	if( $resolution_by_default && $column_title) {
		db_begin();
		if( $column->update($column_title, $title_bg_color, $color_bg_color, $column_max_tasks) ) {
			$column->setResolutions($resolutions);

			if( $column->setDropRule(NULL, $resolution_by_default, $alert, $autoassign) ) {
				db_commit();
				$feedback .= _('Successfully Updated');
			} else {
				db_rollback();
				$error_msg = $column->getErrorMessage();
			}
		} else {
			db_rollback();
			$error_msg = $column->getErrorMessage();
		}
	} else {
		$warning_msg .= _('Please, fill all required fields.');
		session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&column_id='.$column_id.'&view=edit_column', false);
	}
}
session_redirect('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=columns', false);
