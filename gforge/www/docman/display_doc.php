<?php
/**
  *
  * SourceForge Documentaion Manager
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

/*
        by Quentin Cregan, SourceForge 06/2000
*/


require_once('doc_utils.php');
require_once('pre.php');

if ($docid) {
	$query = "select * "
		."from doc_data "
		."where docid = $docid "
		."and stateid = '1'";
		// stateid = 1 == active
	$result = db_query($query);
	
	if (db_numrows($result) < 1) {
		exit_error('Document unavailable','Document is not available.');
	} else {
		$row = db_fetch_array($result);
	}
	
	docman_header($row['title'],$row['title'],'docman_display_doc','',group_getname($group_id));

	// data in DB stored in htmlspecialchars()-encoded form
    	print util_unconvert_htmlspecialchars($row['data']);
	docman_footer($params);

} else {
	exit_error("No document data.","No document to display - invalid or inactive document number.");
}
