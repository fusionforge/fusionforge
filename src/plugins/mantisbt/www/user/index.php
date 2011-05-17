<?php

/*
 * User MantisBT page
 * Copyright 2010 (c) Franck Villaume - Capgemini
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

$action = getStringFromRequest('action');
$view = getStringFromRequest('view');

switch ($action) {
	case "updateIssue":
	case "updateNote":
	case "addNote":
	case "deleteNote":
	case "addAttachment":
	case "deleteAttachment":
		include ("mantisbt/action/$action.php");
		break;
}

// page a afficher
switch ($view) {
	case "editIssue":
	case "addAttachment":
	case "viewNote":
		include ("mantisbt/view/$view.php");
		break;
	case "viewIssue":
		include ("mantisbt/view/$view.php");
		include ("mantisbt/view/viewNote.php");
		include ("mantisbt/view/viewAttachment.php");
		break;
	case "editNote":
	case "addNote":
		include ("mantisbt/view/addOrEditNote.php");
		break;
	// default page is view All issues
	default:
		include('mantisbt/view/viewIssues.php');
}

?>
