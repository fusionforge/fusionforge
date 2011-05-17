<?php
/**
 * FusionForge Project Management Facility : Tasks
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'pm/include/ProjectGroupHTML.class.php';
require_once $gfwww.'pm/include/ProjectTaskHTML.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';

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
$status_id = getIntFromRequest('status_id');
$category_id = getIntFromRequest('category_id');
$percent_complete = getStringFromRequest('percent_complete');
$assigned_to = getStringFromRequest('assigned_to');
$new_group_project_id = getIntFromRequest('new_group_project_id');
$dependent_on = getStringFromRequest('dependent_on');
$duration = getStringFromRequest('duration');
$parent_id = getIntFromRequest('parent_id');



if (!$group_id || !$group_project_id) {
    $redirect_url = '';
    if (isset($_SERVER['HTTP_REFERER']))
        $redirect_url = $_SERVER['HTTP_REFERER'];

    if (!$group_id)
        $missing_params[] = _('Group ID');

    if (!$group_project_id)
        $missing_params[] = _('Group Project ID');

	exit_missing_param($redirect_url,$missing_params,'pm');
}

$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error($g->getErrorMessage(),'pm');
}

$pg = new ProjectGroupHTML($g,$group_project_id);
if (!$pg || !is_object($pg)) {
	exit_error(_('Could Not Get Factory'),'pm');
} elseif ($pg->isError()) {
	exit_error($pg->getErrorMessage(),'pm');
}

/*
	Figure out which function we're dealing with here
*/
switch (getStringFromRequest('func')) {

	//
	//	Show blank form to add new task
	//
	case 'addtask' : {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		$pt=new ProjectTaskHTML($pg);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		include $gfwww.'pm/add_task.php';
		break;
	}

	//
	//	Insert the task into the database
	//
	case 'postaddtask' : {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		$add_artifact_id = getIntFromRequest('add_artifact_id');
		
		$pt = new ProjectTask($pg);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get Empty ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		
		$saved_hours = $hours;
		$hours = (float) $hours;
		if ( $saved_hours !== (string)$hours ) {
			exit_error(_('Illegal format for hours: must be an integer or a float number.'),'pm');
		}
		
		if (!$dependent_on)
		{
			$dependent_on=array();
		}
		$start_date=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
		$end_date=mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
		
		
		$sanitizer = new TextSanitizer();
		$details = $sanitizer->purify($details);

		if (!$pt->create($summary,$details,$priority,$hours,$start_date,$end_date,$category_id,$percent_complete,$assigned_to,$pt->convertDependentOn($dependent_on),$duration,$parent_id)) {
			exit_error($pt->getErrorMessage(),'pm');
		} else {
			if (count($add_artifact_id) > 0) {
				if (!$pt->addRelatedArtifacts($add_artifact_id)) {
					exit_error('addRelatedArtifacts():: '.$pt->getErrorMessage(),'pm');
				}
			}
			$feedback=_('Task Created Successfully');
			include $gfwww.'pm/browse_task.php';
		}
		break;
	}

	//
	//	Modify an existing task
	//
	case 'postmodtask' : {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		$rem_artifact_id = getIntFromRequest('rem_artifact_id');
		
		if(!$rem_artifact_id){
			$rem_artifact_id=array();
		}
		
		$pt = new ProjectTask($pg,$project_task_id);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		
		$saved_hours = $hours;
		$hours = (float) $hours;
		if ( $saved_hours !== (string)$hours ) {
			exit_error(_('Illegal format for hours: must be an integer or a float number.'),'pm');
		}
		
		if (!$dependent_on)	{
			$dependent_on=array();
		}
		$start_date=mktime($start_hour,$start_minute,0,$start_month,$start_day,$start_year);
		$end_date=mktime($end_hour,$end_minute,0,$end_month,$end_day,$end_year);
		if (!$pt->update($summary,$details,$priority,$hours,$start_date,$end_date,
				 $status_id,$category_id,$percent_complete,$assigned_to,$pt->convertDependentOn($dependent_on),$new_group_project_id,$duration,$parent_id)) {
			exit_error('update():: '.$pt->getErrorMessage(),'pm');
		} else {
			if (count($rem_artifact_id) > 0) {
				if (!$pt->removeRelatedArtifacts($rem_artifact_id)) {
					exit_error('removeRelatedArtifacts():: '.$pt->getErrorMessage(),'pm');
				}
			}
			$feedback=_('Task Updated Successfully');
			include $gfwww.'pm/browse_task.php';
		}
		break;
	}

	case 'csv': {
		include $gfwww.'pm/csv.php';
		exit;
	}

	case 'format_csv': {
		include $gfwww.'pm/format_csv.php';
		exit;
	}

	case 'downloadcsv': {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		include $gfwww.'pm/downloadcsv.php';
		exit;
	}

	case 'uploadcsv': {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		include $gfwww.'pm/uploadcsv.php';
		exit;
	}

	case 'postuploadcsv': {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		include $gfwww.'pm/postuploadcsv.php';
		include $gfwww.'pm/browse_task.php';
		break;
	}

	case 'massupdate' : {
		$project_task_id_list = getArrayFromRequest('project_task_id_list');
		$count=count($project_task_id_list);
	
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		for ($i=0; $i < $count; $i++) {
			$pt=new ProjectTask($pg,$project_task_id_list[$i]);
			if (!$pt || !is_object($pt)) {
				$error_msg .= ' ID: '.$project_task_id_list[$i].'::ProjectTask Could Not Be Created';
			} else if ($pt->isError()) {
				$error_msg .= ' ID: '.$project_task_id_list[$i].'::'.$pt->getErrorMessage();
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
		if ($count == 0) {
			$warning_msg = _('No task selected');
		}
		elseif (isset($was_error) && !$was_error) {
			$feedback = _('Task Updated Successfully');
		}
		include $gfwww.'pm/browse_task.php';
		break;
	}

	//
	//	Add an artifact relationship to an existing task
	//
	case 'addartifact' : {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		$add_artifact_id[] = getIntFromRequest('add_artifact_id');
		
		$pt = new ProjectTask($pg,$project_task_id);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		if (!$pt->addRelatedArtifacts($add_artifact_id)) {
			exit_error('addRelatedArtifacts():: '.$pt->getErrorMessage(),'pm');
		} else {
			$feedback=_('Successfully Added Tracker Relationship');
			include $gfwww.'pm/browse_task.php';
			
		}
		break;
	}

	//
	//	Show delete form
	//
	case 'deletetask' : {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		$pt= new ProjectTask($pg,$project_task_id);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		include $gfwww.'pm/deletetask.php';
		break;
	}

	//
	//	Handle the actual delete
	//

	case 'postdeletetask' : {
		session_require_perm ('pm', $pg->getID(), 'manager') ;

		$pt= new ProjectTask($pg, $project_task_id);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		if (!getStringFromRequest('confirm_delete')) {
			$warning_msg.= _('Confirmation failed. Task not deleted');
		} else {
			$deletion = $pt->delete(true);
			if (!$deletion) {
				$error_msg .= _('Delete failed') . ': '.$pt->getErrorMessage();
			} else {
				$feedback .= _('Task Successfully Deleted');
			}
		}
		include $gfwww.'pm/browse_task.php';
		break;
	}

	//
	//	Show the page surrounding the gantt chart
	//
	case 'ganttpage' : {
		include $gfwww.'pm/ganttpage.php';
		break;
	}

	//
	//	Show a gantt chart
	//
	case 'ganttchart' : {
		include $gfwww.'pm/gantt.php';
		break;
	}

	//
	//	View a specific existing task
	//
	case 'detailtask' : {
		$pt=new ProjectTaskHTML($pg,$project_task_id);
		if (!$pt || !is_object($pt)) {
			exit_error(_('Could Not Get ProjectTask'),'pm');
		} elseif ($pt->isError()) {
			exit_error($pt->getErrorMessage(),'pm');
		}
		if (forge_check_perm ('pm', $pg->getID(), 'manager')) {
			include $gfwww.'pm/mod_task.php';
		} else {
			include $gfwww.'pm/detail_task.php';
		}
		break;
	}

	default : {
		include $gfwww.'pm/browse_task.php';
		break;
	}
}
?>
