<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2014,2015, Franck Villaume - TrivialDev
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

$return_url = '/docman/?group_id='.$group_id.'&dirid='.$dirid;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($return_url);
}

$uploaded_zip = getUploadedFile('uploaded_zip');
$dg = documentgroup_get_object($dirid, $group_id);

if (!$dg || $dg->isError() || !$dg->injectArchive($uploaded_zip)) {
	$error_msg = $dg->getErrorMessage();
	session_redirect($return_url);
}

$feedback = _('Archive injected successfully.');
session_redirect($return_url);
