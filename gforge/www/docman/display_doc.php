<?php
/**
 * SourceForge Documentaion Manager
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/*
		by Quentin Cregan, SourceForge 06/2000
*/


require_once('doc_utils.php');
require_once('pre.php');

$arr=explode('/',$REQUEST_URI);
$docid=$arr[3];

if ($docid) {
	$query = "select data,doc_group,filetype,filename
		from doc_data 
		where docid = '$docid'";
		//and stateid = '1'";
		// stateid = 1 == active
	$result = db_query($query);
	if (db_numrows($result) < 1) {
		exit_error('Document unavailable','Document is not available.');
	} else {
		$row = db_fetch_array($result);
	}
	$g =& group_get_object($row['doc_group']);
	if (!$g || !is_object($g)) {
		exit_no_group();
	} elseif ($g->isError()) {
		exit_error('Error',$g->getErrorMessage());
	}

	Header ("Content-disposition: filename=$row[filename]");
	if (strstr($row['filetype'],'app')) {
		Header ("Content-type: application/binary");
	} else {
		Header ("Content-type: $row[filetype]");
	}
	echo base64_decode($row['data']);

} else {
	exit_error("No document data.","No document to display - invalid or inactive document number.");
}

?>
