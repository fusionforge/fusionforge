<?php
/**
 * Tracker Links
 *
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015 Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
 *
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

//
//  SHOW LINKS TO FUNCTIONS
//

global $group, $group_id, $pluginTaskboard, $taskboard;

$taskboard->header(
	array(
		'title' => $taskboard->getName()._(': ')._('Administration'),
		'pagename' => _('Administration'),
		'sectionvals' => array($group->getPublicName()),
		'group' => $group_id
	)
);
$taskboard_id = $taskboard->getID();

echo html_e('p', array(), util_make_link('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=init',
		html_e('strong', array(), _('Configure Task Board')))
		.html_e('br'). _('Change Task Board name and description.'));

echo html_e('p', array(), util_make_link('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=trackers',
					html_e('strong', array(), _('Configure Trackers used')))
			.html_e('br'). _('Choose and configure trackers, used with Task Board.'));

echo html_e('p', array(), util_make_link('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=columns',
					html_e('strong', array(), _('Configure Columns')))
			.html_e('br'). _('Configure Task Board columns.'));

echo html_e('p', array(), util_make_link('/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&view=delete_taskboard',
		html_e('strong', array(), _('Delete Task Board')))
		.html_e('br'). _('Permanently delete this Task Board.'));
