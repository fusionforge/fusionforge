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
	echo "DELETE FROM forum_agg_msg_count : ".db_error();
}

$res = db_query("INSERT INTO forum_agg_msg_count
SELECT fgl.group_forum_id,count(f.msg_id)
FROM forum_group_list fgl
LEFT JOIN forum f USING (group_forum_id)
GROUP BY fgl.group_forum_id;");
if (!$res) {
	echo "INSERT INTO forum_agg_msg_count : ".db_error();
}

db_commit();

db_query("VACUUM ANALYZE forum_agg_msg_count;");


/*
	Create an aggregation table that includes counts of artifacts
*/
db_begin();

db_query("LOCK TABLE artifact_counts_agg IN ACCESS EXCLUSIVE MODE;");
db_query("LOCK TABLE artifact IN ACCESS EXCLUSIVE MODE;");
db_query("LOCK TABLE artifact_group_list IN ACCESS EXCLUSIVE MODE;");

$rel = db_query("DELETE FROM artifact_counts_agg;");
echo db_error();

$rel=db_query("INSERT INTO artifact_counts_agg
SELECT agl.group_artifact_id,
(SELECT count(*) FROM artifact WHERE status_id <> 3 AND group_artifact_id=agl.group_artifact_id), 
(SELECT count(*) FROM artifact WHERE status_id=1 AND group_artifact_id=agl.group_artifact_id)
FROM artifact_group_list agl 
LEFT JOIN artifact a USING (group_artifact_id)
GROUP BY agl.group_artifact_id;");
echo db_error();

db_commit();

db_query("VACUUM ANALYZE artifact_counts_agg;");

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
$sql="INSERT INTO project_sums_agg 
	SELECT group_id,'mail'::text AS type,count(*) AS count 
	FROM mail_group_list
	GROUP BY group_id,type;";

$res=db_query($sql);
echo db_error();


/*
	Get counts of surveys
*/
$sql="INSERT INTO project_sums_agg 
	SELECT group_id,'surv'::text AS type,count(*) AS count 
	FROM surveys
	WHERE is_active='1'
	GROUP BY group_id,type;";

$res=db_query($sql);
echo db_error();


/*
	Forum message count
*/
$sql="INSERT INTO project_sums_agg
	SELECT forum_group_list.group_id,'fmsg'::text AS type, count(forum.msg_id) AS count 
	FROM forum,forum_group_list 
	WHERE forum.group_forum_id=forum_group_list.group_forum_id 
	AND forum_group_list.is_public=1
	GROUP BY group_id,type;";

$res=db_query($sql);
echo db_error();


/*
	Forum count
*/
$sql="INSERT INTO project_sums_agg
	SELECT group_id,'fora'::text AS type, count(*) AS count 
	FROM forum_group_list 
	WHERE is_public=1
	GROUP BY group_id,type;";

$res=db_query($sql);
echo db_error();


db_commit();
echo db_error();

db_query("VACUUM ANALYZE project_sums_agg;");

if (db_error()) {
	echo "Error: ".db_error();
}

?>
