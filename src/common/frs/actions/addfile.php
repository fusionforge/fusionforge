<?php
/**
 * FusionForge FRS: Add file Action
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
if (!forge_check_perm('frs', $package_id, 'file')) {
	$error_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$release_id = getIntFromRequest('release_id');
$userfile = getUploadedFile('userfile');
$userfile_name = $userfile['name'];
$type_id = getIntFromRequest('type_id');
$release_date = getStringFromRequest('release_date');
// Build a Unix time value from the supplied Y-m-d value
$release_date = strtotime($release_date);
$processor_id = getIntFromRequest('processor_id');
$group_unix_name=group_getunixname($group_id);
$ftp_filename = getStringFromRequest('ftp_filename');
$manual_filename = getStringFromRequest('manual_filename');
$docman_fileid = getIntFromRequest('docman_fileid');

$frsp = frspackage_get_object($package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRS Package'), 'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(), 'frs');
}

$frsr = frsrelease_get_object($release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRS Release'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

$ret = frs_add_file_from_form($frsr, $type_id, $processor_id, $release_date, $userfile, $ftp_filename, $manual_filename, $docman_fileid);

if ($ret === true) {
	$feedback = _('File Released');
} else {
	$error_msg = $ret;
}

session_redirect('/frs/?group_id='.$group_id.'&view=editrelease&release_id='.$release_id.'&package_id='.$package_id);
