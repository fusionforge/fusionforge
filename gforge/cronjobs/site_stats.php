#!/usr/local/bin/php
<?php

require ('squal_pre.php');

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
            exit_permission_denied();
}*/


db_begin();

if (!$day) {
    $day=date('Ymd',(time()-86400));
}

$yesterday_formatted=$day;

echo $yesterday_formatted;

## logo showings by day
$sql = "DELETE FROM stats_agg_logo_by_day WHERE day='$yesterday_formatted'";
$rel = db_query($sql);
$sql = "INSERT INTO stats_agg_logo_by_day SELECT day, count(*) FROM activity_log_old WHERE type=1 AND day='$yesterday_formatted' GROUP BY day";
$rel = db_query($sql);

## logo showings by group
$sql = "DELETE FROM stats_agg_logo_by_group WHERE day='$yesterday_formatted'";
$rel = db_query($sql);
$sql = "INSERT INTO stats_agg_logo_by_group SELECT day,group_id,count(*) FROM activity_log_old WHERE type=1 AND day='$yesterday_formatted' GROUP BY day,group_id";
$rel = db_query($sql);

## site showings by day
$sql = "DELETE FROM stats_agg_site_by_day WHERE day='$yesterday_formatted'";
$rel = db_query($sql);
$sql = "INSERT INTO stats_agg_site_by_day SELECT day,COUNT(*) FROM activity_log_old WHERE type=0 AND day='$yesterday_formatted' GROUP BY day";
$rel = db_query($sql);

## site showings by group
$sql = "DELETE FROM stats_agg_site_by_group WHERE day='$yesterday_formatted'";
$rel = db_query($sql);
$sql = "INSERT INTO stats_agg_site_by_group SELECT day,group_id,COUNT(*) FROM activity_log_old WHERE type=0 AND day='$yesterday_formatted' GROUP BY day,group_id";
$rel = db_query($sql);

## page views by day
$sql = "DELETE FROM stats_agg_pages_by_day WHERE day='$yesterday_formatted'";
$rel = db_query($sql);
$sql = "INSERT INTO stats_agg_pages_by_day SELECT day, count(*) FROM activity_log_old WHERE type=0 AND day='$yesterday_formatted' GROUP BY day";
$rel = db_query($sql);

db_commit();
echo "Done: ".db_error();

?>
