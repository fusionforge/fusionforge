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
require_once $gfcommon.'forum/AttachManager.class.php';

/*
	User obviously has to be logged in to save place
*/

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
		exit_error(_('Error Getting Forum'),'forums');
	} elseif ($f->isError()) {
		exit_error($f->getErrorMessage(),'forums');
	}

	$fh=new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error(_('Error Getting ForumHTML'),'forums');
	} elseif ($fh->isError()) {
		exit_error($fh->getErrorMessage(),'forums');
	}

	if (forge_check_perm('forum', $f->getID(), 'post')) {
		if (session_loggedin() && !$f->savePlace()) {
			exit_error($f->getErrorMessage(),'forums');
		} else {
			forum_header(array('title'=> _('Start New Topic for: ') . $f->getName(), 'forum_id'=>$forum_id, 'modal' => 1));
			$fh->showPostForm();
			forum_footer(array());
		}
	} else {
		exit_not_logged_in();
	}
} else {
	exit_missing_param('',array(_('Forum ID'),_('Project ID')),'forums');
}
