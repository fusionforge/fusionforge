#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2009, Roland Mas
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
  
  Rebuild the trove_agg table, which saves us
  from doing really expensive queries in trove
  each time of the trove map is viewed
  
*/

db_begin();

db_query_params ('DELETE FROM trove_agg',
		 array());

db_query_params ('INSERT INTO trove_agg
(SELECT tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name, g.status, g.register_time, g.short_description, project_weekly_metric.percentile, project_weekly_metric.ranking
FROM groups g
LEFT JOIN project_weekly_metric USING (group_id), trove_group_link tgl
WHERE tgl.group_id=g.group_id
AND g.is_public = 1
AND g.type_id = 1
AND g.status = $1
ORDER BY trove_cat_id ASC, ranking ASC)',
		 array('A'));
$err .= db_error();

db_commit();

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

$res = db_query_params ('SELECT trove_cat.trove_cat_id,trove_cat.parent
FROM trove_cat
WHERE trove_cat.trove_cat_id!=0
GROUP BY trove_cat.trove_cat_id,trove_cat.parent',
			array());
$rows=db_numrows($res);

for ($i=0; $i<$rows; $i++) {
	$parent_list[db_result($res,$i,'parent')][]=db_result($res,$i,'trove_cat_id');
}

$res = db_query_params ('SELECT trove_cat.trove_cat_id,trove_cat.parent,count(groups.group_id) AS count
	FROM  trove_cat LEFT JOIN trove_group_link ON
		trove_cat.trove_cat_id=trove_group_link.trove_cat_id
	LEFT JOIN groups ON
		groups.group_id=trove_group_link.group_id
	WHERE (groups.status=$1 OR groups.status IS NULL)
	AND (groups.type_id=1 OR groups.status IS NULL)
	AND (groups.is_public=1 OR groups.is_public IS NULL)
	GROUP BY trove_cat.trove_cat_id,trove_cat.parent',
			array('A'));

$rows = db_numrows($res);

for ($i=0; $i<$rows; $i++) {
	$cat_counts[db_result($res,$i,'trove_cat_id')][0]=db_result($res,$i,'parent');
	$cat_counts[db_result($res,$i,'trove_cat_id')][1]=db_result($res,$i,'count');
}

$sum_totals=array();

function get_trove_sub_projects($cat_id) {
	global $cat_counts,$sum_totals,$parent_list;

	// Number of groups that were in this trove_cat
	$count=isset($cat_counts[$cat_id][1]) ? $cat_counts[$cat_id][1] : 0;
 
	//number of children of this trove_cat
	$rows=count( @$parent_list[$cat_id] );

	for ($i=0; $i<$rows; $i++) {
		$count += get_trove_sub_projects( $parent_list[$cat_id][$i] );
	}
	$sum_totals["$cat_id"]=$count;
	return $count;
}

//start the recursive function at the top of the trove tree
$res2 = db_query_params ('SELECT trove_cat_id FROM trove_cat WHERE parent=0',
			 array());

for ($i=0; $i< db_numrows($res2); $i++) {
	get_trove_sub_projects( db_result($res2,$i,0) );
}

db_begin();
db_query_params ('DELETE FROM trove_treesums',
		 array());
$err .= db_error();

//$err .= "<table>";
while (list($k,$v) = each($sum_totals)) {
	$res = db_query_params ('INSERT INTO trove_treesums (trove_cat_id,subprojects) 
		VALUES ($1,$2)',
				array($k,
				      $v));
	if (!$res || db_affected_rows($res)!=1) {
		$err .= db_error();
	}
//	$err .= "<tr><td>$k</td><td>$v</td></tr>\n";

}
//$err .= "</table>";

db_commit();

if (db_error()) {
	$err .= "Error: ".db_error();
}

cron_entry(5,$err)

?>
