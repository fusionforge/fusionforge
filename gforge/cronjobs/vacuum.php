#! /usr/bin/php4 -f
<?php

/**
  *
  * nightly VACUUM job
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require ('squal_pre.php');

// drop and recreate page cache
//
db_query("DROP TABLE cache_store");
db_query("CREATE TABLE cache_store (
name varchar(255) primary key,
data text,
indate int not null default 0
);");

// VACUUM db1
//
$res = db_query("VACUUM ANALYZE;");

if (!$res) {
	echo "Error on DB1: " . db_error();
}

// VACUUM db2
//
$res = db_query("VACUUM ANALYZE;", -1, 0, SYS_DB_STATS);

if (!$res) {
	echo "Error on DB2: " . db_error();
}

?>
