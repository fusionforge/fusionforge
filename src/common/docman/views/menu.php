<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
 * Copyright 2010, Franck Villaume - Capgemini
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $HTML; // html object
global $d_arr; // document array
global $group_id; // id of group
global $g; // project group object

/* create the submenu following role, rules and content */
$menu_text=array();
$menu_links=array();

$menu_text[]=_('Submit new documentation');
$menu_links[]='/docman/?group_id='.$group_id.'&view=addfile';
if (session_loggedin()) {
	$menu_text[]=_('Add new documentation group');
	$menu_links[]='/docman/?group_id='.$group_id.'&view=addsubdocgroup';
}

if ($g->useDocmanSearch()) {
	if ($d_arr || count($d_arr) > 1) {
		$menu_text[]=_('Search in documents');
		$menu_links[]='/docman/?group_id='.$group_id.'&view=search';
	}
}

if (forge_check_perm ('docman', $group_id, 'approve')) {
	$menu_text[]=_('Admin');
	$menu_links[]='/docman/?group_id='.$group_id.'&view=admin';
}

echo $HTML->subMenu( $menu_text, $menu_links);

plugin_hook ("blocks", "doc index");

?>
