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

$how_far_back=(time() - 86400);

$yesterday_formatted=date('Ymd',$how_far_back);

$year=date('Y', $how_far_back);
$month=date('m', $how_far_back);
$day=date('d', $how_far_back);
$day_begin=mktime(0,0,0,$month,$day,$year);
$day_end=($day_begin + 86400);


//
//	logo showings by day
//
echo "\n\nBeginning stats_agg_logo_by_day ".date('Ymd H:i:s',time());
$sql = "DELETE FROM stats_agg_logo_by_day WHERE day='$yesterday_formatted'";
$rel = db_query($sql);
echo db_error();
$sql = "INSERT INTO stats_agg_logo_by_day 
	SELECT day, count(*) 
	FROM activity_log_old WHERE type=1 AND day='$yesterday_formatted' GROUP BY day";
$rel = db_query($sql);
echo db_error();


//
//	logo showings by group
//	new table format 2001-april
//
echo "\n\nBeginning stats_agg_logo_by_group ".date('Ymd H:i:s',time());
$sql = "DELETE FROM stats_agg_logo_by_group WHERE month='$year$month' AND day='$day'";
$rel = db_query($sql);
echo db_error();
$sql = "INSERT INTO stats_agg_logo_by_group 
	SELECT '$year$month'::int AS month, '$day'::int AS newday,group_id,count(*) 
	FROM activity_log_old WHERE type=1 AND day='$yesterday_formatted' GROUP BY month,newday,group_id";
$rel = db_query($sql);
echo db_error();


//
//	site showings by group
//	new table format 2001-april
//
echo "\n\nBeginning stats_agg_site_by_group ".date('Ymd H:i:s',time());
$sql = "DELETE FROM stats_agg_site_by_group WHERE month='$year$month' AND day='$day'";
$rel = db_query($sql);
echo db_error();
$sql = "INSERT INTO stats_agg_site_by_group 
	SELECT '$year$month'::int AS month, '$day'::int AS newday,group_id,COUNT(*) 
	FROM activity_log_old WHERE type=0 AND day='$yesterday_formatted' GROUP BY month,newday,group_id";
$rel = db_query($sql);
echo db_error();


//
//	page views by day
//
echo "\n\nBeginning stats_site_pages_by_day ".date('Ymd H:i:s',time());
$sql = "DELETE FROM stats_site_pages_by_day WHERE month='$year$month' AND day='$day'";
$rel = db_query($sql);
echo db_error();
$sql = "INSERT INTO stats_site_pages_by_day (month,day,site_page_views)
	SELECT '$year$month'::int AS month, '$day'::int AS newday, count(*) 
	FROM activity_log_old WHERE type=0 AND day='$yesterday_formatted' GROUP BY month,newday";
$rel = db_query($sql);
echo db_error();


//
//	insert the number of developers per project into history table
//
echo "\n\nBeginning stats_project_developers ".date('Ymd H:i:s',time());
$rel=db_query("DELETE FROM stats_project_developers WHERE month='$year$month' AND day='$day'");
echo db_error();
$res=db_query("INSERT INTO stats_project_developers (month,day,group_id,developers) 
	SELECT '$year$month'::int AS month,'$day'::int AS day,group_id,count(*) 
	FROM user_group 
	GROUP BY month,day,group_id");
echo db_error();

db_commit();

echo "Error: ".db_error();

//
//	populate stats_site table
//
echo "\n\nBeginning stats_site ".date('Ymd H:i:s',time());
include('cronjobs/stats_site.inc');
site_stats_day($year,$month,$day);

//
//	populate stats_project table
//
echo "\n\nBeginning stats_project ".date('Ymd H:i:s',time());
include('cronjobs/stats_projects.inc');
project_stats_day($year,$month,$day);

?>
