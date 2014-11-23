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
global $g; // group object
global $HTML;

$sysdebug_enable = false;
$result = array();

$package_id_strings = getStringFromRequest('package_id');

if (!$package_id_strings) {
	$result['html'] = $HTML->warning_msg(_('Missing package_id'));
} else {
	$package_ids = explode(',', $package_id_strings);
	$result['format'] = 'multi';
	foreach ($package_ids as $key => $package_id) {
		if (forge_check_perm('frs', $package_id, 'admin')) {
			$frsp = frspackage_get_object($package_id);
			if (!$frsp || !is_object($frsp)) {
				$result[$key]['html'] = $HTML->error_msg(_('Error Getting FRSPackage'));
			} elseif ($frsp->isError()) {
				$result[$key]['html'] = $HTML->error_msg($frsp->getErrorMessage());
			} else {
				$sure = getIntFromRequest('sure');
				$really_sure = getIntFromRequest('really_sure');
				if (!$frsp->delete($sure, $really_sure)) {
					$result[$key]['html'] = $HTML->error_msg($frsp->getErrorMessage());
				} else {
					$result[$key]['html'] = $HTML->feedback(_('Package successfully deleted.'));
					$result[$key]['deletedom'] = 'pkgid'.$package_id;
				}
			}
		} else {
			$result[$key]['html'] = $HTML->error_msg(_('FRS Action Denied'));
		}
	}
}

echo json_encode($result);
exit;
