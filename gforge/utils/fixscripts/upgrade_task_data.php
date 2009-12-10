<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net

exit;

/*

	One-time use script

*/

require $gfwww.'include/pre.php';    

session_require(array('group'=>'1','admin_flags'=>'A'));

//get all the tasks
$result=db_query_params ('SELECT project_task_id FROM project_task ORDER BY project_task_id ASC',
			array());
$rows=db_numrows($result);
echo "\nRows: $rows\n";
flush();

for ($i=0; $i<$rows; $i++) {

	echo "\n".db_result($result,$i,'project_task_id')."\n";

	/*
		//insert a default dependency
	*/

	$res2=db_query_params('SELECT * FROM project_dependencies WHERE project_task_id=$1',
			      array (db_result($result,$i,'project_task_id'))) ;
	$rows2=db_numrows($res2);
	if ($rows2 < 1) {
		db_query_params ('INSERT INTO project_dependencies VALUES ($1,$2,100)',
				 array ('',
					db_result($result,$i,'project_task_id'))) ;
	} else if ($rows2 > 1) {
		db_query_params ('DELETE FROM project_dependencies WHERE project_task_id=$1 AND is_dependent_on_task_id=100',
				 array (db_result($result,$i,'project_task_id'))) ;
	}

	/*
		//insert a default assignee 
	*/

	$res2=db_query_params('SELECT * FROM project_assigned_to WHERE project_task_id=$1',
			      array (db_result($result,$i,'project_task_id'))) ;
	$rows2=db_numrows($res2);
	if ($rows2 < 1) {
		db_query_params ('INSERT INTO project_assigned_to VALUES ($1,$2,100)',
				 array ('',
					db_result($result,$i,'project_task_id'))) ;
	} else if ($rows2 > 1) {
		db_query_params ('DELETE FROM project_assigned_to WHERE project_task_id=$1 AND assigned_to_id=100',
				 array (db_result($result,$i,'project_task_id'))) ;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
