<?php
/**
 * admin hierarchy view
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

global $group_id;
$projectsHierarchy = plugin_get_object('projects_hierarchy');

echo '<h3>'._('Modify the hierarchy').'</h3>';
echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=addChild&id='.$group_id.'">';
echo _('Select a project: ');
echo $projectsHierarchy->son_box($group_id, 'sub_project_id', '0');
echo '<input type="submit" value="'._('Add Child project').'">';
echo '</form>';

$child = $projectsHierarchy->getFamily($group_id, 'child');
if (sizeof($child)) {
	echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=addChild&id='.$group_id.'">';
	echo _('Select a project: ');
	//echo $projectsHierarchy->son_box($group_id, 'sub_project_id', '0');
	echo '<input type="submit" value="'._('Remove Child project').'">';
	echo '</form>';
}

$parent = $projectsHierarchy->getFamily($group_id, 'parent');
if (sizeof($parent)) {
	echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=addChild&id='.$group_id.'">';
	echo _('Select a project: ');
	//echo $projectsHierarchy->son_box($group_id, 'sub_project_id', '0');
	echo '<input type="submit" value="'._('Remove parent project').'">';
	echo '</form>';
}

$pendingParent = $projectsHierarchy->getFamily($group_id, 'parent', false, false);
if (sizeof($pendingParent)) {
	$parentGroup = group_get_object($pendingParent[0][0]);
	echo '<h3>'._('Pending hierarchy request').'</h3>';
	echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=validateRelationship&id='.$group_id.'">';
	echo '<input type="hidden" name="validation_id" value="'.$pendingParent[0][0].'" />';
	echo _('Validate parent').' '.$parentGroup->getPublicName();
	echo html_build_select_box_from_arrays(array(1,0), array(_('Yes'), _('No')), 'validation_status', 'xzxz', false);
	echo '<input type="submit" value="'. _('Send') .'" />';
	echo '</form>';
}

$pendingChilds = $projectsHierarchy->getFamily($group_id, 'child', false, false);
if (sizeof($pendingChilds)) {
	echo '<h3>'._('Pending hierarchy request').'</h3>';
	foreach ($pendingChilds as $pendingChild) {
		$childGroup = group_get_object($pendingChild[0][0]);
		echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=validateRelationship&id='.$group_id.'">';
		echo '<input type="hidden" name="validation_id" value="'.$pendingChild[0][0].'" />';
		echo _('Validate parent').' '.$childGroup->getPublicName();
		echo html_build_select_box_from_arrays(array(1,0), array(_('Yes'), _('No')), 'validation_status', 'xzxz', false);
		echo '<input type="submit" value="'. _('Send') .'" />';
		echo '</form>';
	}
}

?>
