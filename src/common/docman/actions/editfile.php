<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume - Capgemini
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
global $dirid; //id of doc_group
global $group_id; // id of group

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg = _('Document Action Denied.');
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&warning_msg='.urlencode($return_msg));
} else {
	$doc_group = getIntFromRequest('doc_group');
	$docid = getIntFromRequest('docid');
	$title = getStringFromRequest('title');
	$description = getStringFromRequest('description');
	$data = getStringFromRequest('details'.$docid);
	$file_url = getStringFromRequest('file_url');
	$uploaded_data = getUploadedFile('uploaded_data');
	$stateid = getIntFromRequest('stateid');
	$filetype = getStringFromRequest('filetype');
	$editor = getStringFromRequest('editor');
	$fromview = getStringFromRequest('fromview');
	if ( 'admin' == $fromview ) {
		$urlparam = '&view='.$fromview;
	} else {
		$urlparam = '&view=listfile&dirid='.$doc_group;
	}

	$d= new Document($g,$docid,false,$gfcommon.'docman/engine/');
	if ($d->isError())
		session_redirect('/docman/?group_id='.$group_id.$urlparam.'&error_msg='.urlencode($d->getErrorMessage()));

	$sanitizer = new TextSanitizer();
	$data = $sanitizer->SanitizeHtml($data);
	if (($editor) && ($d->getFileData()!=$data) && (!$uploaded_data['name'])) {
		$filename = $d->getFileName();
		if (!$filetype)
			$filetype = $d->getFileType();

	} elseif (!empty($uploaded_data) && $uploaded_data['name']) {
		if (!is_uploaded_file($uploaded_data['tmp_name'])) {
			$return_msg = sprintf(_('Invalid file attack attempt %1$s.'), $uploaded_data['name']);
		session_redirect('/docman/?group_id='.$group_id.$urlparam.'&error_msg='.urlencode($return_msg));
		}
		$data = fread(fopen($uploaded_data['tmp_name'], 'r'), $uploaded_data['size']);
		$filename = $uploaded_data['name'];
		$filetype = $uploaded_data['type'];
	} elseif ($file_url) {
		$data = '';
		$filename = $file_url;
		$filetype = 'URL';
	} else {
		$filename = $d->getFileName();
		$filetype = $d->getFileType();
	}
	if (!$d->update($filename, $filetype, $data, $doc_group, $title, $description, $stateid))
		session_redirect('/docman/?group_id='.$group_id.$urlparam.'&error_msg='.urlencode($d->getErrorMessage()));

	$return_msg = _('Document Updated successfully.');
	session_redirect('/docman/?group_id='.$group_id.$urlparam.'&feedback='.urlencode($return_msg));
}
?>
