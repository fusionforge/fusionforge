<?php
/*
 * Project MantisBT page
 *
 * Copyright 2009-2011, Franck Villaume - Capgemini
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

global $mantisbt;
global $mantisbtConf;
global $group_id;
global $gfplugins;

// submenu
$labelTitle = array();
$labelTitle[] = _('Roadmap');
$labelTitle[] = _('Tickets');
$labelPage = array();
$labelPage[] = "/plugins/".$mantisbt->name."/?type=group&id=".$group_id."&pluginname=".$mantisbt->name."&view=roadmap";
$labelPage[] = "/plugins/".$mantisbt->name."/?type=group&id=".$group_id."&pluginname=".$mantisbt->name;
$userperm = $group->getPermission($user);
if ($userperm->isAdmin()) {
	$labelTitle[] = _('Administration');
	$labelPage[] = "/plugins/".$mantisbt->name."/?type=admin&id=".$group_id."&pluginname=".$mantisbt->name;
	$labelTitle[] = _('Statistics');
	$labelPage[] = "/plugins/".$mantisbt->name."/?type=admin&id=".$group_id."&pluginname=".$mantisbt->name."&view=stat";
}

echo $HTML->subMenu($labelTitle, $labelPage);

// page a afficher
switch ($view) {
	case "editIssue":
	case "viewNote":
	case "addIssue":
	case "addAttachment":
	case "roadmap": {
		include($gfplugins.$mantisbt->name."/view/$view.php");
		break;
	}
	case "viewIssue": {
		include($gfplugins.$mantisbt->name."/view/$view.php");
		include($gfplugins.$mantisbt->name."/view/viewNote.php");
		include($gfplugins.$mantisbt->name."/view/viewAttachment.php");
		break;
	}
	case "editNote":
	case "addNote": {
		include($gfplugins.$mantisbt->name."/view/addOrEditNote.php");
		break;
	}
	/* viewAllIssues is the default page */
	default: {
		include($gfplugins.$mantisbt->name."/view/viewIssues.php");
		break;
	}
}

?>