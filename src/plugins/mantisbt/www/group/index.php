<?php
/**
 * Project MantisBT page
 *
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2011,2014 Franck Villaume - TrivialDev
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

global $mantisbt;
global $gfplugins;
global $view;

$mantisbt->getSubMenu();

// page a afficher
switch ($view) {
	case 'addAttachment':
	case 'addIssue':
	case 'editIssue':
	case 'roadmap':
	case 'viewNote':
	case 'viewIssue': {
		include($gfplugins.$mantisbt->name.'/view/'.$view.'.php');
		break;
	}
	case 'addNote':
	case 'editNote': {
		include($gfplugins.$mantisbt->name.'/view/addOrEditNote.php');
		break;
	}
	/* viewIssues is the default page */
	default: {
		include($gfplugins.$mantisbt->name.'/view/viewIssues.php');
		break;
	}
}
