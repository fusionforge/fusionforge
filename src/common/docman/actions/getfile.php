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
global $dirid; //id of doc_group
global $group_id; // id of group

$sysdebug_enable = false;

$urlparam = '/docman/?group_id='.$group_id;
if (isset($childgroup_id) && $childgroup_id) {
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
		$urlparam .= '&view=listfile&dirid='.$doc_group;
		break;
	}
}

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg = _('Document Manager Action Denied.');
	session_redirect($urlparam.'&warning_msg='.urlencode($return_msg));
}

$fileid = getIntFromRequest('fileid');
$childgroup_id = getIntFromRequest('childgroup_id');
$details = getIntFromRequest('details');
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
}
$d = new Document($g, $fileid);
if ($d->isError())
	session_redirect($urlparam.'&error_msg='.urlencode($d->getErrorMessage()));

$filearray = array();
if ($details) {
	$filearray["name"] = $d->getFileName();
	$filearray["type"] = $d->getFileType();
	$filearray["title"] = $d->getName();
	$filearray["description"] = $d->getDescription();
	$filearray["stateid"] = $d->getStateID();
	$filearray["docgroupid"] = $d->getDocGroupID();
	$filearray["isurl"] = $d->isURL();
}
$filearray["body"] = $d->getFileData();
echo json_encode($filearray);
exit;
