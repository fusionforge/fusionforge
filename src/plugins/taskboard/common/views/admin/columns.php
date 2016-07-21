<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $group_id, $group, $HTML, $pluginTaskboard, $taskboard;

$taskboard->header(
	array(
		'title' => $taskboard->getName()._(': ')._('Administration')._(': ')._('Columns configuration'),
		'pagename' => _('Columns configuration'),
		'sectionvals' => array(group_getname($group_id)),
		'group' => $group_id
	)
);

if (count($taskboard->getUsedTrackersIds()) == 0) {
	echo $HTML->warning_msg(_('Choose at least one tracker for using with taskboard.'));
} else {
	if ($taskboard->isError()) {
		echo $HTML->error_msg($taskboard->getErrorMessage());
	} else {
		echo html_e('div', array('id' => 'messages', 'style' => 'display: none;'), '', false);
	}

	$taskboard_id = $taskboard->getID();
	$columns = $taskboard->getColumns();
	$tablearr = array(_('Order'), _('Title'), _('Max number of tasks'), _('Assigned resolutions'), _('Drop resolution'));

	echo $HTML->listTableTop($tablearr, false, 'sortable_table_tracker', 'sortable_table_tracker');
	foreach ($columns as $column) {
		$downLink = '';
		if ($column->getOrder() < count($columns)) {
			$downLink = util_make_link('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&column_id='.$column->getID().'&action=down_column', html_image('pointer_down.png', 16, 16, array('title' => _('Down'), 'alt' => _('Down'))));
		}
		$cells = array();
		$cells[][] = $column->getOrder().'&nbsp;'.$downLink;
		$cells[][] = html_e('div', array('style' => 'float: left; border: 1px solid grey; height: 30px; width: 20px; background-color: '.$column->getColumnBackgroundColor().'; margin-right: 10px;'), html_e('div', array('style' => 'width: 100%; height: 10px; background-color: '.$column->getTitleBackgroundColor()), '', false)).
					util_make_link('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&column_id='.$column->getID().'&view=edit_column',
					$column->getTitle());
		$cells[][] = ( $column->getMaxTasks() ? $column->getMaxTasks() : '&nbsp;' );
		$cells[][] = implode(', ', array_values($column->getResolutions()));
		$cells[][] = $column->getResolutionByDefault();
		echo $HTML->multiTableRow(array('valign' => 'middle'), $cells);
	}
	echo $HTML->listTableBottom();

	$unused_resolutions = array_values($taskboard->getUnusedResolutions());
	echo html_e('h2', array(), _('Add new column').(':'));
	if (count($unused_resolutions)) {
		echo $HTML->openForm(array('action' => '/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&action=columns', 'method' => 'post'));
		echo html_e('input', array('type' => 'hidden', 'name' => 'post_changes', 'value' => 'y'));
		echo $HTML->listTableTop();
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Title').utils_requiredField()._(':'));
		$cells[][] = html_e('input', array('type' => 'text', 'name' => 'column_title', 'required' => 'required'));
		echo $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Title background color')._(':'));
		$cells[][] = $taskboard->colorBgChooser('title_bg_color');
		echo $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Column Background color')._(':'));
		$cells[][] = $taskboard->colorBgChooser('column_bg_color', 'white');
		echo $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Maximum tasks number')._(':'));
		$cells[][] = html_e('input', array('type' => 'text', 'name' => 'column_max_tasks'));
		echo $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Drop resolution by default').utils_requiredField()._(':'));

		$cells[][] = html_build_select_box_from_arrays( $unused_resolutions, $unused_resolutions, 'resolution_by_default', NULL, false);
		echo $HTML->multiTableRow(array(), $cells);
		echo $HTML->listTableBottom();
		echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'post_changes', 'value' => _('Submit'))));
		echo $HTML->closeForm();
		echo $HTML->addRequiredFieldsInfoBox();
	} else {
		echo $HTML->information(_('All resolutions are mapped to columns. To add a new column, you need at least one unmapped resolution.'));
	}
}
