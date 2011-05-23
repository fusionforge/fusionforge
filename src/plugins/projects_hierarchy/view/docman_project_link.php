<?php
/**
 * docman hierarchy view
 *
 * Copyright 2011, Franck Villaume - Capgemini
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

if ($projectsHierarchy->getFamily($group_id, 'parent')) {
	echo '<form id="projectsHierarchyDocman" name="projectsHierarchyDocman" method="post" action="/plugins/'.$projectsHierarchy->name.'/?action=projectsHierarchyDocman&id='.$group_id.'&type=group" >';
	echo '<ul>';
	$label = _('Enable hierarchical browsing');
	$status = 1;
	if ($projectsHierarchy->getDocmanStatus($group_id)) {
		$label = _('Disable hierarchical browsing');
		$status = 0;
	}
	echo '<input name="status" type="hidden" value="'.$status.'" />';
	echo '<li><input id="projectsHierarchyDocmanSubmit" type="submit" value="'.$label.'" /></li>';
	echo '</ul>';
	echo '</form>';
}

$childs = $projectsHierarchy->getFamily($group_id,'child', true);
if (sizeof($childs)) {
	if ($projectsHierarchy->getDocmanStatus($group_id)) {
		echo '<h3>'._('Subprojects Browsing Selection').'</h3>';
		// display a tree ? with checkbox ? to limit scope of browsing
		// include children of children ?
		echo _('TO BE IMPLEMENTED VIEW');
		echo '<table>';
		echo '<thead>';
		echo '<tr>';
		echo '<th>'._('Subprojects').'</th>';
		echo '<th>'._('Actions').'</th>';
		echo '</thead>';
		echo '<tbody>';
		foreach ($childs as $child) {
			$childGroup = group_get_object($child[0]);
			if ($childGroup->usesDocman()) {
				echo '<tr>';
				echo '<td>'.$childGroup->getPublicName().'</td>';
				echo '<td><label><input type="checkbox" />'._('Include in browsing').'</label></td>';
				echo '</tr>';
			}
		}
		echo '</tbody>';
		echo '</table>';
	}
}
?>
