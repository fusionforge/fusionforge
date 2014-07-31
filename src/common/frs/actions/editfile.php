<?php
/**
 * Project Admin: Edit File of Release
 *
 * Copyright 2014, Franck Villaume - TrivialDev
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
	$warning_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$file_id = getIntFromRequest('file_id');
$processor_id = getIntFromRequest('processor_id');
$type_id = getIntFromRequest('type_id');
$new_release_id = getIntFromRequest('new_release_id');
$release_time = getStringFromRequest('release_time');
$group_id = getIntFromRequest('group_id');
$release_id = getIntFromRequest('release_id');
$im_sure = getStringFromRequest('im_sure');

$frsf = frsfile_get_object($file_id);
if (!$frsf || !is_object($frsf)) {
	exit_error(_('Could Not Get FRSFile'),'frs');
} elseif ($frsf->isError()) {
	exit_error($frsf->getErrorMessage(),'frs');
} else {
	//$date_list = split('[- :]',$release_time,5);
	//$release_time = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
	$release_time = strtotime($release_time);
	if (!$frsf->update($type_id, $processor_id, $release_time, $new_release_id)) {
		$error_msg = $frsf->getErrorMessage();
	} else {
		$feedback .= _('File Updated');
	}
}

session_redirect('/frs/?group_id='.$group_id.'&view=editrelease&package_id='.$package_id.'&release_id='.$release_id);
