#! /usr/bin/php4 -f
<?php

/**
 * Data migration for the doc_manager - between pre6 and pre7
 *
 * Copyright 2002 (c) GFORGE LLC
 *
 * @version   $Id$
 */

require ('squal_pre.php');

// drop and recreate page cache
//
//SELECT * FROM doc_data WHERE filename is null;
$res=db_query("SELECT * FROM doc_data WHERE filename IS NULL");
$rows=db_numrows($res);
echo $rows;

db_begin();
for ($i=0; $i<$rows; $i++) {

	$res2=db_query("UPDATE doc_data 
		SET 
		data='". base64_encode( util_unconvert_htmlspecialchars( db_result($res,$i,'data') )) ."',
		filename='file".db_result($res,$i,'docid').".html',
		filetype='text/html'
		WHERE docid='".db_result($res,$i,'docid')."'");
	if (!$res2 || db_affected_rows($res2) < 1) {
		echo 'DB ERROR'.db_error();
		db_rollback();
		exit;
	}

}

db_commit();

?>
