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

//
//	PG 7.1 and earlier
//
//$res = db_query("VACUUM ANALYZE;");
//
//	PG 7.2 and 7.3
//
$res = db_query("VACUUM FULL;");


if (!$res) {
	echo "Error on DB1: " . db_error();
}

?>
