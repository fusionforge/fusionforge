#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}*/

db_begin();

#one hour ago for projects
$then=(time()-3600);
db_query("DELETE FROM groups WHERE status='I' and register_time < '$then'");
echo db_error();

#one week ago for users
$then=(time()-604800);
db_query("DELETE FROM users WHERE status='P' and add_date < '$then'");
echo db_error();

#one week ago for sessions
$then=(time()-604800);
db_query("DELETE FROM session WHERE time < '$then'");
echo db_error();

#one month ago for preferences
$then=(time()-604800*4);
db_query("DELETE FROM user_preferences WHERE set_date < '$then'");
echo db_error();

#3 weeks ago for jobs
$then=(time()-604800*3);
db_query("UPDATE people_job SET status_id = '3' where date < '$then'");
echo db_error();

db_commit();
echo "Done: ".db_error();

?>
