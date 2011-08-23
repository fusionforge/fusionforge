<?php
/**
 * projects_hierarchyPlugin Class
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
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

global $g; //group object
global $group_id; // id of the group
$projectsHierarchy = plugin_get_object('projects_hierarchy');

$parent = $projectsHierarchy->getFamily($group_id, 'parent', false, 'validated');
$childs = $projectsHierarchy->getFamily($group_id, 'child', false, 'validated');
if (sizeof($parent) || sizeof($childs)) {
	echo $HTML->boxTop(_('Linked projects'));
	if (sizeof($parent)) {
		echo '<ul>';
		$parentGroup = group_get_object($parent[0]);
		echo '<li>'._('Parent Project:').' '.util_make_link('/projects/'.$parentGroup->getUnixName(), $parentGroup->getPublicName(), array('class' => 'tabtitle', 'title' => _('Direct link to project'))).'</li>';
		echo '</ul>';
	}
	if (sizeof($childs)) {
		if (sizeof($parent))
			echo '<hr>';

		echo '<ul>';
		foreach ($childs as $child) {
			$childGroup = group_get_object($child[0]);
			echo '<li>'._('Child project').' '.util_make_link('/projects/'.$childGroup->getUnixName(), $childGroup->getPublicName(), array('class' => 'tabtitle', 'title' => _('Direct link to project'))).'</li>';
		}
		echo '</ul>';
	}
	echo '</ul>';
	echo $HTML->boxBottom();
}
?>
