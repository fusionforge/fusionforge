<?php
/**
 * FusionForge FRS: Add release Action
 *
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $group_id; // id of group
global $g; // group object
global $warning_msg; // warning message
global $feedback; // feedback message
global $error_msg; // error message

$package_id = getIntFromRequest('package_id');

if (!$package_id) {
	$error_msg = _('Missing package_id');
	session_redirect('/frs/?group_id='.$group_id.'&view=admin');
}

if (!forge_check_perm('frs', $package_id, 'release')) {
	$error_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$frsp = frspackage_get_object($package_id);
if (!$frsp || !is_object($frsp)) {
	$error_msg = _('Could Not Get FRS Package');
	session_redirect('/frs/?group_id='.$group_id.'&view=admin');
} elseif ($frsp->isError()) {
	$error_msg = $frsp->getErrorMessage();
	session_redirect('/frs/?group_id='.$group_id.'&view=admin');
}

$userfile = getUploadedFile('userfile');
$userfile_name = $userfile['name'];
$type_id = getIntFromRequest('type_id');
$release_name = getStringFromRequest('release_name');
$uploaded_notes = getUploadedFile('uploaded_notes');
$uploaded_changes = getUploadedFile('uploaded_changes');
$release_notes = getStringFromRequest('release_notes');
$release_changes = getStringFromRequest('release_changes');
$preformatted = getStringFromRequest('preformatted');
$release_date = getStringFromRequest('release_date');
// Build a Unix time value from the supplied Y-m-d value
$release_date = strtotime($release_date);
$processor_id = getIntFromRequest('processor_id');
$ftp_filename = getStringFromRequest('ftp_filename');
$manual_filename = getStringFromRequest('manual_filename');
$docman_fileid = getIntFromRequest('docman_fileid');
$exec_changes = true;

if (strlen($release_name) >= 3) {
	// Check for uploaded release notes
	if (isset($uploaded_notes['tmp_name']) && $uploaded_notes['tmp_name']) {
		if (!is_uploaded_file($uploaded_notes['tmp_name'])) {
			$error_msg = _('Attempted File Upload Attack');
			session_redirect('/frs/?group_id='.$group_id);
		}
		if ($uploaded_notes['type'] !== 'text/plain') {
			$error_msg .= _('Release Notes Are not in Text').'<br />';
			$exec_changes = false;
		} else {
			$notes = fread(fopen($uploaded_notes['tmp_name'], 'r'), $uploaded_notes['size']);
		}
	} else {
		$notes = $release_notes;
	}

	// Check for uploaded change logs
	if (isset($uploaded_changes['tmp_name']) && $uploaded_changes['tmp_name']) {
		if (!is_uploaded_file($uploaded_changes['tmp_name'])) {
			$error_msg = _('Attempted File Upload Attack');
			session_redirect('/frs/?group_id='.$group_id);
		}
		if ($uploaded_changes['type'] !== 'text/plain') {
			$error_msg .= _('Change Log Is not in Text').'<br />';
			$exec_changes = false;
		} else {
			$changes = fread(fopen($uploaded_changes['tmp_name'], 'r'), $uploaded_changes['size']);
		}
	} else {
		$changes = $release_changes;
	}

	if ($exec_changes) {
		$frsr = new FRSRelease($frsp);
		if ($frsr->create($release_name, $notes, $changes, $preformatted, $release_date)) {
			$feedback = _('Release successfully created');
			if (strlen($userfile_name) || strlen($ftp_filename) || strlen($manual_filename) || $docman_fileid) {
				$ret = frs_add_file_from_form($frsr, $type_id, $processor_id, $release_date, $userfile, $ftp_filename, $manual_filename, $docman_fileid);
				if ($ret === true) {
					$feedback .= _(' and file successfully added to the release.');
				} else {
					$error_msg = $ret;
				}
			}
			session_redirect('/frs/?group_id='.$group_id.'&view=showreleases&package_id='.$package_id);
		} else {
			$error_msg = $frsr->getErrorMessage();
		}
	}
} else {
	$error_msg = _('Missing release_name or too short release_name, at least 3 characters.');
}
session_redirect('/frs/?group_id='.$group_id.'&view=admin');
