<?php
/**
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

global $group_id, $group, $taskboard, $pluginTaskboard, $HTML;

session_require_perm('tracker_admin', $group_id) ;

$release_id = getStringFromRequest('release_id','');
$release = new TaskBoardRelease( $taskboard, $release_id );

$taskboard->header(
	array(
		'title'=> $taskboard->getName()._(': ')._('Release').' : '._('Delete release') ,
		'pagename'=>_('Release')._(': ')._('Delete release'),
		'sectionvals'=>array(group_getname($group_id)),
		'group'=>$group_id
	)
);

if ($taskboard->isError()) {
	echo $HTML->error_msg($taskboard->getErrorMessage());
} else {
	echo html_e('div', array('id' => 'messages', 'style' => 'display: none;'), '', false);
}
$taskboard_id = $taskboard->getID();

echo $HTML->openForm(array('action' => '/plugins/'.$pluginTaskboard->name.'/releases/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&action=delete_release', 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'release_id', 'value' => $release_id));
echo html_e('h2', array(), _('Release') ." '".$release->getTitle() ."'");
echo html_e('div', array(), _('You are about to permanently and irretrievably delete this release with all indicators!'));
echo html_e('div', array(), html_e('input', array('type' => 'checkbox', 'value' => 'y', 'name' => 'confirmed'))._('I am Sure'));
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'post_delete', 'value' => _('Delete'))));
echo $HTML->closeForm();
