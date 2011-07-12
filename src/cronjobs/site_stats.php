#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

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
$err .= "\n\nBeginning stats_agg_logo_by_day ".date('Ymd H:i:s',time());
$rel = db_query_params ('DELETE FROM stats_agg_logo_by_day WHERE day=$1',
			array ($yesterday_formatted));
$err .= db_error();
$rel = db_query_params ('INSERT INTO stats_agg_logo_by_day
	SELECT day, count(*)
	FROM activity_log WHERE type=0 AND day=$1 GROUP BY day',
			array ($yesterday_formatted));
$err .= db_error();


//
//	logo showings by group
//	new table format 2001-april
//
$err .= "\n\nBeginning stats_agg_logo_by_group ".date('Ymd H:i:s',time());
$rel = db_query_params ('DELETE FROM stats_agg_logo_by_group WHERE month=$1 AND day=$2',
			array ("$year$month",
				$day));
$err .= db_error();
$rel = db_query_params ('INSERT INTO stats_agg_logo_by_group SELECT $1::int AS month, $2::int AS newday,group_id,count(*) AS count
	FROM activity_log WHERE type=0 AND day=$3 GROUP BY month,newday,group_id',
			array ("$year$month",
			       $day,
			       $yesterday_formatted)) ;
$err .= db_error();


//
//	site showings by group
//	new table format 2001-april
//
$err .= "\n\nBeginning stats_agg_site_by_group ".date('Ymd H:i:s',time());
$rel = db_query_params ('DELETE FROM stats_agg_site_by_group WHERE month=$1 AND day=$2',
			array ("$year$month",
				$day));
$err .= db_error();
$rel = db_query_params ('INSERT INTO stats_agg_site_by_group SELECT $1::int AS month, $2::int AS newday,group_id,COUNT(*) AS count FROM activity_log WHERE type=0 AND day=$3 GROUP BY month,newday,group_id',
			array ("$year$month",
			       $day,
			       $yesterday_formatted)) ;
$err .= db_error();


//
//	page views by day
//
$err .= "\n\nBeginning stats_site_pages_by_day ".date('Ymd H:i:s',time());
$rel = db_query_params ('DELETE FROM stats_site_pages_by_day WHERE month=$1 AND day=$2',
			array ("$year$month",
				$day));
$err .= db_error();
$rel = db_query_params ('INSERT INTO stats_site_pages_by_day (month,day,site_page_views) SELECT $1::int AS month, $2::int AS newday, count(*) AS count FROM activity_log WHERE type=0 AND day=$3 GROUP BY month,newday',
			array ("$year$month",
			       $day,
			       $yesterday_formatted)) ;
$err .= db_error();


//
//	insert the number of developers per project into history table
//
$err .= "\n\nBeginning stats_project_developers ".date('Ymd H:i:s',time());
$rel = db_query_params ('DELETE FROM stats_project_developers WHERE month=$1 AND day=$2',
			array ("$year$month",
				$day));
$err .= db_error();
if (USE_PFO_RBAC) {
$rel = db_query_params ('INSERT INTO stats_project_developers (month,day,group_id,developers)
SELECT  $1::int AS month, $2::int, pfo_role.home_group_id AS group_id, COUNT(DISTINCT(pfo_user_role.user_id)) AS developers FROM pfo_user_role, pfo_role WHERE pfo_user_role.role_id = pfo_role.role_id AND pfo_role.home_group_id IS NOT NULL GROUP BY pfo_role.home_group_id',
			array ("$year$month",
			       $day));
} else {
$rel = db_query_params ('INSERT INTO stats_project_developers (month,day,group_id,developers) SELECT $1::int AS month,$2::int AS day,group_id,count(*) AS count FROM user_group GROUP BY month,day,group_id',
			array ("$year$month",
			       $day));
}
$err .= db_error();

db_commit();

$err .= db_error();

//
//	populate stats_site table
//
$err .= "\n\nBeginning stats_site ".date('Ymd H:i:s',time());
include('cronjobs/stats_site.inc');
site_stats_day($year,$month,$day);

//
//	populate stats_project table
//
$err .= "\n\nBeginning stats_project ".date('Ymd H:i:s',time());
include('cronjobs/stats_projects.inc');
project_stats_day($year,$month,$day);

cron_entry(11,$err);

?>
