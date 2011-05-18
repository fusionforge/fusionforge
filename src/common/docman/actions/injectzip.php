<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
global $g; // group object
global $group_id; // id of group

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg = _('Document Manager Action Denied.');
	if ($doc_group) {
		session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$doc_group.'&warning_msg='.urlencode($return_msg));
	} else {
		session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
	}
}

$doc_group = getIntFromRequest('dirid');
$uploaded_zip = getUploadedFile('uploaded_zip');
$dg = new DocumentGroup($g,$doc_group);
	
if ($dg->isError())
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($dg->getErrorMessage()));

if (!$dg->injectArchive($uploaded_zip)) {
	$return_msg = $dg->getErrorMessage();
	$return_url = '/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg);
} else {
	$return_msg = _('Archive injected successfully.');
	$return_url = '/docman/?group_id='.$group_id.'&feedback='.urlencode($return_msg);
}

if ($doc_group)
	$return_url .= '&dirir='.$doc_group;

session_redirect($return_url);
?>
