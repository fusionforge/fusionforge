<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $HTML; // html object
global $d_arr; // document array
global $group_id; // id of group

if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

/* create the submenu following role, rules and content */
$menu_text = array();
$menu_links = array();
$menu_attr = array();

$menu_text[] = _('List files & Directories');
$menu_links[] = '/docman/?group_id='.$group_id.'&view=listfile&dirid=0';
$menu_attr[] = array('title' => _('View the files and the directories in 2 panels. Left a directory tree, right a list of files of specific directory'), 'id' => 'listFileDocmanMenu', 'class' => 'tabtitle-nw');

if (forge_check_perm('docman', $group_id, 'submit')) {
	$menu_text[] = _('Add new item');
	$menu_links[] = '/docman/?group_id='.$group_id.'&view=additem';
	$menu_attr[] = array('title' => _('Add a new item such as file, create directory, inject a zip at root level.'), 'id' => 'addItemDocmanMenu', 'class' => 'tabtitle');
}

if ($g->useDocmanSearch()) {
	if ($d_arr || count($d_arr) > 1) {
		$menu_text[] = _('Search in documents');
		$menu_links[] = '/docman/?group_id='.$group_id.'&amp;view=search';
		$menu_attr[] = array('title' => _('Search documents in this project using keywords.'), 'id' => 'searchDocmanMenu', 'class' => 'tabtitle');
	}
}

if (session_loggedin()) {
	if (forge_check_perm('docman', $group_id, 'approve')) {
		$menu_text[] = _('Trash');
		$menu_links[] = '/docman/?group_id='.$group_id.'&view=listtrashfile';
		$menu_attr[] = array('title' => _('Recover or delete permanently files with deleted status.'), 'id' => 'trashDocmanMenu', 'class' => 'tabtitle');
	}

	if (forge_check_perm('docman', $group_id, 'admin')) {
		$menu_text[] = _('Admin');
		$menu_links[] = '/docman/?group_id='.$group_id.'&amp;view=admin';
		$menu_attr[] = array('title' => _('Docman module administration.'), 'id' => 'adminDocmanMenu', 'class' => 'tabtitle');
	}
}

if (count($menu_text)) {
	echo $HTML->subMenu($menu_text, $menu_links, $menu_attr);
}

plugin_hook("blocks", "doc index");
?>
