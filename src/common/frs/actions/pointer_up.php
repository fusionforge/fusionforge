<?php
/**
 * FRS release delete vote action
 *
 * Copyright 2019, Franck Villaume - TrivialDev
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

global $feedback; // feedback message
global $error_msg; // error message

$package_id = getIntFromRequest('package_id');
if (!forge_check_perm('frs', $package_id, 'file')) {
	$error_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$release_id = getIntFromRequest('release_id');
$frsr = frsrelease_get_object($release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRS Release'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

if ($frsr->castVote(true)) {
	$feedback = _('Cast Vote successfully');
} else {
	$error_msg = $frsr->getErrorMessage();
} 

$view = getStringFromRequest('view', 'listpackages');
session_redirect('/frs/?group_id='.$group_id.'&view='.$view.'&release_id='.$release_id.'&package_id='.$package_id);
