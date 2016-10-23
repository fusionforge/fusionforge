<?php
/**
 * FusionForge FRS: Delete association Action
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
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
$frsr = frsrelease_get_object($release_id);
if (!$frsr || !is_object($frsr)) {
	exit_error(_('Could Not Get FRS Release'), 'frs');
} elseif ($frsr->isError()) {
	exit_error($frsr->getErrorMessage(), 'frs');
}

$objectRefId = getStringFromRequest('objectrefid');
$objectId = getStringFromRequest('objectid');
$objectType = getStringFromRequest('objecttype');
$link = getStringFromRequest('link');
$was_error = false;
if ($link == 'to') {
	if (!$frsr->removeAssociationTo($objectId, $objectRefId, $objectType)) {
		$error_msg = $frsr->getErrorMessage();
		$was_error = true;
	}
} elseif ($link == 'from') {
	if (!$frsr->removeAssociationFrom($objectId, $objectRefId, $objectType)) {
		$error_msg = $frsr->getErrorMessage();
		$was_error = true;
	}
} elseif ($link == 'any') {
	if (!$frsr->removeAllAssociations()) {
		$error_msg = $frsr->getErrorMessage();
		$was_error = true;
	}
}
if (!$was_error) {
	$feedback = _('Associations removed successfully');
}

session_redirect('/frs/?group_id='.$group_id.'&view=editrelease&release_id='.$release_id.'&package_id='.$package_id);
