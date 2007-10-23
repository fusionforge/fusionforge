#! /usr/bin/php5 -f
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$err='';

$time = time();

$last_week= ( $time - (86400 * 7) );  
$this_week = ( $time );

$last_year=date('Y',$last_week);
$last_month=date('m',$last_week);
$last_day=date('d',$last_week);

$this_year=date('Y',$this_week);
$this_month=date('m',$this_week);
$this_day=date('d',$this_week);

$err .= "\nlast_week: $last_week $last_day ";
$err .= "\n\nthis_week: $this_week $this_day";

db_drop_table_if_exists ("project_counts_weekly_tmp");
$err .= "\n\nDROP TABLE project_counts_weekly_tmp" ;
db_drop_table_if_exists ("project_metric_weekly_tmp1");
$err .= "\n\nDROP TABLE project_metric_weekly_tmp1" ;

#create a table to put the aggregates in
$sql="CREATE TABLE project_counts_weekly_tmp (
group_id int,
type text,
count float(8))";
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}


#forum messages
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT forum_group_list.group_id,'forum',log(3 * count(forum.msg_id)) AS count ";
} else {
	$sql.="SELECT forum_group_list.group_id,'forum',log(3 * count(forum.msg_id)::float) AS count ";
}
$sql.="
FROM forum,forum_group_list 
WHERE forum.group_forum_id=forum_group_list.group_forum_id 
AND post_date > '$last_week' 
AND post_date < '$this_week'
GROUP BY group_id";
$rel = db_query($sql);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#project manager tasks
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT project_group_list.group_id,'tasks',log(4 * count(project_task.project_task_id)) AS count ";
} else {
	$sql.="SELECT project_group_list.group_id,'tasks',log(4 * count(project_task.project_task_id)::float) AS count ";
}
$sql.="
FROM project_task,project_group_list 
WHERE project_task.group_project_id=project_group_list.group_project_id 
AND end_date > '$last_week'
AND end_date < '$this_week' 
GROUP BY group_id";
//$err .= "\n\n".$sql;
$rel = db_query($sql);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#bugs
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT agl.group_id,'bugs',log(3 * count(*)) AS count ";
} else {
	$sql.="SELECT agl.group_id,'bugs',log(3 * count(*)::float) AS count ";
}
$sql.="
FROM artifact_group_list agl,artifact a
WHERE a.open_date > '$last_week'
AND a.open_date < '$this_week'
AND a.group_artifact_id=agl.group_artifact_id 
AND agl.datatype='1'
GROUP BY agl.group_id";

#$err .= "\n\n".$sql;

$rel = db_query($sql);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#patches
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT agl.group_id,'patches',log(10 * count(*)) AS count ";
} else {
	$sql.="SELECT agl.group_id,'patches',log(10 * count(*)::float) AS count ";
}
$sql.="
FROM artifact_group_list agl,artifact a
WHERE a.open_date > '$last_week'
AND a.open_date < '$this_week'
AND a.group_artifact_id=agl.group_artifact_id 
AND agl.datatype='3'
GROUP BY agl.group_id";

#$err .= "\n\n".$sql;

$rel = db_query($sql);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#support
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT agl.group_id,'support',log(5 * count(*)) AS count ";
} else {
	$sql.="SELECT agl.group_id,'support',log(5 * count(*)::float) AS count ";
}
$sql.="
FROM artifact_group_list agl,artifact a
WHERE a.open_date > '$last_week'
AND a.open_date < '$this_week'
AND a.group_artifact_id=agl.group_artifact_id 
AND agl.datatype='2'
GROUP BY agl.group_id";

#$err .= "\n\n".$sql;

$rel = db_query($sql);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#cvs commits
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT group_id,'cvs',log(sum(commits)) AS count ";
} else {
	$sql.="SELECT group_id,'cvs',log(sum(commits)::float) AS count ";
}
$sql.="
FROM stats_cvs_group 
WHERE ((month = '$last_year$last_month' AND day >= '$last_day') OR (month > '$last_year$last_month'))
AND commits > 0
GROUP BY group_id";
//$err .= "\n\n".$sql;
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#developers
#$sql="INSERT INTO project_counts_weekly_tmp 
#SELECT group_id,'developers',log(5.0*count(*)) AS count FROM user_group GROUP BY group_id";
#$err .= "\n\n".$sql;
#$rel = db_query($sql);

#file releases
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="select frs_package.group_id,'filereleases',log(5 * count(*)) ";
} else {
	$sql.="select frs_package.group_id,'filereleases',log(5 * count(*)::float) ";
}
$sql.="
FROM frs_release,frs_package
WHERE 
	frs_package.package_id = frs_release.package_id 
	AND frs_release.release_date > '$last_week'
	AND frs_release.release_date < '$this_week'
GROUP BY frs_package.group_id";
$rel = db_query($sql);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

db_begin();

#file downloads
$sql="INSERT INTO project_counts_weekly_tmp ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT group_id,'downloads', log(.3 * sum(downloads)) AS downloads ";
} else {
	$sql.="SELECT group_id,'downloads', log(.3 * sum(downloads)::float) AS downloads ";
}
$sql.="
FROM frs_dlstats_group_vw
WHERE (month = '$last_year$last_month' AND day >= '$last_day') OR (month > '$last_year$last_month')
GROUP BY group_id;";
$rel = db_query($sql,-1,0,SYS_DB_STATS);

if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

db_commit();

if ($sys_database_type != 'mysql') {
	$sql = "CREATE SEQUENCE project_metric_weekly_seq" ;
	//$err .= "\n\n".$sql;
	$rel = db_query($sql);
	if (!$rel) {
		$err .= "\n\n***ERROR: $sql\n\n".db_error();
	}
}

#create a new table to insert the final records into
$sql="CREATE TABLE project_metric_weekly_tmp1 (
ranking serial primary key,
group_id int not null,
value float (10))";
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}



#insert the rows into the table in order, adding a sequential rank #
$sql="INSERT INTO project_metric_weekly_tmp1 (group_id,value) 
SELECT project_counts_weekly_tmp.group_id,sum(project_counts_weekly_tmp.count) AS value 
FROM project_counts_weekly_tmp
WHERE
project_counts_weekly_tmp.count > 0
GROUP BY group_id ORDER BY value DESC";
//$err .= "\n\n".$sql;
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

#numrows in the set
$sql="SELECT count(*) FROM project_metric_weekly_tmp1";
//$err .= "\n\n".$sql;
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

$counts = db_result($rel,0,0);
$err .= "\n\nCounts: ".$counts;

db_begin();
#drop the old metrics table
$sql="DELETE FROM project_weekly_metric";
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}
db_commit();

$sql="INSERT INTO project_weekly_metric (ranking,percentile,group_id) ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT ranking,100-(100*((ranking-1)/$counts)),group_id ";
} else {
	$sql.="SELECT ranking,100-(100*((ranking::float-1)/$counts)),group_id ";
}
$sql.="
FROM project_metric_weekly_tmp1
ORDER BY ranking ASC";
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}


//
//	Now archive the metric
//
db_query("DELETE FROM stats_project_metric WHERE month='$this_year$this_month' AND day='$this_day'");

$sql="INSERT INTO stats_project_metric (month,day,group_id,ranking,percentile) ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT '$this_year$this_month', '$this_day',group_id,ranking,percentile ";
} else {
	$sql.="SELECT '$this_year$this_month'::int, '$this_day'::int,group_id,ranking,percentile ";
}
$sql.="
	FROM project_weekly_metric";
$rel = db_query($sql);
if (!$rel) {
	$err .= "\n\n***ERROR: $sql\n\n".db_error();
}

$err .= db_error();

db_drop_sequence_if_exists ("project_metric_weekly_seq") ;
db_drop_table_if_exists ("project_counts_weekly_tmp");
db_drop_table_if_exists ("project_metric_weekly_tmp1");
db_drop_sequence_if_exists ("project_metric_week_ranking_seq");

cron_entry(8,$err);

?>
