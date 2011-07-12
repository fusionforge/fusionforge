#! /usr/bin/php
<?php
require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'pm/ProjectTask.class.php';

db_begin();

$res = db_query_params ('SELECT project_task_id FROM project_task WHERE status_id=$1',
			array('3')) ;


if (!$res) {
	echo "FAIL\n";
	exit();
} else {
	$tasks = array();

	for ($i=0;$i<db_numrows($res);$i++) {
		$data = db_fetch_array($res);
		$tasks[] = $data['project_task_id'];
	}

	foreach ($tasks as $task_id) {
		$res = db_query_params ('DELETE FROM project_assigned_to WHERE project_task_id=$1',
					array ($task_id));
		if (!$res) {
			echo 'Error deleting assigned users relationship: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM project_dependencies WHERE project_task_id=$1',
					array ($task_id)) ;
		if (!$res) {
			echo 'Error deleting dependencies: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM project_history WHERE project_task_id=$1',
					array ($task_id)) ;
		if (!$res) {
			echo 'Error deleting history: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM project_messages WHERE project_task_id=$1',
					array ($task_id)) ;
		if (!$res) {
			echo 'Error deleting messages: '.db_error();
			db_rollback();
			eixt();
		}
		$res = db_query_params ('DELETE FROM project_task_artifact WHERE project_task_id=$1',
					array ($task_id)) ;
		if (!$res) {
			echo 'Error deleting artifacts: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM rep_time_tracking WHERE project_task_id=$1',
					array ($task_id)) ;
		if (!$res) {
			echo 'Error deleting time tracking report: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM project_task WHERE project_task_id=$1',
					array ($task_id)) ;
		if (!$res) {
			echo 'Error deleting task: '.db_error();
			db_rollback();
			exit();
		}
	}
}

$res = db_query_params ('SELECT artifact_id FROM artifact WHERE status_id=$1',
			array('3')) ;


if (!$res) {
	echo "FAIL\n";
	exit();
} else {
	$artifacts = array();

	for ($i=0;$i<db_numrows($res);$i++) {
		$data = db_fetch_array($res);
		$artifacts[] = $data['artifact_id'];
	}

	foreach ($artifacts as $artifact_id) {
		$res = db_query_params ('DELETE FROM artifact_extra_field_data WHERE artifact_id=$1',
					array ($artifact_id)) ;
		if (!$res) {
			echo 'Error deleting extra field data: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM artifact_file WHERE artifact_id=$1',
					array ($artifact_id)) ;
		if (!$res) {
			echo 'Error deleting file from db: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM artifact_message WHERE artifact_id=$1',
					array ($artifact_id)) ;
		if (!$res) {
			echo 'Error deleting message: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM artifact_history WHERE artifact_id=$1',
					array ($artifact_id)) ;
		if (!$res) {
			echo 'Error deleting history: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM artifact_monitor WHERE artifact_id=$1',
					array ($artifact_id)) ;
		if (!$res) {
			echo 'Error deleting monitor: '.db_error();
			db_rollback();
			exit();
		}
		$res = db_query_params ('DELETE FROM artifact WHERE artifact_id=$1',
					array ($artifact_id)) ;
		if (!$res) {
			echo 'Error deleting artifact: '.db_error();
			db_rollback();
			exit();
		}
	}
}
echo "SUCCESS\n";
db_commit();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
