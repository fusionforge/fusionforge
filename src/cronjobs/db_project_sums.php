#! /usr/bin/php
<?php
/**
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
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

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

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
if (forge_get_config('use_forum')) {
	db_begin();

	db_query_params ('LOCK TABLE forum_agg_msg_count IN ACCESS EXCLUSIVE MODE',
			 array()) ;
	db_query_params ('LOCK TABLE forum IN ACCESS EXCLUSIVE MODE',
			 array()) ;
	db_query_params ('LOCK TABLE forum_group_list IN ACCESS EXCLUSIVE MODE',
			 array()) ;

	$res = db_query_params ('DELETE FROM forum_agg_msg_count',
				array()) ;

	if (!$res) {
		$err .= "DELETE FROM forum_agg_msg_count : ".db_error();
	}

	$res = db_query_params ('INSERT INTO forum_agg_msg_count
SELECT fgl.group_forum_id,count(f.msg_id)
FROM forum_group_list fgl
LEFT JOIN forum f USING (group_forum_id)
GROUP BY fgl.group_forum_id',
				array()) ;

	if (!$res) {
		$err .= "INSERT INTO forum_agg_msg_count : ".db_error();
	}
	
	db_commit();
}

/*
	Create an aggregation table that includes counts of artifacts
*/
if (forge_get_config('use_tracker')) {
	db_begin();

	db_query_params ('LOCK TABLE artifact_counts_agg IN ACCESS EXCLUSIVE MODE',
			 array()) ;
	db_query_params ('LOCK TABLE artifact IN ACCESS EXCLUSIVE MODE',
			 array()) ;
	db_query_params ('LOCK TABLE artifact_group_list IN ACCESS EXCLUSIVE MODE',
			 array()) ;

	$rel = db_query_params ('DELETE FROM artifact_counts_agg',
				array()) ;

	$err .= db_error();

	$rel=db_query_params ('INSERT INTO artifact_counts_agg
SELECT agl.group_artifact_id,
(SELECT count(*) FROM artifact WHERE status_id <> 3 AND group_artifact_id=agl.group_artifact_id),
(SELECT count(*) FROM artifact WHERE status_id=1 AND group_artifact_id=agl.group_artifact_id)
FROM artifact_group_list agl
LEFT JOIN artifact a USING (group_artifact_id)
GROUP BY agl.group_artifact_id',
			      array()) ;
	
	$err .= db_error();
	
	db_commit();
}

/*

	Rebuild the project_sums_agg table, which saves us
	from doing really expensive queries
	each time the project summary is viewed

*/
db_begin();
$res=db_query_params ('DELETE FROM project_sums_agg',
		      array()) ;

/*
	Get counts of mailing lists
*/
if (forge_get_config('use_mail')) {
	$res=db_query_params ('INSERT INTO project_sums_agg
SELECT group_id,$1 AS type,count(*) AS count
FROM mail_group_list WHERE is_public = 1
GROUP BY group_id,type',
			      array('mail'));
	$err .= db_error();
}

/*
	Get counts of surveys
*/
if (forge_get_config('use_survey')) {
	$res=db_query_params ('INSERT INTO project_sums_agg
SELECT group_id,$1 AS type,count(*) AS count
FROM surveys
WHERE is_active=$2
GROUP BY group_id,type',
			      array('surv',
				    '1'));
	$err .= db_error();

}

/*
	Forum message count
*/
if (forge_get_config('use_forum')) {
	$res=db_query_params ('INSERT INTO project_sums_agg
SELECT forum_group_list.group_id,$1 AS type, count(forum.msg_id) AS count
FROM forum,forum_group_list
WHERE forum.group_forum_id=forum_group_list.group_forum_id
AND forum_group_list.is_public=1
GROUP BY group_id,type',
			      array('fmsg'));
	$err .= db_error();

/*
	Forum count
*/
	$res=db_query_params ('INSERT INTO project_sums_agg
SELECT group_id,$1 AS type, count(*) AS count
FROM forum_group_list
WHERE is_public=1
GROUP BY group_id,type',
			      array('fora'));
	$err .= db_error();
}
db_commit();
$err .= db_error();

cron_entry(3,$err);

?>
