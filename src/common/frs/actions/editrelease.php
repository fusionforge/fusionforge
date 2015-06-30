<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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

if (!forge_check_perm('frs', $package_id, 'release')) {
	$warning_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$release_id = getIntFromRequest('release_id');
$release_date = getStringFromRequest('release_date');
$release_name = getStringFromRequest('release_name');
$status_id = getIntFromRequest('status_id');
$uploaded_notes = getUploadedFile('uploaded_notes');
$uploaded_changes = getUploadedFile('uploaded_changes');
$release_notes = getStringFromRequest('release_notes');
$release_changes = getStringFromRequest('release_changes');
$preformatted = getStringFromRequest('preformatted');
$exec_changes = true;

//
//  Get the package
//
$frsp = frspackage_get_object($package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRS Package'), 'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(), 'frs');
}

//
//  Get the release
//
$frsr = frsrelease_get_object($release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRS Release'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

// Check for uploaded release notes
if ($uploaded_notes['tmp_name']) {
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
if ($uploaded_changes['tmp_name']) {
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

// If we haven't encountered any problems so far then save the changes
if ($exec_changes == true) {
	$release_date = strtotime($release_date);
	if (!$frsr->update($status_id, $release_name, $notes, $changes, $preformatted, $release_date)) {
		$error_msg = $frsr->getErrorMessage();
	} else {
		$feedback = _('Release edited successfully');
	}
}

session_redirect('/frs/?group_id='.$group_id.'&view=editrelease&release_id='.$release_id.'&package_id='.$package_id);
