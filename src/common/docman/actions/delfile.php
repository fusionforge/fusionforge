<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2014-2015 Franck Villaume - TrivialDev
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
global $dirid; // id of doc_group
global $group_id; // id of group
global $childgroup_id; // id of child group if any

$urlredirect = '/docman/?group_id='.$group_id.'&view=listtrashfile&dirid='.$dirid;

// plugin projects-hierarchy handler
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$urlredirect .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($urlredirect);
}

$arr_fileid = explode(',', getStringFromRequest('fileid'));
foreach ($arr_fileid as $fileid) {
	if (!empty($fileid)) {
		$d = document_get_object($fileid, $g->getID());
		if ($d->isError() || !$d->delete()) {
			$error_msg = $d->getErrorMessage();
			session_redirect($urlredirect);
		}
	} else {
		$warning_msg = _('No action to perform');
		session_redirect($urlredirect);
	}
}
$count = count($arr_fileid);
$feedback = sprintf(ngettext('%s document deleted successfully.', '%s documents deleted successfully.', $count), $count);
session_redirect($urlredirect);
