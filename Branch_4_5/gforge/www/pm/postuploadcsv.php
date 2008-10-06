<?php
require_once('common/pm/import_utils.php');

$input_file = $_FILES['userfile']['tmp_name'];

if (is_uploaded_file($input_file)) {
	$size =	@filesize($input_file);	
	$handle = fopen($input_file, 'r');
	$tasks = array();
	
	while (($cols = fgetcsv($handle, 4096, ",")) !== FALSE) {

		$resources = array();
		for ($i=12;$i<17;$i++) {
			if (trim($cols[$i]) != '') {
				$resources[] = array('user_name'=>$cols[$i]);
			}
		}

		$dependentOn = array();

		for ($i=17;$i<30;$i=$i+3) {
			if (trim($cols[$i]) != '') {
				$dependentOn[] = array('task_id'=>$cols[$i], 'msproj_id'=>$cols[$i+1], 'task_name'=>'', 'link_type'=>$cols[$i+2]);
			}
		}

		$tasks[] = array('id'=>$cols[0],
				'msproj_id'=>$cols[1],
				'parent_id'=>$cols[2],
				'parent_msproj_id'=>$cols[3],
				'name'=>$cols[4],
				'duration'=>$cols[5],
				'work'=>$cols[6],
				'start_date'=>$cols[7],
				'end_date'=>$cols[8],
				'percent_complete'=>$cols[9],	
				'priority'=>$cols[10],
				'resources'=>$resources,
				'dependenton'=>$dependentOn,
				'notes'=>$cols[11]);
	}
	
}

$res=&pm_import_tasks($group_project_id, &$tasks);

if ($res['success']) {
	$feedback .= 'Import Was Successful';
} else {
	$feedback .= $res['errormessage'];
}

?>
