<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

$no_gz_buffer=true;

require_once('pre.php');
require_once('include/doc_utils.php');
require_once('common/docman/Document.class');

$arr=explode('/',$REQUEST_URI);
$group_id=$arr[3];
$docid=$arr[4];

if ($docid) {

	$g =& group_get_object($group_id);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error('Error',$g->getErrorMessage());
	}

	$d = new Document($g,$docid);
	if (!$d || !is_object($d)) {
		exit_error('Document unavailable','Document is not available.');
	} elseif ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}

	Header ("Content-disposition: filename=".$d->getFileName());

	if (strstr($d->getFileType(),'app')) {
		Header ("Content-type: application/binary");
	} else {
		Header ("Content-type: ".$d->getFileType());
	}

	echo $d->getFileData();

} else {
	exit_error("No document data.","No document to display - invalid or inactive document number.");
}

?>