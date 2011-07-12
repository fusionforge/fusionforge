#! /usr/bin/php
<?php

/**
 * Data migration for the doc_manager - between pre6 and pre7
 *
 * Copyright 2002 (c) GFORGE LLC
 */

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

@ini_set('memory_limit', '128M');

// drop and recreate page cache
//
//SELECT * FROM doc_data WHERE filename is null;
db_begin();

$res=db_query_params ('SELECT * FROM doc_data WHERE filename IS NULL',
			array()) ;

if (!$res) {
	echo db_error();
	db_rollback();
	exit();
}
$rows=db_numrows($res);

for ($i=0; $i<$rows; $i++) {
	$res2 = db_query_params ('UPDATE doc_data SET data=$1,filename=$2,filetype=$3 WHERE docid=$4',
				 array (base64_encode( util_unconvert_htmlspecialchars( db_result($res,$i,'data'))),
					'file'.db_result($res,$i,'docid').'.html',
					'text/html',
					db_result($res,$i,'docid'))) ;
	if (!$res2 || db_affected_rows($res2) < 1) {
		echo 'DB ERROR'.db_error();
		db_rollback();
		exit;
	}
}

db_commit();
echo "SUCCESS\n";

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
