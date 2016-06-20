<?php
/**
 * admin hierarchy view
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2013,2014 Franck Villaume - TrivialDev
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
global $HTML;
$projectsHierarchy = plugin_get_object('projects-hierarchy');

echo html_e('h3', array(), _('Modify the hierarchy'));

$parent = $projectsHierarchy->getFamily($group_id, 'parent', false, 'validated');
if (sizeof($parent)) {
	echo html_e('h4', array(), _('Parent'));
	$parentGroup = group_get_object($parent[0]);
	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$projectsHierarchy->name.'/?type=group&action=removeParent&id='.$group_id.'&parent_id='.$parentGroup->getID()));
	echo util_make_link('/projects/'.$parentGroup->getUnixName(),$parentGroup->getPublicName(),array('title'=>_('Browse this project')));
	echo '<input type="submit" value="'._('Remove parent project').'">';
	echo $HTML->closeForm();
}

$childs = $projectsHierarchy->getFamily($group_id, 'child', false, 'validated');
if (sizeof($childs)) {
	echo html_e('h4', array(), _('Children'));
	foreach ($childs as $child) {
		$childGroup = group_get_object($child);
		echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$projectsHierarchy->name.'/?type=group&action=removeChild&id='.$group_id.'&child_id='.$childGroup->getID()));
		echo util_make_link('/projects/'.$childGroup->getUnixName(),$childGroup->getPublicName(),array('title'=>_('Browse this project')));
		echo '<input type="submit" value="'._('Remove child project').'">';
		echo $HTML->closeForm();
	}
}

$childs = $projectsHierarchy->getFamily($group_id, 'child', false, 'pending');
if (sizeof($childs)) {
	echo html_e('h4', array(), _('Pending'));
	foreach ($childs as $child) {
		$childGroup = group_get_object($child);
		echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$projectsHierarchy->name.'/?type=group&action=removeChild&id='.$group_id.'&child_id='.$childGroup->getID()));
		echo util_make_link('/projects/'.$childGroup->getUnixName(),$childGroup->getPublicName(),array('title'=>_('Browse this project')));
		echo '<input type="submit" value="'._('Remove child project').'">';
		echo $HTML->closeForm();
	}
}

echo html_e('h4', array(), _('Add new child'));
echo $projectsHierarchy->son_box($group_id, 'sub_project_id', '0');

echo html_e('h4', array(), _('Pending hierarchy request'));
$pendingParent = $projectsHierarchy->getFamily($group_id, 'parent', false, 'pending');
if (sizeof($pendingParent)) {
	$pendingParentGroup = group_get_object($pendingParent[0]);
	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$projectsHierarchy->name.'/?type=group&action=validateRelationship&id='.$group_id.'&relation=parent'));
	echo '<input type="hidden" name="validation_id" value="'.$pendingParent[0].'" />';
	echo _('Validate parent').' '.util_make_link('/projects/'.$pendingParentGroup->getUnixName(), $pendingParentGroup->getPublicName(), array('title'=>_('Browse this project')));
	echo html_build_select_box_from_arrays(array(1,0), array(_('Yes'), _('No')), 'validation_status', 'xzxz', false);
	echo '<input type="submit" value="'. _('Send') .'" />';
	echo $HTML->closeForm();
}

if (!sizeof($pendingParent))
	echo $HTML->information(_('No pending requests'));
