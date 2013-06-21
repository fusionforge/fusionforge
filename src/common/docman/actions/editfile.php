<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
global $g; //group object
global $dirid; //id of doc_group
global $group_id; // id of group

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

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$return_msg = _('Document Manager Action Denied.');
	session_redirect($urlparam.'&warning_msg='.urlencode($return_msg));
}

$docid = getIntFromRequest('docid');
$title = getStringFromRequest('title');
$description = getStringFromRequest('description');
$details = getStringFromRequest('details');
$file_url = getStringFromRequest('file_url');
$uploaded_data = getUploadedFile('uploaded_data');
$stateid = getIntFromRequest('stateid');
$filetype = getStringFromRequest('filetype');
$editor = getStringFromRequest('editor');

if (!$docid)
	session_redirect($urlparam.'&warning_msg='.urlencode(_('No document found to update')));

$d= new Document($g, $docid);
if ($d->isError())
	session_redirect($urlparam.'&error_msg='.urlencode($d->getErrorMessage()));

$sanitizer = new TextSanitizer();
$details = $sanitizer->SanitizeHtml($details);
if (($editor) && ($d->getFileData() != $details) && (!$uploaded_data['name'])) {
	$filename = $d->getFileName();
	$datafile = tempnam("/tmp", "docman");
	$fh = fopen($datafile, 'w');
	fwrite($fh, $details);
	fclose($fh);
	$data = $datafile;
	if (!$filetype)
		$filetype = $d->getFileType();

} elseif (!empty($uploaded_data) && $uploaded_data['name']) {
	if (!is_uploaded_file($uploaded_data['tmp_name'])) {
		$return_msg = sprintf(_('Invalid file attack attempt %s.'), $uploaded_data['name']);
		session_redirect($urlparam.'&error_msg='.urlencode($return_msg));
	}
	$data = $uploaded_data['tmp_name'];
	$filename = $uploaded_data['name'];
	if (function_exists('finfo_open')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$filetype = finfo_file($finfo, $uploaded_data['tmp_name']);
	} else {
		$filetype = $uploaded_data['type'];
	}
} elseif ($file_url) {
	$data = '';
	$filename = $file_url;
	$filetype = 'URL';
} else {
	$filename = $d->getFileName();
	$filetype = $d->getFileType();
}

if (!$d->update($filename, $filetype, $data, $doc_group, $title, $description, $stateid))
	session_redirect($urlparam.'&error_msg='.urlencode($d->getErrorMessage()));

$return_msg = sprintf(_('Document %s updated successfully.'), $filename);
session_redirect($urlparam.'&feedback='.urlencode($return_msg));
