<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2015-2016, Franck Villaume - TrivialDev
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
global $dirid; // id of doc_group
global $group_id; // id of group
global $childgroup_id; // id of child group if any
global $feedback;
global $error_msg;
global $warning_msg;

$urlparam = '/docman/?group_id='.$group_id.'&dirid='.$dirid;

if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$urlparam .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($urlparam);
}

$docid = getIntFromRequest('notifydocid');

if (!$docid) {
	$warning_msg = _('No document found to notify.');
	session_redirect($urlparam);
}

$d = document_get_object($docid, $g->getID());
if ($d->isError()) {
	$error_msg = $d->getErrorMessage();
	session_redirect($urlparam);
}

$userIDs = getArrayFromRequest('notify-userids', array());
if (count($userIDS) <= 0) {
	$warning_msg = _('No users selected for notification.');
	session_redirect($urlparam);
}

$emailsArr = array();
foreach ($userIDs as $userID) {
	$user = user_get_object($userID);
	$emailsArr[] = $user->getEmail();
}

$details = getStringFromRequest('details');
$sanitizer = new TextSanitizer();
$details = $sanitizer->SanitizeHtml($details);
$subject = '['.$d->Group->getPublicName().'] '._('Notification on document').' - '.$d->getName();
$body = _('Project')._(': ').$d->Group->getPublicName()."\n";
$body .= _('Document Folder')._(': ').$d->getDocGroupName()."\n";
$body .= _('Document Title')._(': ').$d->getName()."\n";
$body .= _('Document Filename')._(': ').$d->getFileName()."\n";
$body .= _('Direct Link')._(': ').$d->getPermalink()."\n";
$body .= _('Notification Comment')._(':')."\n";
$body .= $details;
$sendEmails = 0;
foreach ($emailsArr as $key => $toEmail) {
	util_send_message(trim($toEmail), $subject, $body);
	$sendEmails++;
}

$feedback = sprintf(ngettext('%s user notified.', '%s users notified.', $sendEmails), $sendEmails);
session_redirect($urlparam);
