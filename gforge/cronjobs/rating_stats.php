#! /usr/bin/php4 -f
<?php
        
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

echo "Done: ".db_error();

?>
