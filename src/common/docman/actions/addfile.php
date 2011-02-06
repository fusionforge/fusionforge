<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Roland Mas
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; //group object
global $group_id; // id of group

if (!forge_check_perm('docman', $group_id, 'submit')) {
	$return_msg = _('Document Manager Action Denied.');
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($return_msg));
}

$doc_group = getIntFromRequest('doc_group');
$title = getStringFromRequest('title');
$description = getStringFromRequest('description');
$file_url = getStringFromRequest('file_url');
$uploaded_data = getUploadedFile('uploaded_data');
$manual_path = getStringFromRequest('manual_path');
$type = getStringFromRequest('type');
$name = getStringFromRequest('name');

if (!$doc_group || $doc_group == 100) {
	//cannot add a doc unless an appropriate group is provided
	$return_msg = _('No valid Directory was selected.');
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
}
	
if (!$title || !$description || (!$uploaded_data && !$file_url && (!$editor && !$name))) {
	$missing_params = array();
	if (!$title)
		$missing_params[] = 'title';

	if (!$description)
		$missing_params[] = 'description';

	if (forge_get_config('use_ssl'))
		$url = "https://";
	else
		$url = "http://";

	$url .= forge_get_config('web_host');

	exit_missing_param(substr($_SERVER['HTTP_REFERER'], strlen($url)),$missing_params,'docman');
}

if (empty($gfcommon)) {
	$engine_dir = '../../common';
} else {
	$engine_dir = $gfcommon;
}

$d = new Document($g, false, false, $engine_dir.'/docman/engine/');

if (!$d || !is_object($d)) {
	$return_msg= _('Error getting blank document.');
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
} elseif ($d->isError()) {
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($d->getErrorMessage()));
}

switch ($type) {
	case 'editor' : {
		$data = getStringFromRequest('details');
		$uploaded_data_name = $name;
		$sanitizer = new TextSanitizer();
		$data = $sanitizer->SanitizeHtml($data);
		if (strlen($data)<1) {
			$return_msg = _('Error getting blank document.');
			session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
		}
		$uploaded_data_type = 'text/html';
		break;
	}
	case 'pasteurl' : {
		$data = '';
		$uploaded_data_name = $file_url;
		$uploaded_data_type = 'URL';
		break;
	}
	case 'httpupload' : {
		if (!is_uploaded_file($uploaded_data['tmp_name'])) {
		$return_msg = _('Invalid file name.');
			session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
		}
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$uploaded_data_type = finfo_file($finfo, $uploaded_data['tmp_name']);
		} else {
			$uploaded_data_type = $uploaded_data['type'];
		}
		$data = fread(fopen($uploaded_data['tmp_name'], 'r'), $uploaded_data['size']);
		$file_url = '';
		$uploaded_data_name = $uploaded_data['name'];
		break;
	}
	case 'manualupload' : {
		if (!forge_get_config('use_manual_uploads')) {
			$return_msg = _('Manual uploads disabled.');
			session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
		}
		
		$incoming = forge_get_config('groupdir_prefix')."/".$g->getUnixName()."/incoming";
		$filename = $incoming.'/'.$manual_path;

		if (!util_is_valid_filename($manual_path) || !is_file($filename)) {
		$return_msg = _('Invalid file name.');
			session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
		}

		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$uploaded_data_type = finfo_file($finfo, $filename);
		} else {
			$uploaded_data_type = 'application/binary';
		}
		$stat = stat($filename);
		$data = fread(fopen($filename, 'r'), $stat['size']);
		$file_url = '';
		$uploaded_data_name = $manual_path;
		break;
	}
	default: {
		$return_msg = _('Unknown type submission.');
		session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($return_msg));
	}
}

if (!$d->create($uploaded_data_name, $uploaded_data_type, $data, $doc_group, $title, $description)) {
	if (forge_check_perm('docman', $group_id, 'approve')) {
		session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$doc_group.'&error_msg='.urlencode($d->getErrorMessage()));
	} else {
		session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode($d->getErrorMessage()));
	}
} else {
	if ($type == 'editor') {
		//release the cookie for the document contents (should expire at the end of the session anyway)
		setcookie("gforgecurrentdocdata", "", time() - 3600);
	}
	if (forge_check_perm('docman', $group_id, 'approve')) {
		$return_msg = sprintf(_('Document %s submitted successfully.'),$d->getFilename());
		session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$doc_group.'&feedback='.urlencode($return_msg));
	} else {
		$return_msg = sprintf(_('Document %s submitted successfully : pending state (need validation).'),$d->getFilename());
		session_redirect('/docman/?group_id='.$group_id.'&feedback='.urlencode($return_msg));
	}
}
?>
