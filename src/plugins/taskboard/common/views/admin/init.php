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

global $group, $group_id, $HTML, $pluginTaskboard, $taskboard;

require_once $gfplugins.'taskboard/common/include/TaskBoardFactoryHtml.class.php';

$taskboard_id = getIntFromRequest('taskboard_id');
if ($taskboard_id) {
	$taskboard->header(
		array(
			'title' => $taskboard->getName()._(': ')._('Administration - Configure Taskboard'),
			'pagename' => _('Administration'),
			'sectionvals' => array($group->getPublicName()),
			'group' => $group_id
		)
	);
	$taskboard_name = $taskboard->getName();
	$taskboard_description = $taskboard->getDescription();
} else {
	$taskboardFactory = new TaskBoardFactoryHtml($group);
	if (!$taskboardFactory || !is_object($taskboardFactory) || $taskboardFactory->isError()) {
		exit_error(_('Could Not Get TaskBoardFactory'),'taskboard');
	}

	$taskboardFactory->header(
		array(
			'title' => _('Taskboards for ').$taskboardFactory->Group->getPublicName()._(': ')._('Administration - Create New Taskboard'),
			'pagename' => _('Administration'),
			'sectionvals' => array($group->getPublicName()),
			'group' => $group_id
		)
	);
	$taskboard_name = '';
	$taskboard_description = '';
}

echo $HTML->openForm(array('action' => '/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&action=init', 'method' => 'post'));
if ($taskboard_id) {
	echo html_e('input', array('type' => 'hidden', 'name' => 'taskboard_id','value'=>$taskboard_id));
}
echo html_ao('p');
echo html_e('strong', array(), _('Taskboard Name').utils_requiredField());
echo html_e('br');
echo html_e('input', array('type' => 'text', 'name' => 'taskboard_name', 'size'=>60, 'required'=>'required','value'=>$taskboard_name));
echo html_ac(html_ap()-1);
echo html_ao('p');
echo html_e('strong', array(), _('Taskboard Description'));
echo html_e('br');
echo html_e('textarea', array('name' => 'taskboard_description', 'rows'=>3, 'cols'=>50),$taskboard_description,false);
echo html_ac(html_ap()-1);
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'post_changes', 'value' => _('Submit'))));

echo $HTML->closeForm();
