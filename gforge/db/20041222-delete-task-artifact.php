#! /usr/bin/php4 -f
<?php
require_once('www/include/squal_pre.php');
require_once('common/pm/ProjectTask.class');

$res = db_query("SELECT project_task_id FROM project_task WHERE status_id='3'");

if (!$res) {
	echo "FAIL\n";
	exit();
} else
{
	$tasks = array();
	
	for ($i=0;$i<db_numrows($res);$i++) {
		$data = &db_fetch_array($res);
		$tasks[] = $data['project_task_id'];
	}
	
	foreach ($tasks as $task_id) {
		$task = projecttask_get_object($task_id);
		if (!$task || !is_object($task)) {
			// echo "Error instantiating Task object with id: $task_id\n";
			echo "FAIL\n";
			exit();
		} else {
			if (!$task->delete()) {
				// echo "Error deleting Task with id: $task_id\n";
				echo "FAIL\n";
				exit();
			} else {
				//echo "Task with id: $task_id successfully deleted\n";
			}
		}
	}
	echo "SUCCESS\n";
}
?>