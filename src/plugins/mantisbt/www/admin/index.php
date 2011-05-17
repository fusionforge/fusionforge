<?php

/*
 * Copyright 2010 (c) : Franck Villaume - Capgemini
 * Admin MantisBT page
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

switch($action) {
	case "addCategory":
	case "addVersion":
	case "renameCategory":
	case "deleteCategory":
	case "deleteVersion":
	case "updateVersion":
		include ("mantisbt/action/admin/$action.php");
		break;
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
        $labelTitle[] = _('Admin');
        $labelPage[] = "/plugins/mantisbt/?type=admin&id=".$id."&pluginname=".$pluginname;
        $labelTitle[] = _('Stats');
        $labelPage[] = "/plugins/mantisbt/?type=admin&id=".$id."&pluginname=".$pluginname."&view=stat";
}

echo $HTML->subMenu( $labelTitle, $labelPage );

switch($view) {
	case "editVersion":
	case "stat":
		include ("mantisbt/view/admin/$view.php");
		exit;
	default:
		/* affichage principal */
		echo '<table><tr><td valign="top">';
		include ("mantisbt/view/admin/viewCategorie.php");
		echo '</td><td valign="top">';
		include ("mantisbt/view/admin/viewVersion.php");
		echo '</td></tr><tr><td valign="top">';
		include ("mantisbt/view/admin/addCategory.php");
		echo '</td><td valign="top">';
		include ("mantisbt/view/admin/addVersion.php");
		echo '</td></tr></table>';
		break;
}

?>
