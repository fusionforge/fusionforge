#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}*/

$today_formatted=date('Ymd',time());

db_begin();

   ## shuffle the activity log tables - we keep 3 days of data
$sql = "DROP TABLE activity_log_old_old";
$rel = db_query($sql);

$sql = "ALTER TABLE activity_log_old RENAME TO activity_log_old_old";
$rel = db_query($sql);

$sql = "ALTER TABLE activity_log RENAME TO activity_log_old";
$rel = db_query($sql);

$sql = "CREATE TABLE activity_log (
	day int DEFAULT '0' NOT NULL,
	hour int DEFAULT '0' NOT NULL,
	group_id int DEFAULT '0' NOT NULL,
	browser varchar(8) DEFAULT 'OTHER' NOT NULL,
	ver float(8) DEFAULT '0.00' NOT NULL,
	platform varchar(8) DEFAULT 'OTHER' NOT NULL,
	time int DEFAULT '0' NOT NULL,
	page text,
	type int DEFAULT '0' NOT NULL
)";
$rel = db_query($sql);

## Cleanup any spillover, so that the activity log always contains exactly 24 hours worth of data.
$sql = "INSERT INTO activity_log SELECT * FROM activity_log_old WHERE day='$today_formatted'";
$rel = db_query($sql);

$sql = "DELETE FROM activity_log_old WHERE day='$today_formatted'";
$rel = db_query($sql);

db_commit();
echo "Done: ".db_error();

?>
