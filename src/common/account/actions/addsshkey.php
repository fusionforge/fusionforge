<?php
/**
 * add ssh key action
 *
 * Copyright 2012, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

global $u;

require_once $gfcommon.'include/account.php';

$authorized_key = getStringFromRequest('authorized_key');
$uploaded_filekey = getUploadedFile('uploaded_filekey');
if (strlen($authorized_key)) {
	$authorized_keys = explode("\n", $authorized_key);
	$authorized_key = $authorized_keys[0];
	checkKeys($authorized_key);
	if (!$u->addAuthorizedKey($authorized_key)) {
		$error_msg = $u->getErrorMessage();
		session_redirect('/account/');
	}
	$feedback = _('SSH Key added successfully.');
	session_redirect('/account/');
}

if (!is_uploaded_file($uploaded_filekey['tmp_name'])) {
	$error_msg = _('Invalid file name.');
	session_redirect('/account/');
}

$payload = fread(fopen($uploaded_filekey['tmp_name'], 'r'), $uploaded_filekey['size']);
if (strlen($payload)) {
	checkKeys($payload);
	if (!$u->addAuthorizedKey($payload)) {
		$error_msg = $u->getErrorMessage();
		session_redirect('/account/');
	}
	$feedback = _('SSH Key added successfully.');
	session_redirect('/account/');
}

session_redirect('/account/');
