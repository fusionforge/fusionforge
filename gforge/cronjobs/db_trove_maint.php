#! /usr/bin/php4 -f
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


/*
//FIRST TIME THIS SCRIPT IS RUN - YOU MAY NEED TO RUN THIS QUERY FIRST

//nightly aggregation query
DROP TABLE trove_agg;
CREATE TABLE trove_agg AS
	SELECT tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name,
		g.status, g.register_time, g.short_description,
		project_weekly_metric.percentile, project_weekly_metric.ranking
		FROM groups g
		LEFT JOIN project_weekly_metric USING (group_id) ,
		trove_group_link tgl
		WHERE
		tgl.group_id=g.group_id
		AND (g.is_public=1)
		AND (g.type=1)
		AND (g.status='A')
	ORDER BY trove_cat_id ASC, ranking ASC;

CREATE INDEX troveagg_trovecatid ON trove_agg(trove_cat_id);
create index troveagg_trovecatid_ranking ON trove_agg(trove_cat_id,ranking);

DROP TABLE trove_treesums;
CREATE TABLE "trove_treesums" (
		"trove_treesums_id" serial primary key,
		"trove_cat_id" integer DEFAULT '0' NOT NULL,
		"limit_1" integer DEFAULT '0' NOT NULL,
		"subprojects" integer DEFAULT '0' NOT NULL
);

*/

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
  exit_permission_denied();
  }*/

/*
  
  Rebuild the trove_agg table, which saves us
  from doing really expensive queries in trove
  each time of the trove map is viewed
  
*/

db_begin(SYS_DB_TROVE);

db_query("DELETE FROM trove_agg;", -1, 0, SYS_DB_TROVE);

$sql="INSERT INTO trove_agg
	(SELECT tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name, g.status, g.register_time, g.short_description, project_weekly_metric.percentile, project_weekly_metric.ranking
	FROM groups g
	LEFT JOIN project_weekly_metric USING (group_id), trove_group_link tgl 
	WHERE tgl.group_id=g.group_id 
	AND (g.is_public=1) 
	AND (g.type_id=1) 
	AND (g.status='A') 
	ORDER BY trove_cat_id ASC, ranking ASC)";

db_query($sql, -1, 0, SYS_DB_TROVE);
$err .= db_error(SYS_DB_TROVE);

db_commit(SYS_DB_TROVE);

/*

Calculate the number of projects under each category

Do this by first running an aggregate query in the database,
then putting that into two associative arrays.

Start at the top of the trove tree and recursively go down 
the tree, building a third associative array which contains
the count of projects under each category

Then iterate through that third array and insert the results into the
database inside of a transaction

*/

$cat_counts=array();
$parent_list=array();

$q = "SELECT trove_cat.trove_cat_id,trove_cat.parent
	FROM trove_cat
	WHERE trove_cat.trove_cat_id!=0
	GROUP BY trove_cat.trove_cat_id,trove_cat.parent;" ;
$res=db_query($q);
$rows=db_numrows($res);

for ($i=0; $i<$rows; $i++) {
	$parent_list[db_result($res,$i,'parent')][]=db_result($res,$i,'trove_cat_id');
}

$res=db_query("SELECT trove_cat.trove_cat_id,trove_cat.parent,count(groups.group_id) AS count
	FROM  trove_cat LEFT JOIN trove_group_link ON
		trove_cat.trove_cat_id=trove_group_link.trove_cat_id
	LEFT JOIN groups ON
		groups.group_id=trove_group_link.group_id
	WHERE (groups.status='A' OR groups.status IS NULL)
	AND ( groups.type='1' OR groups.status IS NULL)
	AND ( groups.is_public='1' OR groups.is_public IS NULL)
	GROUP BY trove_cat.trove_cat_id,trove_cat.parent", -1, 0, SYS_DB_TROVE);

$rows = db_numrows($res);

for ($i=0; $i<$rows; $i++) {
	$cat_counts[db_result($res,$i,'trove_cat_id')][0]=db_result($res,$i,'parent');
	$cat_counts[db_result($res,$i,'trove_cat_id')][1]=db_result($res,$i,'count');
}

$sum_totals=array();

function get_trove_sub_projects($cat_id) {
	global $cat_counts,$sum_totals,$parent_list;

	//number of groups that were in this trove_cat
	$count=$cat_counts[$cat_id][1];
	if ($count == '') { $count = 0 ; }
 
	//number of children of this trove_cat
	$rows=count( $parent_list[$cat_id] );

	for ($i=0; $i<$rows; $i++) {
		$count += get_trove_sub_projects( $parent_list[$cat_id][$i] );
	}
	$sum_totals["$cat_id"]=$count;
	return $count;
}

//start the recursive function at the top of the trove tree
$res2=db_query("SELECT trove_cat_id FROM trove_cat WHERE parent=0", -1, 0, SYS_DB_TROVE);

for ($i=0; $i< db_numrows($res2); $i++) {
	get_trove_sub_projects( db_result($res2,$i,0) );
}

db_begin(SYS_DB_TROVE);
db_query("DELETE FROM trove_treesums", -1, 0, SYS_DB_TROVE);
$err .= db_error(SYS_DB_TROVE);

//$err .= "<table>";
while (list($k,$v) = each($sum_totals)) {
	$res = db_query("INSERT INTO trove_treesums (trove_cat_id,subprojects) 
		VALUES ($k,$v)", -1, 0, SYS_DB_TROVE);
	if (!$res || db_affected_rows($res)!=1) {
		$err .= db_error(SYS_DB_TROVE);
	}
//	$err .= "<tr><td>$k</td><td>$v</td></tr>\n";

}
//$err .= "</TABLE>";

db_commit(SYS_DB_TROVE);

if (db_error(SYS_DB_TROVE)) {
	$err .= "Error: ".db_error(SYS_DB_TROVE);
}

cron_entry(5,$err)

?>
