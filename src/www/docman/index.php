<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

/* are we using docman ? */
if (!forge_get_config('use_docman'))
	exit_disabled();

/* get informations from request or $_POST */
$group_id = getIntFromRequest('group_id');
$feedback = getStringFromRequest('feedback');

/* validate group */
if (!$group_id) {
	exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

/* is this group using docman ? */
if (!$g->usesDocman())
	exit_error(_('Error'),_('This project has turned off the Doc Manager.'));

/* everything sounds ok, now let do the job */
$action = getStringFromRequest('action');
switch ($action) {
	case "addfile":
	case "addsubdocgroup":
	case "deldir":
	case "editdocgroup":
	case "editfile":
	case "forcereindexenginesearch":
	case "emptytrash":
	case "trashdir":
	case "trashfile":
	case "updateenginesearch":
		include ("docman/actions/$action.php");
		;;
}

$title = _('Document Manager: Display Document');

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'docman'));

echo '<div style="float:left; width:20%;">';
include('docman/views/tree.php');
echo '</div>';

echo '<div style="float:right; width:78%;">';
include('docman/views/menu.php');
include('docman/views/views.php');
echo '</div>';

echo '<div style="clear:both; margin-bottom:5px;" />';
site_project_footer(array());

?>
