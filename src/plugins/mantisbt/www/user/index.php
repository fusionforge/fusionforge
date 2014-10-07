<?php
/**
 * User MantisBT page
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2014, Franck Villaume - TrivialDev
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

global $HTML;
global $mantisbt;
global $gfplugins;

$view = getStringFromRequest('view');

// submenu
$labelTitle = array();
$labelPage = array();
$labelAttr = array();
$labelTitle[] = _('My Tickets');
$labelPage[] = '/plugins/'.$mantisbt->name.'/?type=user';
$labelAttr[] = array('title' => _('View My tickets.'), 'id' => 'ticketView');
$labelTitle[] = _('Administration');
$labelPage[] = '/plugins/'.$mantisbt->name.'/?type=user&view=adminuser';
$labelAttr[] = array('title' => _('Manage your mantisbt account.'), 'id' => 'adminView');

echo $HTML->subMenu($labelTitle, $labelPage, $labelAttr);

// page a afficher
switch ($view) {
	case 'inituser':
	case 'editIssue':
	case 'viewIssues':
	case 'addAttachment':
	case 'adminuser':
	case 'viewNote': {
		include ($gfplugins.$mantisbt->name.'/view/'.$view.'.php');
		break;
	}
	case 'viewIssue': {
		include ($gfplugins.$mantisbt->name.'/view/'.$view.'.php');
		include ($gfplugins.$mantisbt->name.'/view/viewNote.php');
		include ($gfplugins.$mantisbt->name.'/view/viewAttachment.php');
		break;
	}
	case 'editNote':
	case 'addNote': {
		include ($gfplugins.$mantisbt->name.'/view/addOrEditNote.php');
		break;
	}
	// default page is view All issues
	default: {
		include ($gfplugins.$mantisbt->name.'/view/viewIssues.php');
	}
}
