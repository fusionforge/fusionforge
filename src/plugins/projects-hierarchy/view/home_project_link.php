<?php
/**
 * Projects Hierarchy Plugin
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2013, French Ministry of Education
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

global $g; //group object
global $group_id; // id of the group
$projectsHierarchy = plugin_get_object('projects-hierarchy');

$parent = $projectsHierarchy->getFamily($group_id, 'parent', false, 'validated');
$childs = $projectsHierarchy->getFamily($group_id, 'child', false, 'validated');

if (sizeof($parent)) {
	$parentGroup = group_get_object($parent[0]);
	if (!forge_check_perm('project_read', $parentGroup->getID())) {
		array_pop($parent);
	}
}
if (sizeof($childs)) {
	for ($i = 0; $i < count($childs); $i++) {
		$childGroup = group_get_object($childs[$i]);
		if (!forge_check_perm('project_read', $childGroup->getID())) {
				unset($childs[$i]);
		}
	}
	$childs = array_values($childs);
}
if (sizeof($parent) || sizeof($childs)) {
	echo $HTML->boxTop(_('Linked projects'));
	if (sizeof($parent)) {
		echo html_ao('ul');
		$parentGroup = group_get_object($parent[0]);
		echo html_e('li', array(), _('Parent Project')._(': ').util_make_link('/projects/'.$parentGroup->getUnixName(), $parentGroup->getPublicName(), array('title' => _('Direct link to project'))));
		echo html_ac(html_ap() -1);
	}
	if (sizeof($childs)) {
		if (sizeof($parent)) {
			echo html_e('hr');
		}
		echo html_ao('ul');
		foreach ($childs as $child) {
			$childGroup = group_get_object($child);
			echo html_e('li', array(), _('Child project')._(': ').util_make_link('/projects/'.$childGroup->getUnixName(), $childGroup->getPublicName(), array('title' => _('Direct link to project'))));
		}
		echo html_ac(html_ap() -1);
	}
	echo $HTML->boxBottom();
}
