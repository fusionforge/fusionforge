<?php
/**
 * Projects Hierarchy Plugin
 * docman hierarchy view
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
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

global $g; //group object
global $group_id; // id of the group
global $HTML;
$projectsHierarchy = plugin_get_object('projects-hierarchy');
$globalConfArray = $projectsHierarchy->getGlobalConf();

if ($globalConfArray['docman']) {
	echo $HTML->openForm(array('id' => 'projectsHierarchyDocman', 'name' => 'projectsHierarchyDocman', 'method' => 'post', 'action' => '/plugins/'.$projectsHierarchy->name.'/?action=projectsHierarchyDocman&id='.$group_id.'&type=group'));
	echo html_ao('ul');
	$label = _('Enable hierarchical browsing');
	$status = 1;
	if ($projectsHierarchy->getDocmanStatus($group_id)) {
		$label = _('Disable hierarchical browsing');
		$status = 0;
	}
	echo html_e('input', array('name' => 'status', 'type' => 'hidden', 'value' => $status));
	echo html_ao('li');
	echo html_e('input', array('id' => 'projectsHierarchyDocmanSubmit', 'type' => 'submit', 'value' => $label));
	echo html_ac(html_ap() - 2);
	echo $HTML->closeForm();
}
