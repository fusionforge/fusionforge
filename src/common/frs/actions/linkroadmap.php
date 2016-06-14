<?php
/**
 * FusionForge FRS: Link Roadmap Action
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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
global $feedback; // feedback message
global $error_msg; // error message

$package_id = getIntFromRequest('package_id');
if (!forge_check_perm('frs', $package_id, 'release')) {
	$error_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$frsp = frspackage_get_object($package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRS Package'), 'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(), 'frs');
}

$release_id = getIntFromRequest('release_id');
$frsr = frsrelease_get_object($release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRS Release'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

$roadmap_id = getIntFromRequest('roadmap_id');
$roadmap_release = trim(getStringFromRequest('roadmap_release'));
$type = getStringFromRequest('type');
if ($roadmap_id && (strlen($roadmap_release) > 0)) {
	switch ($type) {
		case 'add': {
			if ($frsr->addLinkedRoadmap($roadmap_id, $roadmap_release)) {
				$feedback = _('Roadmap successfully linked');
			} else {
				$error_msg = _('Failed to link the roadmap');
			}
			break;
		}
		case 'del': {
			if ($frsr->deleteLinkedRoadmap($roadmap_id, $roadmap_release)) {
				$feedback = _('Roadmap successfully linked');
			} else {
				$error_msg = _('Failed to link the roadmap');
			}
			break;
		}
		default: {
			$warning_msg = _('No action to perform');
		}
	}
}

session_redirect('/frs/?group_id='.$group_id.'&view=editrelease&release_id='.$release_id.'&package_id='.$package_id);
