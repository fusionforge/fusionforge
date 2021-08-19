<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2014,2015-2017,2021, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; // Group object
global $group_id; // id of group
global $feedback;
global $error_msg;
global $warning_msg;
global $dirid;

$return_url = DOCMAN_BASEURL.$group_id.'&dirid='.$dirid;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($return_url);
}

$uploadtype = getStringFromRequest('type');
if ($uploadtype == 'httpupload') {
	$uploaded_zip = getUploadedFile('uploaded_zip');
	if (empty($uploaded_zip['name'])) {
		$error_msg = _('Missing file or limit size exceeded');
		session_redirect($return_url);
	}
	if (strlen($uploaded_zip['tmp_name']) > 0 && !is_uploaded_file($uploaded_zip['tmp_name'])) {
		$error_msg = _('Invalid file name.');
		session_redirect($return_url);
	}
} elseif ($uploadtype == 'manualupload') {
	$uploaded_zip = array();
	$uploaded_zip['type'] = 'application/zip';
	$uploaded_zip['tmp_name'] = forge_get_config('groupdir_prefix').'/'.$g->getUnixName().'/incoming/'.getStringFromRequest('manual_path');
	if (!is_file($uploaded_zip['tmp_name'])) {
		$error_msg = _('Missing file');
		session_redirect($return_url);
	}
}

if ($dirid) {
	$dg = documentgroup_get_object($dirid, $group_id);
} else {
	$dg = new DocumentGroup(group_get_object($group_id));
}

if (!$dg) {
	$error_msg = _('No valid Group nor directory');
	session_redirect($return_url);
} elseif ($dg->isError() || !$dg->injectArchive($uploaded_zip)) {
	$error_msg = $dg->getErrorMessage();
	session_redirect($return_url);
}

$feedback = _('Archive injected successfully.');
session_redirect($return_url);
