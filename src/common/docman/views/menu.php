<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2000, Quentin Cregan/SourceForge
 * Copyright 2002-2004, GForge Team
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2015,2021, Franck Villaume - TrivialDev
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
global $HTML; // Layout object
global $d_arr; // document array
global $group_id; // id of group
global $g; // Group object
global $warning_msg;

if (!forge_check_perm('docman', $group_id, 'read')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

$dm = new DocumentManager($g);

/* create the submenu following role, rules and content */
$menu_text = array();
$menu_links = array();
$menu_attr = array();

$menu_text[] = _('View Documents');
$menu_links[] = '/docman/?group_id='.$group_id;
$menu_attr[] = array('title' => _('View documents and folders in 2 panels. Left a folder tree, right a list of files of selected folder.'), 'id' => 'listFileDocmanMenu');

if (forge_check_perm('docman', $group_id, 'submit')) {
	$menu_text[] = _('Add new item');
	$menu_links[] = '/docman/?group_id='.$group_id.'&view=additem';
	$menu_attr[] = array('title' => _('Add a new item such as file, create directory, inject a ZIP at root level.'), 'id' => 'addItemDocmanMenu');
}

if ($g->useDocmanSearch()) {
	$menu_text[] = _('Search');
	$menu_links[] = '/docman/?group_id='.$group_id.'&view=search';
	$menu_attr[] = array('title' => _('Search documents in this project using keywords.'), 'id' => 'searchDocmanMenu');
}

if (forge_check_perm('docman', $group_id, 'approve') && !$dm->isTrashEmpty()) {
	$menu_text[] = _('Trash');
	$menu_links[] = '/docman/?group_id='.$group_id.'&view=listtrashfile';
	$menu_attr[] = array('title' => _('Recover or delete permanently files with deleted status.'), 'id' => 'trashDocmanMenu');
}

if (forge_check_perm('docman', $group_id, 'admin')) {
	$menu_text[] = _('Reporting');
	$menu_links[] = '/docman/?group_id='.$group_id.'&view=reporting';
	$menu_attr[] = array('title' => _('Docman module reporting.'), 'id' => 'reportDocmanMenu');
	$menu_text[] = _('Administration');
	$menu_links[] = '/docman/?group_id='.$group_id.'&view=admin';
	$menu_attr[] = array('title' => _('Docman module administration.'), 'id' => 'adminDocmanMenu');
}

if (count($menu_text)) {
	echo $HTML->subMenu($menu_text, $menu_links, $menu_attr);
}

plugin_hook('blocks', 'doc index');
