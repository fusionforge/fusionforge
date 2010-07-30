<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume
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

require_once ('include/TextSanitizer.class.php'); // to make the HTML input by the user safe to store
require_once ('docman/Document.class.php');

$doc_group = getIntFromRequest('doc_group');

$docid = getIntFromRequest('docid');
$title = getStringFromRequest('title');
$description = getStringFromRequest('description');
$data = getStringFromRequest('data');
$file_url = getStringFromRequest('file_url');
//$ftp_filename = getStringFromRequest('ftp_filename');
$uploaded_data = getUploadedFile('uploaded_data');
$stateid = getIntFromRequest('stateid');
$filetype = getStringFromRequest('filetype');
$editor = getStringFromRequest('editor');

$d= new Document($g,$docid,false,$sys_engine_path);
if ($d->isError())
	exit_error(_('Error'),$d->getErrorMessage());

$sanitizer = new TextSanitizer();
$data = $sanitizer->SanitizeHtml($data);
if (($editor) && ($d->getFileData()!=$data) && (!$uploaded_data['name'])) {
	$filename = $d->getFileName();
	if (!$filetype)
		$filetype = $d->getFileType();

} elseif ($uploaded_data['name']) {
	if (!is_uploaded_file($uploaded_data['tmp_name']))
		exit_error(_('Error'),sprintf(_('Invalid file attack attempt %1$s'), $uploaded_data['name']));

	$data = fread(fopen($uploaded_data['tmp_name'], 'r'), $uploaded_data['size']);
	$filename=$uploaded_data['name'];
	$filetype=$uploaded_data['type'];
} elseif ($file_url) {
	$data = '';
	$filename=$file_url;
	$filetype='URL';
	/*
} elseif (forge_get_config('use_ftp_uploads') && $ftp_filename!=100) { //100==None
	$filename=$upload_dir.'/'.$ftp_filename;
	$data = fread(fopen($filename, 'r'), filesize($filename));
	$filetype=$uploaded_data_type;
} elseif (forge_get_config('use_manual_uploads') && $uploaded_filename!=100 && util_is_valid_filename($uploaded_filename)) { //100==None
	$incoming = forge_get_config('groupdir_prefix')."/".$g->getUnixName()."/incoming" ;
	$filename = $incoming.'/'.$uploaded_filename;
	$data = addslashes(fread(fopen($filename, 'r'), filesize($filename)));
	$finfo = finfo_open (FILEINFO_MIME_TYPE) ;
	$filetype = finfo_file($finfo, $filename) ;
	finfo_close ($finfo) ;
*/
} else {
	$filename=$d->getFileName();
	$filetype=$d->getFileType();
}
if (!$d->update($filename,$filetype,$data,$doc_group,$title,$description,$stateid))
	exit_error('Error',$d->getErrorMessage());

$feedback = _('Document Updated successfully');
Header('Location: '.util_make_url('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$doc_group.'&feedback='.urlencode($feedback)));
exit;

?>

