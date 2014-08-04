<?php
/**
 * FusionForge FRS: Add package Action
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

if (!forge_check_perm('frs_admin', $group_id, 'admin')) {
	$warning_msg = _('FRS Action Denied.');
	session_redirect('/frs/?group_id='.$group_id);
}

$package_name = htmlspecialchars(trim(getStringFromRequest('package_name')));

if ($package_name) {
	//create a new package
	$frsp = new FRSPackage($g);
	if (!$frsp || !is_object($frsp)) {
		exit_error(_('Could Not Get FRS Package'), 'frs');
	} elseif ($frsp->isError()) {
		exit_error($frsp->getErrorMessage(), 'frs');
	}
	if (!$frsp->create($package_name)) {
		$error_msg = $frsp->getErrorMessage();
	} else {
		$feedback .= _('Added Package');
	}
} else {
	$error_msg = _('Missing package_name');
}
session_redirect('/frs/?group_id='.$group_id);
