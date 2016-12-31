<?php
/**
 * Projects Hierarchy Plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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

global $HTML;
global $projectsHierarchy;
global $use_tooltips;
global $group_id;

$projectsHierarchyProjectConf = $projectsHierarchy->getConf($group_id);
if (!$projectsHierarchyProjectConf) {
	echo $HTML->error_msg(_('Cannot retrieve data from DB'));
} else {
	echo $HTML->boxTop($projectsHierarchy->text._(': ')._('Manage project configuration'));
	echo $HTML->openForm(array('method' => 'post', 'action' => '/plugins/'.$projectsHierarchy->name.'/?type=admin&group_id='.$group_id.'&pluginname='.$projectsHierarchy->name.'&action=updateProjectConf'));
	echo $HTML->listTableTop();
	$cells = array();
	$cells[][] = html_e('label', array('id' => 'projectsHierarchy-tree', 'title' => _('Enable visibility in hierarchy tree.')), _('Enable Tree'));
	$inputAttrs = array('type' => 'checkbox', 'name' => 'tree', 'value' => 1);
	if ($projectsHierarchyProjectConf['tree'])
		$inputAttrs['checked'] = 'checked';
	$cells[][] = html_e('input', $inputAttrs);
	echo $HTML->multiTableRow(array(), $cells);

	$cells = array();
	$cells[][] = html_e('label', array('id' => 'projectsHierarchy-docman', 'title' => _('Enable hierarchy in docman browsing. Direct access to docman features in parent docman tab.')), _('Enable Docman'));
	$inputAttrs = array('type' => 'checkbox', 'name' => 'docman', 'value' => 1);
	if ($projectsHierarchyProjectConf['docman'])
		$inputAttrs['checked'] = 'checked';
	$cells[][] = html_e('input', $inputAttrs);
	echo $HTML->multiTableRow(array(), $cells);

	$cells = array();
	$cells[][] = html_e('label', array('id' => 'projectsHierarchy-forum', 'title' => _('Enable hierarchical view for browsing in Forum main page.')), _('Enable Forum'));
	$inputAttrs = array('type' => 'checkbox', 'name' => 'forum', 'value' => 1);
	if ($projectsHierarchyProjectConf['forum']) {
		$inputAttrs['checked'] = 'checked';
	}
	$cells[][] = html_e('input', $inputAttrs);
	echo $HTML->multiTableRow(array(), $cells);

	$cells = array();
	$cells[][] = html_e('label', array('id' => 'projectsHierarchy-frs', 'title' => _('Enable hierarchical view for browsing in FRS main page.')), _('Enable FRS'));
	$inputAttrs = array('type' => 'checkbox', 'name' => 'frs', 'value' => 1);
	if ($projectsHierarchyProjectConf['frs']) {
		$inputAttrs['checked'] = 'checked';
	}
	$cells[][] = html_e('input', $inputAttrs);
	echo $HTML->multiTableRow(array(), $cells);

	$cells = array();
	$cells[][] = html_e('label', array('id' => 'projectsHierarchy-tracker', 'title' => _('Enable hierarchical view for browsing in Tracker main page.')), _('Enable Tracker'));
	$inputAttrs = array('type' => 'checkbox', 'name' => 'tracker', 'value' => 1);
	if ($projectsHierarchyProjectConf['tracker']) {
		$inputAttrs['checked'] = 'checked';
	}
	$cells[][] = html_e('input', $inputAttrs);
	echo $HTML->multiTableRow(array(), $cells);

	$cells = array();
	$cells[][] = html_e('label', array('id' => 'projectsHierarchy-globalconf', 'title' => _('Use forge global configuration. Superseed any configuration done at project level.')), _('Enable forge global configuration'));
	$inputAttrs = array('type' => 'checkbox', 'name' => 'globalconf', 'value' => 1);
	if ($projectsHierarchyProjectConf['globalconf'])
		$inputAttrs['checked'] = 'checked';
	$cells[][] = html_e('input', $inputAttrs);
	echo $HTML->multiTableRow(array(), $cells);

	echo $HTML->listTableBottom();
	echo html_e('input', array('type' => 'submit', 'value' => _('Update')));
	echo $HTML->closeForm();
	echo $HTML->boxBottom();
}
