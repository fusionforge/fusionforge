#! /usr/bin/php4 -f
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: populate_foundries.php,v 1.9 2001/06/13 18:44:09 pfalcon Exp $
  *
  */

/*


	Nightly internal-use script - adds the projects from trove to the foundries


*/

require ('squal_pre.php');    

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
	exit_permission_denied();
}*/

$result=db_query("SELECT foundry_id,trove_categories FROM foundry_data");

//array of foundries
$foundries=util_result_column_to_array($result,0);

//array of trove_categories for each of those foundries
$foundry_cats=util_result_column_to_array($result,1);


$count=count($foundries);

function get_trove_sub_projects($cat_id) {
	if (!$cat_id) {
		return '';
	}
//	echo '<P>IN SUBPROJECT'.$cat_id;
	//return an array of trove categories under $cat_id
	$sql="SELECT trove_cat_id FROM trove_cat WHERE parent IN ($cat_id)";
	$result=db_query($sql);
	echo db_error();
	$rows=db_numrows($result);
	for ($i=0; $i<$rows; $i++) {
		$trove_list= array_merge( get_trove_sub_projects(db_result($result,$i,0)),$trove_list );
	}
	return array_merge( util_result_column_to_array($result),$trove_list );
}

db_begin();
db_query("DELETE FROM foundry_projects");

for ($i=0; $i<$count; $i++) {
//	echo "<BR>$i";
	$trove_list=array();
	$trove_list= get_trove_sub_projects($foundry_cats[$i]);
	$trove_list[]=$foundry_cats[$i];

	$trove_cats=implode(',',$trove_list);

	if (strlen($trove_cats) > 1) {
		$sql="INSERT INTO foundry_projects (foundry_id,project_id) 
		SELECT DISTINCT $foundries[$i],groups.group_id FROM groups,trove_group_link 
		WHERE trove_group_link.trove_cat_id IN ($trove_cats) 
		AND groups.group_id=trove_group_link.group_id 
		AND groups.is_public=1 
		AND groups.status='A' ";

		$result=db_query($sql);
		echo db_error();
	}
	//add this project to the foundry so it can submit news
	db_query("INSERT INTO foundry_projects (foundry_id,project_id) ".
		"VALUES ($foundries[$i],$foundries[$i])");

	//now add the preferred projects into the foundry data
	db_query("INSERT INTO foundry_projects (foundry_id,project_id) ".
		"SELECT foundry_id,group_id FROM foundry_preferred_projects");
}

db_commit();
if (db_error()) {
	echo "Done: ".db_error();
}
?>
