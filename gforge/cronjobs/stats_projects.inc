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

//
//
//	POPULATES stats_project table - included in site_stats.php
//
//

function project_stats_day($year,$month,$day) {

	db_begin();

	$day_begin=mktime(0,0,0,$month,$day,$year);
	$day_end=($day_begin + 86400);

	echo "Stats_project: $datetime::$day_begin::$day_end::".date('Ymd',$day_begin)."::".date('Ymd',$day_end)."--> $i\n";

	db_query("DELETE FROM stats_project WHERE month='$year$month' AND day='$day'");

$sql="INSERT INTO stats_project

SELECT 
'$year$month'::int AS month,
'$day'::int AS day,
* FROM (

SELECT 
	groups.group_id,
	r.count AS release_count,
	fmcount.count AS msg_posted,
	fucount.count AS msg_uniq_auth,
	obug.count AS bugs_opened,
	cbug.count AS bugs_closed,
	osupport.count AS support_opened,
	csupport.count AS support_closed,
	opatches.count AS patches_opened,
	cpatches.count AS patches_closed,
	oartifact.count AS artifacts_opened,
	cartifact.count AS artifacts_closed,
	otask.count AS tasks_opened,
	ctask.count AS tasks_closed,
	helpr.count AS help_requests

FROM groups

--
--	Create tmp table of groups that made any releases
--
LEFT JOIN (
	SELECT group_id,COUNT(release_id) AS count
    FROM frs_release,frs_package
    WHERE 
		frs_release.release_date > '$day_begin' 
		AND frs_release.release_date < '$day_end' 
        AND frs_release.package_id = frs_package.package_id
	GROUP BY group_id 
	) r USING (group_id)

--
--	Create tmp table of groups that had any forum posts
--
LEFT JOIN ( 
	SELECT forum_group_list.group_id,COUNT(forum.msg_id) AS count
    FROM forum_group_list, forum
    WHERE 
		forum_group_list.group_forum_id = forum.group_forum_id 
        AND forum.post_date > '$day_begin' 
		AND forum.post_date < '$day_end' 
	GROUP BY forum_group_list.group_id
	) fmcount USING (group_id)

--
--	Create tmp table of groups and unique posters to forums
--
LEFT JOIN ( 
	SELECT forum_group_list.group_id,COUNT( DISTINCT(forum.posted_by) ) AS count
    FROM forum_group_list, forum
    WHERE 
		forum_group_list.group_forum_id = forum.group_forum_id 
        AND forum.post_date > '$day_begin' 
		AND forum.post_date < '$day_end'
    GROUP BY group_id
	) fucount USING (group_id)

--
--	Create tmp table of groups and opened bugs
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.open_date > '$day_begin'
		AND a.open_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.datatype='1'
	GROUP BY agl.group_id
	) obug USING (group_id)

--
--	Create tmp table of groups and closed bugs
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.close_date > '$day_begin'
		AND a.close_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.datatype='1'
	GROUP BY agl.group_id
	) cbug USING (group_id)

--
--	Create tmp table of groups and opened support
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.open_date > '$day_begin'
		AND a.open_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.datatype='2'
	GROUP BY agl.group_id
	) osupport USING (group_id)

--
--	Create tmp table of groups and closed support
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.close_date > '$day_begin'
		AND a.close_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.datatype='2'
	GROUP BY agl.group_id
	) csupport USING (group_id)

--
--	Create tmp table of groups and opened patches
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.open_date > '$day_begin'
		AND a.open_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.datatype='3'
	GROUP BY agl.group_id
	) opatches USING (group_id)

--
--	Create tmp table of groups and closed patches
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.close_date > '$day_begin'
		AND a.close_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.datatype='3'
	GROUP BY agl.group_id
	) cpatches USING (group_id)

--
--	Create tmp table of groups and opened total artifacts
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.open_date > '$day_begin'
		AND a.open_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
	GROUP BY agl.group_id
	) oartifact USING (group_id)

--
--	Create tmp table of groups and closed total artifacts
--
LEFT JOIN (
	SELECT agl.group_id,count(*) AS count
	FROM artifact_group_list agl,artifact a
	WHERE
		a.close_date > '$day_begin'
		AND a.close_date < '$day_end'
		AND a.group_artifact_id=agl.group_artifact_id
	GROUP BY agl.group_id
	) cartifact USING (group_id)

--
--	Create tmp table of groups that opened tasks
--
LEFT JOIN (
	SELECT pgl.group_id,count(pt.project_task_id) AS count 
	FROM project_group_list pgl, project_task pt
	WHERE 
		pgl.group_project_id=pt.group_project_id 
		AND pt.start_date > '$day_begin'
		AND pt.start_date < '$day_end'
	GROUP BY pgl.group_id
	) otask USING (group_id)

--
--	Create tmp table of groups that closed tasks
--
LEFT JOIN (
	SELECT pgl.group_id,count(pt.project_task_id) AS count 
	FROM project_group_list pgl, project_task pt
	WHERE 
		pgl.group_project_id=pt.group_project_id 
		AND pt.end_date > '$day_begin'
		AND pt.end_date < '$day_end'
	GROUP BY pgl.group_id
	) ctask USING (group_id)

--
--	Create tmp table of groups that closed tasks
--
LEFT JOIN (
	SELECT group_id,COUNT(job_id) AS count
    FROM people_job
    WHERE 
		date > '$day_begin' 
		AND date < '$day_end'
    GROUP BY group_id
	) helpr USING (group_id)

) mess

--
--	We really only want the rows that have any content
--
WHERE
release_count > 0 
OR msg_posted > 0
OR msg_uniq_auth > 0
OR bugs_opened > 0
OR bugs_closed > 0
OR support_opened > 0
OR support_closed > 0
OR patches_opened > 0
OR patches_closed > 0
OR artifacts_opened > 0
OR artifacts_closed > 0
OR tasks_opened > 0
OR tasks_closed > 0
OR help_requests > 0;";

	db_query($sql);
	//echo $sql;
	echo db_error();

	db_commit();

}

?>
