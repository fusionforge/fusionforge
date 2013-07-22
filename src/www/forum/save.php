<?php
/**
 * Forums Facility
 *
 * Copyright 1999-2001, Tim Perdue - Sourceforge
 * Copyright 2002, Tim Perdue - GForge, LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';

// User obviously has to be logged in to save place
session_require_login();

$forum_id = getIntFromRequest('forum_id');
$group_id = getIntFromRequest('group_id');

if ($forum_id && $group_id) {
	//
	//  Set up local objects
	//
	$g = group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$f=new Forum($g,$forum_id);
	if (!$f || !is_object($f)) {
		exit_error(_('Error getting Forum'),'forums');
	} elseif ($f->isError()) {
		exit_error($f->getErrorMessage(),'forums');
	}

	if (!$f->savePlace()) {
		exit_error($f->getErrorMessage(),'forums');
	} else {
		session_redirect('/forum/forum.php?forum_id='.$forum_id.'&group_id='.$group_id.'&feedback='.urlencode(_('Forum Position Saved. New messages will be highlighted when you return')));
	}
} else {
	exit_missing_param('',array(_('Forum ID'),_('Project ID')),'forums');
}
