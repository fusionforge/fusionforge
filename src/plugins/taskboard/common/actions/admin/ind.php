<?php
/**
 * Tracker Links
 *
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2014, Franck Villaume - TrivialDev
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

$taskboard->header(
	array(
		'title' => _('Taskboard for ').$group->getPublicName()._(': ')._('Administration'),
		'pagename' => _('Administration'),
		'sectionvals' => array(group_getname($group_id)),
		'group' => $group_id
	)
);

if($taskboard->getID()) {
	echo html_e('p', array(), util_make_link('/plugins/taskboard/admin/?group_id='.$group_id.'&action=trackers',
						html_e('strong', array(), _('Configure Trackers')))
				.html_e('br'). _('Choose and configure trackers, used with taskboard.'));

	echo html_e('p', array(), util_make_link('/plugins/taskboard/admin/?group_id='.$group_id.'&action=columns',
						html_e('strong', array(), _('Configure Columns')))
				.html_e('br'). _('Configure taskboard columns.'));
} else {
	echo html_e('p', array(), util_make_link('/plugins/taskboard/admin/?group_id='.$group_id.'&action=init',
						html_e('strong', array(), _('Initialize taskboard')))
				.html_e('br'). _('Create initial taskboard configuration'));
}
