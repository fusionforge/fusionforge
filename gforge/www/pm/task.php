<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once('pre.php');
require_once('www/pm/include/ProjectGroupHTML.class');
require_once('www/pm/include/ProjectTaskHTML.class');
require_once('common/pm/ProjectGroupFactory.class');

if (!$group_id || !$group_project_id) {
	exit_missing_params();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}

$pg = new ProjectGroupHTML($g,$group_project_id);
if (!$pg || !is_object($pg)) {
	exit_error('Error','Could Not Get Factory');
} elseif ($pg->isError()) {
	exit_error('Error',$pg->getErrorMessage());
}

if (!$func) {
	$func='browse';
}

if (session_loggedin()) {
	$perm =& $g->getPermission( session_get_user() );
}

/*
	Figure out which function we're dealing with here
*/
switch ($func) {

	//
	//	Show blank form to add new task
	//
	case 'addtask' : {
		if (session_loggedin() && $perm->isPMAdmin()) {
			$pt=new ProjectTaskHTML($pg);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}
			include 'add_task.php';
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Insert the task into the database
	//
	case 'postaddtask' : {
		if (session_loggedin() && $perm->isPMAdmin()) {
			$pt = new ProjectTask($pg);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get Empty ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}

			$start_date=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
			$end_date=mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);

			if (!$pt->create($summary,$details,$priority,$hours,$start_date,$end_date,$category_id,$percent_complete,$assigned_to,$dependent_on)) {
				exit_error('ERROR',$pt->getErrorMessage());
			} else {
				if (count($add_artifact_id) > 0) {
					if (!$pt->addRelatedArtifacts($add_artifact_id)) {
						exit_error('ERROR','addRelatedArtifacts():: '.$pt->getErrorMessage());
					}
				}
				$feedback='Task Created Successfully';
				include 'browse_task.php';
			}
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Modify an existing task
	//
	case 'postmodtask' : {
		if (session_loggedin() && $perm->isPMAdmin()) {
			$pt = new ProjectTask($pg,$project_task_id);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}

			$start_date=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
			$end_date=mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
			if (!$pt->update($summary,$details,$priority,$hours,$start_date,$end_date,$status_id,$category_id,$percent_complete,$assigned_to,$dependent_on)) {
				exit_error('ERROR','update():: '.$pt->getErrorMessage());
			} else {
				if (count($rem_artifact_id) > 0) {
					if (!$pt->removeRelatedArtifacts($rem_artifact_id)) {
						exit_error('ERROR','removeRelatedArtifacts():: '.$pt->getErrorMessage());
					}
				}
				$feedback='Task Updated Successfully';
				include 'browse_task.php';
			}
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Add an artifact relationship to an existing task
	//
	case 'addartifact' : {
		if (session_loggedin() && $perm->isPMAdmin()) {
			$pt = new ProjectTask($pg,$project_task_id);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}
			if (!$pt->addRelatedArtifacts($add_artifact_id)) {
				exit_error('ERROR','addRelatedArtifacts():: '.$pt->getErrorMessage());
			} else {
				$feedback='Successfully Added Tracker Relationship';
				include 'browse_task.php';

			}
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Simply browse existing tasks
	//
	case 'browse' : {
		include 'browse_task.php';
		break;
	}

	//
	//	Show the page surrounding the gantt chart
	//
	case 'ganttpage' : {
		include 'ganttpage.php';
		break;
	}

	//
	//	Show a gantt chart
	//
	case 'ganttchart' : {
		include 'gantt.php';
		break;
	}

	//
	//	View a specific existing task
	//
	case 'detailtask' : {
		$pt=new ProjectTaskHTML($pg,$project_task_id);
		if (!$pt || !is_object($pt)) {
			exit_error('Error','Could Not Get ProjectTask');
		} elseif ($pt->isError()) {
			exit_error('Error',$pt->getErrorMessage());
		}
		if (session_loggedin() && $perm->isPMAdmin()) {
			include 'mod_task.php';
		} else {
			include 'detail_task.php';
		}
		break;
	}

}

?>
