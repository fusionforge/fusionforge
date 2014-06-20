<?php
/**
 * FusionForge FRS: Delete Package Action
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
global $HTML;

$sysdebug_enable = false;
$result = array();

if (!forge_check_perm('frs', $group_id, 'write')) {
	$result['html'] = $HTML->warning_msg(_('FRS Action Denied.'));
	echo json_encode($result);
	exit;
}

$package_id = getIntFromRequest('package_id');
$result['html'] = $HTML->error_msg(_('Missing package_id'));

if ($package_id) {
	$frsp = new FRSPackage($g, $package_id);
	if (!$frsp || !is_object($frsp)) {
		$result['html'] = $HTML->error_msg(_('Error Getting FRSPackage'));
		echo json_encode($result);
		exit;
	} elseif ($frsp->isError()) {
		$result['html'] = $HTML->error_msg($frsp->getErrorMessage());
		echo json_encode($result);
		exit;
	}
	$sure = getIntFromRequest('sure');
	$really_sure = getIntFromRequest('really_sure');
	if (!$frsp->delete($sure, $really_sure)) {
		$result['html'] = $HTML->error_msg($frsp->getErrorMessage());
		echo json_encode($result);
		exit;
	}
	$result['html'] = $HTML->feedback(_('Pacakge successfully deleted'));
	$result['deletedom'] = 'pkgid'.$package_id;
}

echo json_encode($result);
exit;
