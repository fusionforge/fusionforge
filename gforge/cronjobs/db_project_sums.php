#! /usr/bin/php4 -f
<?php
/**
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$err='';

/*

	Aggregation script

	Since we cannot crunch down all the data on the fly anymore, 
	we need to crunch it down once daily into a separate table, 
	then join against that table to get counts.

*/


/*
    Create an aggregation table that includes counts of forum messages 
*/
db_begin();

db_query("LOCK TABLE forum_agg_msg_count IN ACCESS EXCLUSIVE MODE;");
db_query("LOCK TABLE forum IN ACCESS EXCLUSIVE MODE;");
db_query("LOCK TABLE forum_group_list IN ACCESS EXCLUSIVE MODE;");

$res = db_query("DELETE FROM forum_agg_msg_count;");
if (!$res) {
	$err .= "DELETE FROM forum_agg_msg_count : ".db_error();
}

$res = db_query("INSERT INTO forum_agg_msg_count
SELECT fgl.group_forum_id,count(f.msg_id)
FROM forum_group_list fgl
LEFT JOIN forum f USING (group_forum_id)
GROUP BY fgl.group_forum_id;");
if (!$res) {
	$err .= "INSERT INTO forum_agg_msg_count : ".db_error();
}

db_commit();

if ($sys_database_type != 'mysql') {
	db_query("VACUUM ANALYZE forum_agg_msg_count;");
}

/*
	Create an aggregation table that includes counts of artifacts
*/
db_begin();

db_query("LOCK TABLE artifact_counts_agg IN ACCESS EXCLUSIVE MODE;");
db_query("LOCK TABLE artifact IN ACCESS EXCLUSIVE MODE;");
db_query("LOCK TABLE artifact_group_list IN ACCESS EXCLUSIVE MODE;");

$rel = db_query("DELETE FROM artifact_counts_agg;");
$err .= db_error();

$rel=db_query("INSERT INTO artifact_counts_agg
SELECT agl.group_artifact_id,
(SELECT count(*) FROM artifact WHERE status_id <> 3 AND group_artifact_id=agl.group_artifact_id), 
(SELECT count(*) FROM artifact WHERE status_id=1 AND group_artifact_id=agl.group_artifact_id)
FROM artifact_group_list agl 
LEFT JOIN artifact a USING (group_artifact_id)
GROUP BY agl.group_artifact_id;");
$err .= db_error();

db_commit();

if ($sys_database_type != 'mysql') {
	db_query("VACUUM ANALYZE artifact_counts_agg;");
}

/*

	Rebuild the project_sums_agg table, which saves us
	from doing really expensive queries
	each time the project summary is viewed

*/

db_begin();
$res=db_query("DELETE FROM project_sums_agg;");

/*
	Get counts of mailing lists
*/
$sql="INSERT INTO project_sums_agg ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT group_id,'mail' AS type,count(*) AS count ";
} else {
	$sql.="SELECT group_id,'mail'::text AS type,count(*) AS count ";
}
$sql.="
	FROM mail_group_list WHERE is_public != 9
	GROUP BY group_id,type;";

$res=db_query($sql);
$err .= db_error();


/*
	Get counts of surveys
*/
$sql="INSERT INTO project_sums_agg ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT group_id,'surv' AS type,count(*) AS count ";
} else {
	$sql.="SELECT group_id,'surv'::text AS type,count(*) AS count ";
}
$sql.="
	FROM surveys
	WHERE is_active='1'
	GROUP BY group_id,type;";

$res=db_query($sql);
$err .= db_error();


/*
	Forum message count
*/
$sql="INSERT INTO project_sums_agg ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT forum_group_list.group_id,'fmsg' AS type, count(forum.msg_id) AS count ";
} else {
	$sql.="SELECT forum_group_list.group_id,'fmsg'::text AS type, count(forum.msg_id) AS count ";
}
$sql.="
	FROM forum,forum_group_list 
	WHERE forum.group_forum_id=forum_group_list.group_forum_id 
	AND forum_group_list.is_public=1
	GROUP BY group_id,type;";

$res=db_query($sql);
$err .= db_error();


/*
	Forum count
*/
$sql="INSERT INTO project_sums_agg ";
if ($sys_database_type == 'mysql') {
	$sql.="SELECT group_id,'fora' AS type, count(*) AS count ";
} else {
	$sql.="SELECT group_id,'fora'::text AS type, count(*) AS count ";
}
$sql.="
	FROM forum_group_list 
	WHERE is_public=1
	GROUP BY group_id,type;";

$res=db_query($sql);
$err .= db_error();


db_commit();
$err .= db_error();

if ($sys_database_type != 'mysql') {
	db_query("VACUUM ANALYZE project_sums_agg;");

	if (db_error()) {
		$err .= "Error: ".db_error();
	}
}

cron_entry(3,$err);

?>
