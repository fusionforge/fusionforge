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
	exit_missing_param();
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
				$feedback=$Language->getText('pm_addtask','task_created_successfully');
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
			if (!$pt->update($summary,$details,$priority,$hours,$start_date,$end_date,
				$status_id,$category_id,$percent_complete,$assigned_to,$dependent_on,$new_group_project_id)) {
				exit_error('ERROR','update():: '.$pt->getErrorMessage());
			} else {
				if (count($rem_artifact_id) > 0) {
					if (!$pt->removeRelatedArtifacts($rem_artifact_id)) {
						exit_error('ERROR','removeRelatedArtifacts():: '.$pt->getErrorMessage());
					}
				}
				$feedback=$Language->getText('pm_addtask','task_updated_successfully');
				include 'browse_task.php';
			}
		} else {
			exit_permission_denied();
		}
		break;
	}

	case 'massupdate' : {
		$count=count($project_task_id_list);

		if (session_loggedin() && $perm->isPMAdmin()) {

			for ($i=0; $i < $count; $i++) {
				$pt=new ProjectTask($pg,$project_task_id_list[$i]);
				if (!$pt || !is_object($pt)) {
					$feedback .= ' ID: '.$project_task_id_list[$i].'::ProjectTask Could Not Be Created';
				} else if ($pt->isError()) {
					$feedback .= ' ID: '.$project_task_id_list[$i].'::'.$pt->getErrorMessage();
				} else {

					$_summary=addslashes($pt->getSummary());
					$_details='';
					$_priority=(($priority != 100) ? $priority : $pt->getPriority());
					$_hours=$pt->getHours();
					$_start_date=$pt->getStartDate();
					$_end_date=$pt->getEndDate();
					$_status_id=(($status_id != 100) ? $status_id : $pt->getStatusID());
					$_category_id=(($category_id != 100) ? $category_id : $pt->getCategoryID());
					$_percent_complete=$pt->getPercentComplete();
					//yikes, we want the ability to mass-update to "un-assigned", which is the ID=100, which
					//conflicts with the "no change" ID! Sorry for messy use of 100.1
					$_assigned_to=(($assigned_to != '100.1') ? $pt->getAssignedTo() : array('100'));
					$_dependent_on=$pt->getDependentOn();
					$_new_group_project_id=(($new_group_project_id != 100) ? $new_group_project_id : $pt->ProjectGroup->getID() );

					if (!$pt->update($_summary,$_details,$_priority,$_hours,$_start_date,$_end_date,
							$_status_id,$_category_id,$_percent_complete,$_assigned_to,$_dependent_on,$_new_group_project_id)) {
						$was_error=true;
						$feedback .= ' ID: '.$project_task_id_list[$i].'::'.$pt->getErrorMessage();

					}
					unset($pt);
				}
			}
			if (!$was_error) {
				$feedback = $Language->getText('pm_addtask','task_updated_successfully');
			}
			include 'browse_task.php';
			break;
		} else {
			exit_permission_denied();
		}

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
				$feedback=$Language->getText('pm_addtask','task_added_relationship');
				include 'browse_task.php';

			}
		} else {
			exit_permission_denied();
		}
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

	default : {
		include 'browse_task.php';
		break;
	}


}

?>
