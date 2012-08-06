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
$projectsHierarchy = plugin_get_object('projects-hierarchy');

echo '<h3>'._('Modify the hierarchy').'</h3>';
echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=addChild&id='.$group_id.'">';
echo _('Select a project: ');
echo $projectsHierarchy->son_box($group_id, 'sub_project_id', '0');
echo '<input type="submit" value="'._('Add Child project').'">';
echo '</form>';

$childs = $projectsHierarchy->getFamily($group_id, 'child', false, 'validated');
if (sizeof($childs)) {
	foreach ($childs as $child) {
		$childGroup = group_get_object($child[0]);
		echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=removeChild&id='.$group_id.'&child_id='.$childGroup->getID().'">';
		echo util_make_link('/projects/'.$childGroup->getUnixName(),$childGroup->getPublicName(),array('title'=>_('Browse this project'), 'class'=>'tabtitle-nw'));
		echo '<input type="submit" value="'._('Remove child project').'">';
		echo '</form>';
	}
}

$parent = $projectsHierarchy->getFamily($group_id, 'parent', false, 'validated');
if (sizeof($parent)) {
	$parentGroup = group_get_object($parent[0]);
	echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=removeParent&id='.$group_id.'&parent_id='.$parentGroup->getID().'">';
	echo util_make_link('/projects/'.$parentGroup->getUnixName(),$parentGroup->getPublicName(),array('title'=>_('Browse this project'), 'class'=>'tabtitle-nw'));
	echo '<input type="submit" value="'._('Remove parent project').'">';
	echo '</form>';
}

echo '<h3>'._('Pending hierarchy request').'</h3>';
$pendingParent = $projectsHierarchy->getFamily($group_id, 'parent', false, 'pending');
if (sizeof($pendingParent)) {
	$pendingParentGroup = group_get_object($pendingParent[0]);
	echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=validateRelationship&id='.$group_id.'&relation=parent">';
	echo '<input type="hidden" name="validation_id" value="'.$pendingParent[0].'" />';
	echo _('Validate parent').' '.util_make_link('/projects/'.$pendingParentGroup->getUnixName(), $pendingParentGroup->getPublicName(), array('title'=>_('Browse this project'), 'class'=>'tabtitle'));
	echo html_build_select_box_from_arrays(array(1,0), array(_('Yes'), _('No')), 'validation_status', 'xzxz', false);
	echo '<input type="submit" value="'. _('Send') .'" />';
	echo '</form>';
}

$pendingChilds = $projectsHierarchy->getFamily($group_id, 'child', false, 'pending');
if (sizeof($pendingChilds)) {
	foreach ($pendingChilds as $pendingChild) {
		$pendingChildGroup = group_get_object($pendingChild[0]);
		echo '<form method="post" action="/plugins/'.$projectsHierarchy->name.'/?type=group&pluginname='.$projectsHierarchy->name.'&action=validateRelationship&id='.$group_id.'&relation=child">';
		echo '<input type="hidden" name="validation_id" value="'.$pendingChild[0].'" />';
		echo _('Validate child').' '.util_make_link('/projects/'.$pendingChildGroup->getUnixName(), $pendingChildGroup->getPublicName(), array('title'=>_('Browse this project'), 'class'=>'tabtitle'));
		echo html_build_select_box_from_arrays(array(1,0), array(_('Yes'), _('No')), 'validation_status', 'xzxz', false);
		echo '<input type="submit" value="'. _('Send') .'" />';
		echo '</form>';
	}
}

if (!sizeof($pendingParent) && !sizeof($pendingChilds))
	echo '<p class="information">'._('No pending requests').'</p>';


?>
