#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

$time = time();

$last_week = ( $time - (86400 * 7) );
$this_week = ( $time );

$last_year  = date('Y',$last_week);
$last_month = date('m',$last_week);
$last_day   = date('d',$last_week);

$this_year  = date('Y',$this_week);
$this_month = date('m',$this_week);
$this_day   = date('d',$this_week);

$err .= "\nlast_week: $last_week $last_day ";
$err .= "\n\nthis_week: $this_week $this_day";

db_drop_table_if_exists ("project_counts_weekly_tmp");
$err .= "\n\nDROP TABLE project_counts_weekly_tmp" ;
db_drop_table_if_exists ("project_metric_weekly_tmp1");
$err .= "\n\nDROP TABLE project_metric_weekly_tmp1" ;

#create a table to put the aggregates in
$rel = db_query_params ('CREATE TABLE project_counts_weekly_tmp (group_id INT, type TEXT, count FLOAT(8))',
			array());
if (!$rel) {
	$err .= "\n\n***ERROR: \n\n".db_error();
}


#forum messages
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT forum_group_list.group_id,$1,3*log(1+count(forum.msg_id)::float) AS count
FROM forum,forum_group_list
WHERE forum.group_forum_id=forum_group_list.group_forum_id
AND post_date >= $2
AND post_date < $3
GROUP BY group_id',
			array('forum',
			      $last_week,
			      $this_week));

if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

#project manager tasks
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT project_group_list.group_id,$1,4*log(1+count(project_task.project_task_id)::float) AS count
FROM project_task,project_group_list
WHERE project_task.group_project_id=project_group_list.group_project_id
AND end_date >= $2
AND end_date < $3
GROUP BY group_id',
			array('tasks',
			      $last_week,
			      $this_week));

if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

#bugs
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT agl.group_id,$1,3*log(1+count(*)::float) AS count
FROM artifact_group_list agl,artifact a
WHERE a.open_date >= $2
AND a.open_date < $3
AND a.group_artifact_id=agl.group_artifact_id
AND agl.datatype=$4
GROUP BY agl.group_id',
			array('bugs',
			      $last_week,
			      $this_week,
			      '1'));

if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

#patches
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT agl.group_id,$1,10*log(1+count(*)::float) AS count
FROM artifact_group_list agl,artifact a
WHERE a.open_date >= $2
AND a.open_date < $3
AND a.group_artifact_id=agl.group_artifact_id
AND agl.datatype=$4
GROUP BY agl.group_id',
			array('patches',
			      $last_week,
			      $this_week,
			      '3'));
if (!$rel) {
	$err .= "\n\n***ERROR: \n\n".db_error();
}

#support
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT agl.group_id,$1,5*log(1+count(*)::float) AS count
FROM artifact_group_list agl,artifact a
WHERE a.open_date >= $2
AND a.open_date < $3
AND a.group_artifact_id=agl.group_artifact_id
AND agl.datatype=$4
GROUP BY agl.group_id',
			array('support',
			      $last_week,
			      $this_week,
			      '2'));

if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

#commits
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT group_id,$1,log(1+sum(commits)::float) AS count
FROM stats_cvs_group
WHERE ((month = $2 AND day >= $3) OR (month > $4))
AND commits > 0
GROUP BY group_id',
			array('cvs',
			      "$last_year$last_month",
			      $last_day,
			      "$last_year$last_month"));
if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

#file releases
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT frs_package.group_id,$1,5*log(1+count(*)::float)
FROM frs_release,frs_package
WHERE frs_package.package_id = frs_release.package_id
AND frs_release.release_date >= $2
AND frs_release.release_date < $3
GROUP BY frs_package.group_id',
			array('filereleases',
			      $last_week,
			      $this_week));

if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

db_begin();

#file downloads
$rel = db_query_params ('INSERT INTO project_counts_weekly_tmp
SELECT group_id,$1, .3*log(1+sum(downloads)::float) AS downloads
FROM frs_dlstats_group_vw
WHERE (month = $2 AND day >= $3) OR (month > $4)
GROUP BY group_id',
			array('downloads',
			      "$last_year$last_month",
			      $last_day,
			      "$last_year$last_month"));
if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

db_commit();

$rel = db_query_params ('CREATE SEQUENCE project_metric_weekly_seq',
			array ());
if (!$rel) {
	$err .= "\n\n***ERROR: \n\n".db_error();
}

#create a new table to insert the final records into
$rel = db_query_params ('CREATE TABLE project_metric_weekly_tmp1 (ranking SERIAL PRIMARY KEY, group_id INT NOT NULL, value FLOAT (10))',
			array ());
if (!$rel) {
	$err .= "\n\n***ERROR: \n\n".db_error();
}



#insert the rows into the table in order, adding a sequential rank #
$rel = db_query_params ('INSERT INTO project_metric_weekly_tmp1 (group_id,value)
SELECT project_counts_weekly_tmp.group_id,sum(project_counts_weekly_tmp.count) AS value
FROM project_counts_weekly_tmp
WHERE project_counts_weekly_tmp.count > 0
GROUP BY group_id ORDER BY value DESC',
			array ());
if (!$rel) {
	$err .= "\n\n***ERROR: \n\n".db_error();
}

#numrows in the set

$rel = db_query_params ('SELECT count(*) FROM project_metric_weekly_tmp1',
			array());
if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

$counts = db_result($rel,0,0);
$err .= "\n\nCounts: ".$counts;

db_begin();
#drop the old metrics table

$rel = db_query_params ('DELETE FROM project_weekly_metric',
			array());
if (!$rel) {
	$err .= "\n\n***ERROR: \n\n".db_error();
}
db_commit();

$rel = db_query_params ('INSERT INTO project_weekly_metric (ranking,percentile,group_id)
SELECT ranking,100-(100*((ranking::float-1)/$1)),group_id
FROM project_metric_weekly_tmp1
ORDER BY ranking ASC',
			array($counts));
if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}


//
//	Now archive the metric
//
db_query_params ('DELETE FROM stats_project_metric WHERE month=$1 AND day=$2',
		 array("$this_year$this_month",
		       "$this_day"));

$rel = db_query_params ('INSERT INTO stats_project_metric (month,day,group_id,ranking,percentile)
SELECT $1::int, $2::int,group_id,ranking,percentile
FROM project_weekly_metric',
			array("$this_year$this_month",
			      $this_day));
if (!$rel) {
	$err .= "\n\n***ERROR:\n\n".db_error();
}

$err .= db_error();

db_drop_sequence_if_exists ("project_metric_weekly_seq") ;
db_drop_table_if_exists ("project_counts_weekly_tmp");
db_drop_table_if_exists ("project_metric_weekly_tmp1");
db_drop_sequence_if_exists ("project_metric_week_ranking_seq");

cron_entry(8,$err);

?>
