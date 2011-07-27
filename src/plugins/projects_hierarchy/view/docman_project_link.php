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
?>