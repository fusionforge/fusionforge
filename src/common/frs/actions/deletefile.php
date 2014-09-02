<?php
/**
 * FusionForge FRS: Add release Action
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
global $HTML;

$sysdebug_enable = false;
$result = array();

$package_id = getIntFromRequest('package_id');
$file_ids_string = getStringFromRequest('file_id');
if (!forge_check_perm('frs', $package_id, 'file')) {
	$result['html'] = $HTML->error_msg(_('FRS Action Denied.'));
} elseif ($file_ids_string) {
	$file_ids = explode(',', $file_ids_string);
	$result['format'] = 'multi';
	foreach ($file_ids as $key => $file_id) {
		$frsf = frsfile_get_object($file_id);
		if (!$frsf || !is_object($frsf)) {
			$result[$key]['html'] = $HTML->error_msg(_('Error Getting FRSPackage'));
		} elseif ($frsf->isError()) {
			$result[$key]['html'] = $HTML->error_msg($frsf->getErrorMessage());
		} elseif (!$frsf->delete()) {
			$result[$key]['html'] = $HTML->error_msg($frsf->getErrorMessage());
		} else {
			$result[$key]['html'] = $HTML->feedback(_('File successfully deleted.'));
			$result[$key]['deletedom'] = 'fileid'.$file_id;
		}
	}
} else {
	$result['html'] = $HTML->error_msg(_('Missing file_id'));
}

echo json_encode($result);
exit;
