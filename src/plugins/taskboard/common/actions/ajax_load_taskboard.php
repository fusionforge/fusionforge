<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

global $taskboard;

$columns = $taskboard->getColumns();
$ret['message'] = '';

$release = getStringFromRequest('release', NULL);
$assigned_to = getIntFromRequest('assigned_to', NULL);

if (!$assigned_to) {
	$assigned_to = NULL;
}

$user_stories = $taskboard->getUserStories($release, $assigned_to);
$user_stories_tracker = $taskboard->getUserStoriesTrackerID();
$phases = array();
if ($user_stories_tracker) {
	$phases[] = array(
		'id' => 'user-stories',
		'title' => _('User stories')
	);
}

foreach($columns as $column) {
	$phases[] = array(
		'id' => $column->getID(),
		'dom_id' => $column->getDomID(),
		'title' => $column->getTitle(),
		'titlebackground' => $column->getTitleBackgroundColor(),
		'background' => $column->getColumnBackgroundColor(),
		'resolutions' =>  array_values($column->getResolutions())
	);
}

$ret['user_stories'] = $user_stories;
$ret['phases'] = $phases;

echo json_encode($ret);
