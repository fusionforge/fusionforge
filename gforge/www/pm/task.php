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

require_once('../env.inc.php');
require_once('pre.php');
require_once('www/pm/include/ProjectGroupHTML.class.php');
require_once('www/pm/include/ProjectTaskHTML.class.php');
require_once('common/pm/ProjectGroupFactory.class.php');

$group_id = getIntFromRequest('group_id');
$group_project_id = getIntFromRequest('group_project_id');
$project_task_id = getIntFromRequest('project_task_id');
$start_hour = getStringFromRequest('start_hour');
$start_minute = getStringFromRequest('start_minute');
$start_month = getStringFromRequest('start_month');
$start_day = getStringFromRequest('start_day');
$start_year = getStringFromRequest('start_year');
$end_hour = getStringFromRequest('end_hour');
$end_minute = getStringFromRequest('end_minute');
$end_month = getStringFromRequest('end_month');
$end_day = getStringFromRequest('end_day');
$end_year = getStringFromRequest('end_year');
$summary = getStringFromRequest('summary');
$details = getStringFromRequest('details');
$priority = getStringFromRequest('priority');
$hours = getStringFromRequest('hours');
$start_date = getStringFromRequest('start_date');
$end_date = getStringFromRequest('end_date');
$status_id = getStringFromRequest('status_id');
$category_id = getStringFromRequest('category_id');
$percent_complete = getStringFromRequest('percent_complete');
$assigned_to = getStringFromRequest('assigned_to');
$new_group_project_id = getIntFromRequest('new_group_project_id');
$dependent_on = getStringFromRequest('dependent_on');
$duration = getStringFromRequest('duration');
$parent_id = getIntFromRequest('parent_id');



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

/*
	Figure out which function we're dealing with here
*/
switch (getStringFromRequest('func')) {

	//
	//	Show blank form to add new task
	//
	case 'addtask' : {
		if ($pg->userIsAdmin()) {
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
		if ($pg->userIsAdmin()) {
			
			$add_artifact_id = getStringFromRequest('add_artifact_id');
						
			$pt = new ProjectTask($pg);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get Empty ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}

			if (!$dependent_on)
			{
				$dependent_on=array();
			}
			$start_date=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
			$end_date=mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
			
			
			if (!$pt->create($summary,$details,$priority,$hours,$start_date,$end_date,$category_id,$percent_complete,$assigned_to,$pt->convertDependentOn($dependent_on),$duration,$parent_id)) {
				exit_error('ERROR',$pt->getErrorMessage());
			} else {
				if (count($add_artifact_id) > 0) {
					if (!$pt->addRelatedArtifacts($add_artifact_id)) {
						exit_error('ERROR','addRelatedArtifacts():: '.$pt->getErrorMessage());
					}
				}
				$feedback=_('Task Created Successfully');
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
		if ($pg->userIsAdmin()) {
			$rem_artifact_id = getStringFromRequest('rem_artifact_id');
		
			if(!$rem_artifact_id){
				$rem_artifact_id=array();
			}
		
			$pt = new ProjectTask($pg,$project_task_id);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}

			if (!$dependent_on)	{
				$dependent_on=array();
			}
			$start_date=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
			$end_date=mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
			if (!$pt->update($summary,$details,$priority,$hours,$start_date,$end_date,
				$status_id,$category_id,$percent_complete,$assigned_to,$pt->convertDependentOn($dependent_on),$new_group_project_id,$duration,$parent_id)) {
				exit_error('ERROR','update():: '.$pt->getErrorMessage());
			} else {
				if (count($rem_artifact_id) > 0) {
					if (!$pt->removeRelatedArtifacts($rem_artifact_id)) {
						exit_error('ERROR','removeRelatedArtifacts():: '.$pt->getErrorMessage());
					}
				}
				$feedback=_('Task Updated Successfully');
				include 'browse_task.php';
			}
		} else {
			exit_permission_denied();
		}
		break;
	}

	case 'downloadcsv': {

		if ($pg->userIsAdmin()) {
			include 'downloadcsv.php';
			exit;
		} else {
			exit_permission_denied();
		}

	}

	case 'uploadcsv': {

		if ($pg->userIsAdmin()) {
			include 'uploadcsv.php';
			exit;
		} else {
			exit_permission_denied();
		}

	}

	case 'postuploadcsv': {

		if ($pg->userIsAdmin()) {			
			include 'postuploadcsv.php';
		} else {
			exit_permission_denied();
		}

	}

	case 'massupdate' : {
		$project_task_id_list = getStringFromRequest('project_task_id_list');
		$count=count($project_task_id_list);
	
		if ($pg->userIsAdmin()) {

			for ($i=0; $i < $count; $i++) {
				$pt=new ProjectTask($pg,$project_task_id_list[$i]);
				if (!$pt || !is_object($pt)) {
					$feedback .= ' ID: '.$project_task_id_list[$i].'::ProjectTask Could Not Be Created';
				} else if ($pt->isError()) {
					$feedback .= ' ID: '.$project_task_id_list[$i].'::'.$pt->getErrorMessage();
				} else {

					$mass_summary=addslashes(util_unconvert_htmlspecialchars($pt->getSummary()));
					$mass_details='';
					$mass_priority=(($priority != 100) ? $priority : $pt->getPriority());
					$mass_hours=$pt->getHours();
					$mass_start_date=$pt->getStartDate();
					$mass_end_date=$pt->getEndDate();
					$mass_status_id=(($status_id != 100) ? $status_id : $pt->getStatusID());
					$mass_category_id=(($category_id != 100) ? $category_id : $pt->getCategoryID());
					$mass_percent_complete=$pt->getPercentComplete();

					//yikes, we want the ability to mass-update to "un-assigned", which is the ID=100, which
					//conflicts with the "no change" ID! Sorry for messy use of 100.1
					// 100 means => no change
					// 100.1 means non assigned
					// other means assigned to ...

					if ($assigned_to == '100') {
					    $mass_assigned_to = $pt->getAssignedTo();
					} else if ($assigned_to == '100.1') {
					    $mass_assigned_to = array('100');
					} else {
					    $mass_assigned_to = array($assigned_to);
					} 			

					$mass_dependent_on=$pt->getDependentOn();
					$mass_new_group_project_id=(($new_group_project_id != 100) ? $new_group_project_id : $pt->ProjectGroup->getID() );
					$mass_duration=$pt->getDuration();
					$mass_parent_id=$pt->getParentID();

					if (!$pt->update($mass_summary,$mass_details,$mass_priority,$mass_hours,$mass_start_date,$mass_end_date,
							$mass_status_id,$mass_category_id,$mass_percent_complete,$mass_assigned_to,$mass_dependent_on,$mass_new_group_project_id,$mass_duration,$mass_parent_id)) {
						$was_error=true;
						$feedback .= ' ID: '.$project_task_id_list[$i].'::'.$pt->getErrorMessage();

					}
					unset($pt);
				}
			}
			if (!$was_error) {
				$feedback = _('Task Updated Successfully');
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
		if ($pg->userIsAdmin()) {
			$add_artifact_id = getStringFromRequest('add_artifact_id');

			$pt = new ProjectTask($pg,$project_task_id);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}
			if (!$pt->addRelatedArtifacts($add_artifact_id)) {
				exit_error('ERROR','addRelatedArtifacts():: '.$pt->getErrorMessage());
			} else {
				$feedback=_('Successfully Added Tracker Relationship');
				include 'browse_task.php';

			}
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Show delete form
	//
	case 'deletetask' : {
		if ($pg->userIsAdmin()) {
			$pt= new ProjectTask($pg,$project_task_id);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error',$pt->getErrorMessage());
			}
			include 'deletetask.php';
		} else {
			exit_permission_denied();
		}
		break;
	}

	//
	//	Handle the actual delete
	//

	case 'postdeletetask' : {
		if ($pg->userIsAdmin()) {
			$pt= new ProjectTask($pg, $project_task_id);
			if (!$pt || !is_object($pt)) {
				exit_error('Error','Could Not Get ProjectTask');
			} elseif ($pt->isError()) {
				exit_error('Error', $pt->getErrorMessage());
			}
			if (!getStringFromRequest('confirm_delete')) {
				$feedback .= _('Confirmation failed. Task not deleted');
			} else {
				$deletion = $pt->delete(true);
				if (!$deletion) {
					$feedback .= _('Delete failed') . ': '.$pt->getErrorMessage();
				} else {
					$feedback .= _('Task Successfully Deleted');
				}
			}
			include 'browse_task.php';
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
		if (session_loggedin() && $pg->userIsAdmin()) {
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
