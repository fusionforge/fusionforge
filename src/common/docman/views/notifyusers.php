<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2015, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; // Group object
global $group_id; // id of the group
global $HTML;
global $warning_msg;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect(DOCMAN_BASEURL.$group_id);
}

$userObjects = $g->getUsers();
$userNameArray = array();
$userIDArray = array();
foreach ($userObjects as $userObject) {
	$userNameArray[] = $userObject->getRealname();
	$userIDArray[]   = $userObject->getID();
}

echo html_ao('div', array('id' => 'notifyUsers'));
echo $HTML->openForm(array('id' => 'notifyusersdoc', 'name' => 'notifyusersdoc', 'method' => 'post', 'enctype' => 'multipart/form-data'));
echo $HTML->listTableTop(array(), array(), 'full');
$cells = array();
$cells[][] = _('Document Title')._(':');
$cells[][] = html_e('span', array('id' => 'notifytitle', 'type' => 'text', 'name' => 'title'), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = _('Description')._(':');
$cells[][] = html_e('span', array('id' => 'notifydescription', 'type' => 'text', 'name' => 'description'), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = _('File')._(':');
$cells[][] = html_e('a', array('id' => 'notifyfilelink'), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = _('Users to notify')._(':');
$cells[][] = html_e('p', array(), html_build_multiple_select_box_from_arrays($userIDArray, $userNameArray, 'userids[]', array(), 8, false, 'none', false, array('id' => 'notify-userids')));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Specific content to be added to the email body'), 'colspan' => 2, 'title' => _('Project, Folder, Title, Filename and direct link to the document will be added automatically.'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(html_e('textarea', array('id' => 'defaulteditzone', 'name' => 'details', 'rows' => '5', 'cols' => '60'), '', false), 'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'hidden', 'id' => 'notifydocid', 'name' => 'notifydocid'));
echo $HTML->closeForm();
echo html_ac(html_ap() -1);
