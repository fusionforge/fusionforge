<?php

/*
 * Project MantisBT page
 *
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * http://fusionforge.org
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

$action = getStringFromRequest('action');
$view = getStringFromRequest('view');

switch ($action) {
	case "updateIssue":
	case "addNote":
	case "addIssue":
	case "deleteNote":
	case "addAttachment":
	case "deleteAttachment": {
		include ("mantisbt/action/$action.php");
		break;
	}
	case "updateNote":
	case "privateNote":
	case "publicNote": {
		include ("mantisbt/action/updateNote.php");
		break;
	}
}

// submenu
$labelTitle = array ();
$labelTitle[] = _('Roadmap');
$labelTitle[] = _('Tickets');
$labelPage = array();
$labelPage[] = "/plugins/mantisbt/?type=group&id=".$id."&pluginname=".$pluginname."&view=roadmap";
$labelPage[] = "/plugins/mantisbt/?type=group&id=".$id."&pluginname=".$pluginname;
$userperm = $group->getPermission($user);
if ( $userperm->isAdmin() ) {
	$labelTitle[] = _('Administration');
	$labelPage[] = "/plugins/mantisbt/?type=admin&id=".$id."&pluginname=".$pluginname;
	$labelTitle[] = _('Statistics');
	$labelPage[] = "/plugins/mantisbt/?type=admin&id=".$id."&pluginname=".$pluginname."&view=stat";
}

echo $HTML->subMenu( $labelTitle, $labelPage );

// page a afficher
switch ($view) {
	case "editIssue":
	case "viewNote":
	case "addIssue":
	case "addAttachment":
	case "roadmap": {
		include("mantisbt/view/$view.php");
		break;
	}
	case "viewIssue": {
		include("mantisbt/view/$view.php");
		include('mantisbt/view/viewNote.php');
		include('mantisbt/view/viewAttachment.php');
		break;
	}
	case "editNote":
	case "addNote": {
		include("mantisbt/view/addOrEditNote.php");
		break;
	}
	/* viewAllIssues is the default page */
	default: {
		include('mantisbt/view/viewIssues.php');
		break;
	}
}

?>
