<?php
/**
 * Copyright 2013, Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2018, Franck Villaume - TrivialDev
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

$tracker_id = getIntFromRequest('tracker_id');
$user_story_id = getIntFromRequest('user_story_id', 0);
$desc = getStringFromRequest('desc', '');
$title = getStringFromRequest('title', '');
$release = getStringFromRequest('release', NULL);

if( $tracker_id && $desc && $title  ) {
	db_begin();
	$msg = $taskboard->TrackersAdapter->createTask($tracker_id, $title, $desc, $user_story_id, $release);
	if($msg) {
		$ret['alert'] = $msg;
		db_rollback();
	} else {
		db_commit();
	}
} else {
	$ret['alert'] = _('All fields are mandatory.');
}

echo json_encode($ret);
