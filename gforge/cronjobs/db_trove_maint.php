#!/usr/local/bin/php
<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

/*


	Nightly internal-use script - adds the projects from trove to the foundries


*/

require ('squal_pre.php');    

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
	exit_permission_denied();
}*/

/*

	Rebuild the trove_agg table, which saves us
	from doing really expensive queries in trove
	each time of the trove map is viewed

*/

db_begin();

db_query("DELETE FROM trove_agg;");

$sql="INSERT INTO trove_agg
        SELECT 
            tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name, g.status, g.register_time, g.short_description, 
            project_metric.percentile, project_metric.ranking 
        FROM groups g
        LEFT JOIN project_metric USING (group_id) , 
        trove_group_link tgl 
        WHERE 
        tgl.group_id=g.group_id 
        AND (g.is_public=1) 
        AND (g.type=1) 
        AND (g.status='A') 
        ORDER BY g.group_name;";

db_query($sql);
echo db_error();

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

$res=db_query("SELECT trove_cat.trove_cat_id,trove_cat.parent,count(*) AS count
	FROM trove_cat,trove_group_link,groups 
	WHERE trove_cat.trove_cat_id=trove_group_link.trove_cat_id 
	AND groups.group_id=trove_group_link.group_id 
	AND groups.status='A' 
	AND groups.type='1' 
	AND groups.is_public='1' 
	GROUP BY trove_cat.trove_cat_id,trove_cat.parent");

$rows=db_numrows($res);

for ($i=0; $i<$rows; $i++) {

	$cat_counts[db_result($res,$i,'trove_cat_id')][0]=db_result($res,$i,'parent');
	$cat_counts[db_result($res,$i,'trove_cat_id')][1]=db_result($res,$i,'count');

	$parent_list[db_result($res,$i,'parent')][]=db_result($res,$i,'trove_cat_id');

}

$sum_totals=array();

function get_trove_sub_projects($cat_id) {
	global $cat_counts,$sum_totals,$parent_list;

	//number of groups that were in this trove_cat
	$count=$cat_counts[$cat_id][1];

	//number of children of this trove_cat
	$rows=count( $parent_list[$cat_id] );

	for ($i=0; $i<$rows; $i++) {
		$count += get_trove_sub_projects( $parent_list[$cat_id][$i] );
	}
	$sum_totals["$cat_id"]=$count;
	return $count;
}

//start the recursive function at the top of the trove tree
get_trove_sub_projects(18);

db_begin();
db_query("DELETE FROM trove_treesums");
echo db_error();
//echo "<TABLE>";
while (list($k,$v) = each($sum_totals)) {
	db_query("INSERT INTO trove_treesums (trove_cat_id,subprojects) VALUES ($k,$v)");
//	echo "<TR><TD>$k</TD><TD>$v</TD></TR>\n";

}
//echo "</TABLE>";

db_commit();
echo "Done: ".db_error();

?>
