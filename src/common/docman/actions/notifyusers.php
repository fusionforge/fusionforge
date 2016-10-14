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

$emails = getStringFromRequest('emails', null);
if (!$emails) {
	$warning_msg = _('No email address found.');
	session_redirect($urlparam);
}
$details = getStringFromRequest('details');
$sanitizer = new TextSanitizer();
$details = $sanitizer->SanitizeHtml($details);
$emailsErrArr = validate_emails($emails, ',');
$emailsArr = explode(',', $emails);
$subject = '['.$d->Group->getPublicName().'] '._('Notification on document').' - '.$d->getName();
$body = _('Project')._(': ').$d->Group->getPublicName()."\n";
$body .= _('Document Folder')._(': ').$d->getDocGroupName()."\n";
$body = _('Document Title')._(': ').$d->getName()."\n";
$body .= _('Document Filename')._(': ').$d->getFileName()."\n";
$body .= _('Direct Link')._(': ').util_make_url('/docman/?group_id='.$d->Group->getID().'&view=listfile&dirid='.$d->getDocGroupID().'&filedetailid='.$d->getID())."\n";
$body .= _('Notification Comments')._(':')."\n";
$body .= $details;
$sendEmails = 0;
foreach ($emailsArr as $key => $toEmail) {
	if (!in_array(trim($toEmail), $emailsErrArr)) {
		util_send_message(trim($toEmail), $subject, $body);
		$sendEmails++;
	}
}

$feedback = sprintf(ngettext('%s user notified.', '%s users notified.', $sendEmails), $sendEmails);
if (count($emailsErrArr)) {
	$warning_msg = sprintf(ngettext('%s email rejected due to wrong syntax.', '%s emails rejected due to wrong syntax.', count($emailsErrArr)), count($emailsErrArr));
}
session_redirect($urlparam);
