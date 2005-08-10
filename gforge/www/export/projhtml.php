<?php
/**
 * GForge Exports: Export project summary page as HTML
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-03-16
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('pre.php');
require_once('project_summary.php');

$group_name = getStringFromRequest('group_name');
$group_id = getIntFromRequest('group_id');
$mode = getStringFromRequest('mode');
$no_table = getStringFromRequest('no_table');

//
//	Get group object
//
if ( $group_name ) {
	$group =& group_get_object_by_name($group_name);
} else {
	$group =& group_get_object($group_id);
}

if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error','Group: '.$group->getErrorMessage());
}

//
//	Get the group_id from the object
//
if ( !$group_id ) {
	$group_id=$group->getID();
}

//
//	Add checks to see if they have perms to view this
//
if (!$group->isPublic()) {
	if (!session_loggedin()) {
		exit_permission_denied();
	} elseif (!user_ismember($group_id)) {
		exit_permission_denied();
	}
}

echo project_summary($group_id,$mode,$no_table);

?>
