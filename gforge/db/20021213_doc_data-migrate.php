#! /usr/bin/php5 -f
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
db_begin();

$res=db_query("SELECT * FROM doc_data WHERE filename IS NULL");
if (!$res) {
	echo db_error();
	db_rollback();
	exit();
}	 
$rows=db_numrows($res);

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
echo "SUCCESS\n";
?>
