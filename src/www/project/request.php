<?php
/**
 * Project Membership Request
 *
 * Copyright 2005 (c) GForge, L.L.C.
 * Copyright 2012,2014, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$group_id=getIntFromGet('group_id');
$submit=getStringFromPost('submit');
$comments=getStringFromPost('comments');

if (!$group_id) {
	exit_no_group();
}

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group = group_get_object($group_id);

if ($submit) {
	$gjr=new GroupJoinRequest($group);
	$usr=&session_get_user();
	if (!$gjr->create($usr->getID(),$comments)) {
		$error_msg = $gjr->getErrorMessage();
		session_redirect('/projects/'.$group->getUnixName());
	} else {
		$feedback = _('Your request has been submitted.');
		session_redirect('/projects/'.$group->getUnixName());
	}
}

$title = _('Request to join project') . ' '.$group->getPublicName();

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'summary'));

plugin_hook ("blocks", "request_join");
$nbadmins = count($group->getAdmins());
echo html_e('p', array(), ngettext('You can request to join a project by clicking the submit button. The administrator will be emailed to approve or deny your request.', 'You can request to join a project by clicking the submit button. The administrators will be emailed to approve or deny your request.', $nbadmins));
echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF').'?group_id='.$group_id, 'method' => 'post'));
echo html_e('p', array(), ngettext('You must send a comment to the administrator:', 'You must send a comment to the administrators:',$nbadmins).utils_requiredField());
echo html_e('textarea', array('name' => 'comments', 'required' => 'required', 'rows' => 15, 'cols' => 60), $comments, false);
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit'))));
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
site_project_footer();
