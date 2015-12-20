<?php
/**
 * FusionForge Documentation Manager
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; // Group object
global $dirid; //id of doc_group
global $group_id; // id of group
global $childgroup_id; // id of child group if any
global $feedback;
global $error_msg;
global $warning_msg;

$sysdebug_enable = false;

$urlparam = '/docman/?group_id='.$group_id;
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$urlparam .= '&childgroup_id='.$childgroup_id;
}

$doc_group = getIntFromRequest('doc_group');
$fromview = getStringFromRequest('fromview');

switch ($fromview) {
	case 'listrashfile': {
		$urlparam .= '&view='.$fromview;
		break;
	}
	default: {
		$urlparam .= '&dirid='.$doc_group;
		break;
	}
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Action Denied.');
	session_redirect($urlparam);
}

$itemid = getIntFromRequest('itemid');
$details = getIntFromRequest('details');

$d = document_get_object($itemid, $g->getID());
if ($d->isError()) {
	$error_msg = $d->getErrorMessage();
	session_redirect($urlparam);
}

$filearray = array();
if ($details) {
	$filearray['name'] = $d->getFileName();
	$filearray['type'] = $d->getFileType();
	$filearray['title'] = $d->getName();
	$filearray['description'] = $d->getDescription();
	$filearray['stateid'] = $d->getStateID();
	$filearray['docgroupid'] = $d->getDocGroupID();
	$filearray['isurl'] = $d->isURL();
}
$filearray['body'] = $d->getFileData();
echo json_encode($filearray);
exit;
