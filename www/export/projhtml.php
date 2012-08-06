<?php
/**
 * Exports: Export project summary page as HTML
 *
 * Copyright 2004 (c) Tim Perdue - GForge LLC
 * http://fusionforge.org
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/project_summary.php';

$group_name = getStringFromRequest('group_name');
$group_id = getIntFromRequest('group_id');
$mode = getStringFromRequest('mode');
$no_table = getStringFromRequest('no_table');

//
//	Get group object
//
if ( $group_name ) {
	$group = group_get_object_by_name($group_name);
} else {
	$group = group_get_object($group_id);
}

if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
    exit_error($group->getErrorMessage(),'home');
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
session_require_perm ('project_read', $group_id);

echo project_summary($group_id,$mode,$no_table);

?>
