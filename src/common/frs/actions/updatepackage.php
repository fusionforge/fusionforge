<?php
/**
 * FusionForge FRS: Edit Releases of Packages Action
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
global $feedback; // feedback message
global $error_msg; // error message

$sysdebug_enable = false;
$result = array();

$package_id = getIntFromRequest('package_id');

if (!forge_check_perm('frs', $package_id, 'admin')) {
	$result['html'] = $HTML->error_msg(_('FRS Action Denied.'));
	echo json_encode($result);
	exit;
}

$package_name = htmlspecialchars(trim(getStringFromRequest('package_name')));
$status_id = getIntFromRequest('status_id');

$result['html'] = $HTML->error_msg(_('Missing package_id or package_name'));

if ($package_id && $package_name) {
	$frsp = frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		$result['html'] = $HTML->error_msg(_('Error Getting FRSPackage'));
		echo json_encode($result);
		exit;
	} elseif ($frsp->isError()) {
		$result['html'] = $HTML->error_msg($frsp->getErrorMessage());
		echo json_encode($result);
		exit;
	}
	if (!$frsp->update($package_name, $status_id)) {
		$result['html'] = $HTML->error_msg($frsp->getErrorMessage());
		echo json_encode($result);
		exit;
	} else {
		$result['html'] = $HTML->feedback(_('Package successfully updated'));
	}
}
echo json_encode($result);
exit;
