#! /usr/bin/php4 -f
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */
        
require ('squal_pre.php');

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}*/

#
#    aggregate the ratings
#

db_begin();

$rel = db_query("DELETE FROM survey_rating_aggregate;");
echo db_error();


$query = "INSERT INTO survey_rating_aggregate SELECT type,id,avg(response),count(*) FROM survey_rating_response GROUP BY type,id;";
$rel = db_query($query);

db_commit();

if (db_error()) {
	echo "Error: ".db_error();
}

?>
