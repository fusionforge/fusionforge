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

db_begin();

#one hour ago for projects
$then=(time()-3600);
db_query("DELETE FROM groups WHERE status='I' and register_time < '$then'");
echo db_error();

#one week ago for users
$then=(time()-604800);
db_query("DELETE FROM user_group WHERE EXISTS (SELECT user_id FROM users ".
"WHERE status='P' and add_date < '$then' AND users.user_id=user_group.user_id)");
echo db_error();

db_query("DELETE FROM users WHERE status='P' and add_date < '$then'");
echo db_error();

#30 days ago for sessions
$then=(time()-(30*60*60*24));
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
if (db_error()) {
	echo "Error: ".db_error();
}

?>
