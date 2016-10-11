<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012,2016, Franck Villaume - TrivialDev
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
global $childgroup_id; // id of child group if any
global $feedback;
global $error_msg;
global $warning_msg;

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

$docid = getIntFromRequest('docid');
$title = getStringFromRequest('title');
$description = getStringFromRequest('description');
$vcomment = getStringFromRequest('vcomment');
$details = getStringFromRequest('details');
$file_url = getStringFromRequest('file_url');
$uploaded_data = getUploadedFile('uploaded_data');
$stateid = getIntFromRequest('stateid');
$filetype = getStringFromRequest('filetype');
$editor = getStringFromRequest('editor');
$current_version_radio = getIntFromRequest('doc_version_cv_radio');
$current_version = getIntFromRequest('current_version', 0);
$version = getIntFromRequest('edit_version', 0);
$new_version = getIntFromRequest('new_version', 0);

if (!$docid) {
	$warning_msg = _('No document found to update');
	session_redirect($urlparam);
}

$d = document_get_object($docid, $g->getID());
if ($d->isError()) {
	$error_msg = $d->getErrorMessage();
	session_redirect($urlparam);
}

$sanitizer = new TextSanitizer();
$details = $sanitizer->SanitizeHtml($details);
$data = '';

if ($version) {
	$dv = documentversion_get_object($version, $docid, $group_id);
	if (($editor) && ($dv->getFileData() != $details) && (!$uploaded_data['name'])) {
		$filename = $dv->getFileName();
		$datafile = tempnam('/tmp', 'docman');
		$fh = fopen($datafile, 'w');
		fwrite($fh, $details);
		fclose($fh);
		$data = $datafile;
		if (!$filetype)
			$filetype = $dv->getFileType();

	} elseif (!empty($uploaded_data) && $uploaded_data['name']) {
		if (!is_uploaded_file($uploaded_data['tmp_name'])) {
			$error_msg = sprintf(_('Invalid file attack attempt %s.'), $uploaded_data['name']);
			session_redirect($urlparam);
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
		$filename = $file_url;
		$filetype = 'URL';
	} else {
		$filename = $dv->getFileName();
		$filetype = $dv->getFileType();
	}
} elseif ($new_version) {
	if ($editor && $details && $name) {
		$filename = $name;
		$datafile = tempnam('/tmp', 'docman');
		$fh = fopen($datafile, 'w');
		fwrite($fh, $details);
		fclose($fh);
		$data = $datafile;
		if (!$filetype)
			$filetype = 'text/html';

	} elseif (!empty($uploaded_data) && $uploaded_data['name']) {
		if (!is_uploaded_file($uploaded_data['tmp_name'])) {
			$error_msg = sprintf(_('Invalid file attack attempt %s.'), $uploaded_data['name']);
			session_redirect($urlparam);
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
		$filename = $file_url;
		$filetype = 'URL';
	}
} elseif (($d->getDocGroupID() != $doc_group) || ($d->getStateID() != $stateid)) {
	// we do the update based on the current version.
	$dv = documentversion_get_object($current_version_radio, $docid, $group_id);
	$filename = $dv->getFileName();
	$filetype = $dv->getFileType();
	$title = $dv->getTitle();
	$description = $dv->getDescription();
	$vcomment = $dv->getComment();
	$version = $current_version_radio;
	$current_version = 1;
} else {
	$warning_msg = _('No action to perform');
	session_redirect($urlparam);
}

if (!$d->update($filename, $filetype, $data, $doc_group, $title, $description, $stateid, $version, $current_version, $new_version, null, $vcomment)) {
	$error_msg = $d->getErrorMessage();
	session_redirect($urlparam);
}

$feedback = sprintf(_('Document [D%s] updated successfully.'), $d->getID());
session_redirect($urlparam);
