<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: pm_data.php,v 1.37 2000/12/09 19:54:14 tperdue Exp $

/**
 *	return a result set of the 100 most recent tasks in this subproject
 */
function pm_data_get_tasks ($group_project_id) {
	global $pm_data_tasks;
	if (!$pm_data_tasks["$group_project_id"]) {
		$sql="SELECT project_task_id,summary ".
			"FROM project_task ".
			"WHERE group_project_id='$group_project_id' ".
			"AND status_id <> '3' ORDER BY project_task_id DESC";
		$pm_data_tasks["$group_project_id"]=db_query($sql,100);
	}
	return $pm_data_tasks["$group_project_id"];
}

/**
 *	return a result set of subprojects for this group
 */
function pm_data_get_subprojects ($group_id) {
	global $pm_data_subprojects;
	if (!$pm_data_subprojects["$group_id"]) {
		$sql="SELECT group_project_id,project_name ".
			"FROM project_group_list WHERE group_id='$group_id'";
		$pm_data_subprojects["$group_id"]=db_query($sql);
	}
	return $pm_data_subprojects["$group_id"];
}

/**
 *	return a result set of tasks in this subproject that do not equal 
 *	the passed in task_id
 */
function pm_data_get_other_tasks ($group_project_id,$project_task_id) {
	$sql="SELECT project_task_id,summary ".
		"FROM project_task ".
		"WHERE group_project_id='$group_project_id' ".
		"AND status_id <> '3' ".
		"AND project_task_id <> '$project_task_id' ORDER BY project_task_id DESC";
	return db_query($sql,100);
}

/**
 *	return a result set of pm technicians in this group
 */
function pm_data_get_technicians ($group_id) {
	global $pm_data_technicians;
	if (!$pm_data_technicians["$group_id"]) {
		$sql="SELECT users.user_id,users.user_name ".
			"FROM users,user_group ".
			"WHERE users.user_id=user_group.user_id ".
			"AND user_group.group_id='$group_id' ".
			"AND user_group.project_flags IN (1,2) ".
			"ORDER BY users.user_name";
		$pm_data_technicians["$group_id"]=db_query($sql);
	}
	return $pm_data_technicians["$group_id"];
}

/**
 *	return result set of ids of tasks tasks that are dependent on this task
 */
function pm_data_get_dependent_tasks ($project_task_id) {
	$sql="SELECT is_dependent_on_task_id ".
		"FROM project_dependencies ".
		"WHERE project_task_id='$project_task_id'";
	return db_query($sql);
}

/**
 *	return result set of user_ids that are assigned this task
 */
function pm_data_get_assigned_to ($project_task_id) {
	$sql="SELECT assigned_to_id ".
		"FROM project_assigned_to ".
		"WHERE project_task_id='$project_task_id'";
	return db_query($sql);
}

/**
 *	return result set of statuses
 */
function pm_data_get_statuses () {
	global $pm_data_statuses;
	if (!$pm_data_statuses) {
		$sql='SELECT * FROM project_status';
		$pm_data_statuses=db_query($sql);
	}
	return $pm_data_statuses;
}

function pm_data_get_status_name($string) {
	/*
		simply return status_name from bug_status
	*/
	$sql="SELECT * FROM project_status WHERE status_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'status_name');
	} else {
		return 'Error - Not Found';
	}
}

function pm_data_get_group_name($group_project_id) {
	/*
		Simply return the resolution name for this id
	*/

	$sql="SELECT * FROM project_group_list WHERE group_project_id='$group_project_id'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'project_name');
	} else {
		return 'Error - Not Found';
	}
}

function pm_data_create_history ($field_name,$old_value,$project_task_id) {
	global $feedback;
	/*
		handle the insertion of history for these parameters
	*/
	$sql="insert into project_history(project_task_id,field_name,old_value,mod_by,date) ".
		"VALUES ('$project_task_id','$field_name','$old_value','".user_getid()."','".time()."')";
	$result=db_query($sql);
	if (!$result) {
		$feedback .= ' ERROR IN AUDIT TRAIL - '.db_error();
		return false;
	} else {
		return true;
	}
}

function pm_data_insert_assigned_to($array,$project_task_id) {
	global $feedback;
	/*
		Insert the people this task is assigned to
	*/
	$user_count=count($array);
	if ($user_count < 1) {
		//if no users selected, insert user "none"
		$sql="INSERT INTO project_assigned_to (project_task_id,assigned_to_id) 
			VALUES ('$project_task_id','100')";
		$result=db_query($sql);
		if (!$result) {
			return false;
		} else {
			return true;
		}
	} else {
		for ($i=0; $i<$user_count; $i++) {
			if (($user_count > 1) && ($array[$i]==100)) {
				//don't insert the row if there's more 
				//than 1 item selected and this item is the "none user"
			} else {
				$sql="INSERT INTO project_assigned_to (project_task_id,assigned_to_id) 
					VALUES ('$project_task_id','$array[$i]')";
				//echo "\n$sql";
				$result=db_query($sql);
				if (!$result) {
					$feedback .= ' ERROR inserting project_assigned_to '.db_error();
					return false;
				}
			}
		}
		return true;
	}
}

function pm_data_update_assigned_to($array,$project_task_id) {
	/*
		DELETE THEN Insert the people this task is assigned to
	*/
	$toss=db_query("DELETE FROM project_assigned_to WHERE project_task_id='$project_task_id'");
	return pm_data_insert_assigned_to($array,$project_task_id);
}

function pm_data_insert_dependent_tasks($array,$project_task_id) {
	global $feedback;
	/*
		Insert the list of dependencies
	*/
	$depend_count=count($array);
	if ($depend_count < 1) {
		//if no tasks selected, insert task "none"
		$sql="INSERT INTO project_dependencies (project_task_id,is_dependent_on_task_id) 
			VALUES ('$project_task_id','100')";
		$result=db_query($sql);
		if (!$result) {
			return false;
		} else {
			return true;
		}
	} else {
		for ($i=0; $i<$depend_count; $i++) {
			if (($depend_count > 1) && ($array[$i]==100)) {
				//don't insert the row if there's more
				//than 1 item selected and this item is the "none task"
			} else {
				$sql="INSERT INTO project_dependencies (project_task_id,is_dependent_on_task_id) 
					VALUES ('$project_task_id','$array[$i]')";
				//echo "\n$sql";
				$result=db_query($sql);
	
				if (!$result) {
					$feedback .= ' ERROR inserting dependent_tasks '.db_error();
					return false;
				}
			}
		}
		return true;
	}
}

function pm_data_update_dependent_tasks($array,$project_task_id) {
	/*
		DELETE THEN Insert the list of dependencies
	*/
	$toss=db_query("DELETE FROM project_dependencies WHERE project_task_id='$project_task_id'");
	return pm_data_insert_dependent_tasks($array,$project_task_id);
}

/*

	Creates a new task in the task mgr

	Returns new project_task_id or false and $feedback

	DOES NOT HANDLE SECURITY

*/

function pm_data_create_task ($group_project_id,$start_month,$start_day,$start_year,$end_month,$end_day,
		$end_year,$summary,$details,$percent_complete,$priority,$hours,$assigned_to,$dependent_on) {

	global $feedback;
	if (!$group_project_id || !$start_month || !$start_day || !$start_year || !$end_month || !$end_day || 
		!$end_year || !$summary || !$details || !$priority) {
		$feedback .= ' ERROR - Missing Required Parameters ';
		return false;
	}

	if (mktime(0,0,0,$start_month,$start_day,$start_year) > mktime(0,0,0,$end_month,$end_day,$end_year)) {
		exit_error('Error','End Date Must Be Greater Than Begin Date');
	}

	$sql="INSERT INTO project_task (group_project_id,summary,details,percent_complete,".
		"priority,hours,start_date,end_date,".
		"created_by,status_id) VALUES ('$group_project_id','".htmlspecialchars($summary)."',".
		"'".htmlspecialchars($details)."','$percent_complete','$priority','$hours','".
		mktime(0,0,0,$start_month,$start_day,$start_year)."','".
		mktime(0,0,0,$end_month,$end_day,$end_year)."','".user_getid()."','1')";

	db_begin();

	$result=db_query($sql);
	$project_task_id=db_insertid($result,'project_task','project_task_id');

	if (!$result || !$project_task_id) {
		$feedback .= ' ERROR INSERTING ROW '.db_error();
		db_rollback();
		return false;
	} else {
		$feedback .= ' Successfully added task ';
		if (!pm_data_insert_assigned_to($assigned_to,$project_task_id)) {
			db_rollback();
			$feedback .= ' ERROR inserting assigned to ';
			return false;
		}
		if (!pm_data_insert_dependent_tasks($dependent_on,$project_task_id)) {
			db_rollback();
			$feedback .= ' ERROR inserting assigned to ';
			return false;
		}
		mail_followup($project_task_id,$group_project_id,1);
		db_commit();
		return $project_task_id;
	}
}

/*

	Update a task

	Returns true/false and $feedback string

	DOES NOT HANDLE SECURITY AT THIS TIME *************
	This assumes that you have verified this $group_project_id truly belongs to this $group_id
	AND that this user is a project_task_admin

*/

function pm_data_update_task ($group_project_id,$project_task_id,$start_month,$start_day,$start_year,
		$end_month,$end_day,$end_year,$summary,$details,$percent_complete,$priority,$hours,
		$status_id,$assigned_to,$dependent_on,$new_group_project_id,$group_id) {

	global $feedback;
	if (!$group_project_id || !$project_task_id || !$status_id || !$start_month || !$start_day || !$start_year || 
		!$end_month || !$end_day || !$end_year || !$summary || !$priority || !$new_group_project_id || !$group_id) {
		$feedback .= ' ERROR - Missing Parameters ';
		return false;
	}
	$sql="SELECT * FROM project_task WHERE project_task_id='$project_task_id' AND group_project_id='$group_project_id'";

	$result=db_query($sql);

	if (db_numrows($result) < 1) {
		$feedback .= ' ERROR - Task Doesn\'t Exist In This Subproject ';
		return false;
	}

	/*
		Enforce start date > end date
	*/
	if (mktime(0,0,0,$start_month,$start_day,$start_year) > mktime(0,0,0,$end_month,$end_day,$end_year)) {
		$feedback .= ' ERROR - End Date Must Be Greater Than Start Date ';
		return false;
	}

	db_begin();

	/*
		If changing subproject, verify the new subproject belongs to this project
	*/
	if ($group_project_id != $new_group_project_id) {
		$sql = "SELECT group_id FROM project_group_list WHERE group_project_id='$new_group_project_id'";
		
		if (db_result(db_query($sql),0,'group_id') != $group_id) {
			$feedback .= ' You can not put this task into the subproject of another group. ';
			db_rollback();
			return false;
		} else {
			pm_data_create_history ('subproject_id',$group_project_id,$project_task_id);
		}
	}

	/*
		See which fields changed during the modification
		and create audit trail
	*/

	if (db_result($result,0,'status_id') != $status_id)
		{ pm_data_create_history ('status_id',db_result($result,0,'status_id'),$project_task_id);  }

	if (db_result($result,0,'priority') != $priority)
		{ pm_data_create_history ('priority',db_result($result,0,'priority'),$project_task_id);  }

	if (db_result($result,0,'summary') != htmlspecialchars(stripslashes($summary)))
		{ pm_data_create_history ('summary',addslashes(db_result($result,0,'summary')),$project_task_id);  }

	if (db_result($result,0,'percent_complete') != $percent_complete)
		{ pm_data_create_history ('percent_complete',db_result($result,0,'percent_complete'),$project_task_id);  }

	if (db_result($result,0,'hours') != $hours)
		{ pm_data_create_history ('hours',db_result($result,0,'hours'),$project_task_id);  }

	if (db_result($result,0,'start_date') != mktime(0,0,0,$start_month,$start_day,$start_year))
		{ pm_data_create_history ('start_date',db_result($result,0,'start_date'),$project_task_id);  }

	if (db_result($result,0,'end_date') != mktime(0,0,0,$end_month,$end_day,$end_year))
		{ pm_data_create_history ('end_date',db_result($result,0,'end_date'),$project_task_id);  }

	/*
		Details field is handled a little differently

		Details are comments attached to bugs
		They are still stored in the project_history (audit trail)
		system, but they are not shown in the regular audit trail

		Someday, these should technically be split into their own table.
	*/
	if ($details != '') 
		{ pm_data_create_history ('details',htmlspecialchars($details),$project_task_id);  }

	if (!pm_data_update_dependent_tasks($dependent_on,$project_task_id)) {
		db_rollback();
		$feedback .= ' ERROR updating dependent tasks ';
		return false;
	}
	if (!pm_data_update_assigned_to($assigned_to,$project_task_id)) {
		db_rollback();
		$feedback .= ' ERROR updating assigned to ';
		return false;
	}

	/*
		Update the actual db record
	*/
	$sql="UPDATE project_task SET status_id='$status_id', priority='$priority',".
		"summary='".htmlspecialchars($summary)."',start_date='".
		mktime(0,0,0,$start_month,$start_day,$start_year)."',end_date='".
		mktime(0,0,0,$end_month,$end_day,$end_year)."',hours='$hours',".
		"percent_complete='$percent_complete', ".
		"group_project_id='$new_group_project_id' ".
		"WHERE project_task_id='$project_task_id' AND group_project_id='$group_project_id'";

	$result=db_query($sql);
	if (!$result) {
		$feedback .= ' ERROR - Database Update Failed '.db_error();
		db_rollback();
		return false;
	} else {
		$feedback .= ' Successfully Modified Task ';
		mail_followup($project_task_id,$new_group_project_id);
		db_commit();
		return true;
	}

}

function mail_followup($project_task_id,$group_project_id,$more_addresses=false,$new_task=0) {
	global $sys_datefmt,$feedback;
	/*
	
		Send a message to the person who opened this task and the person(s) it is assigned to

		Accepts the unique id of a task, its group project id and optionally a list of additional addresses to send to

	*/

	$sql="SELECT project_task.*, project_group_list.*, groups.group_name,groups.new_task_address, ".
		"groups.send_all_tasks,project_status.status_name,users.email, ".
                "users.user_name AS creator_name ".
		"FROM project_task,project_group_list,project_status,users,groups ".
		"WHERE project_task_id='$project_task_id' ".
		"AND project_task.group_project_id='$group_project_id' ".
		"AND project_task.status_id=project_status.status_id ".
		"AND project_task.group_project_id=project_group_list.group_project_id ".
		"AND groups.group_id=project_group_list.group_id ".
		"AND project_task.created_by=users.user_id";
		
	$result=db_query($sql);
	
	if ($result && db_numrows($result) > 0) {
	
		// Send a message to the task creator
		$to = db_result($result,0,'email');

		// Build the list of developers assigned this task
		$sql="SELECT users.email AS Email,users.user_name ".
			"FROM users,project_assigned_to ".
			"WHERE project_assigned_to.project_task_id='$project_task_id' ".
			"AND users.user_id=project_assigned_to.assigned_to_id";

		$result3=db_query($sql);
		$rows=db_numrows($result3);
		if ($result3 && $rows > 0) {
			$to .= implode(result_column_to_array($result3),', ');
		}

		$body = "Task #".db_result($result,0,"project_task_id")." has been updated. ".
			"\n\nProject: ".db_result($result,0,'group_name').
			"\nSubproject: ".db_result($result,0,'project_name').
			"\nSummary: ".util_unconvert_htmlspecialchars(db_result($result,0,'summary')).
			"\nPercent Complete: ".db_result($result,0,'percent_complete')."%".
			"\nStatus: ".db_result($result,0,'status_name').
			"\nAuthority  : ".db_result($result,0,'creator_name').
			"\nAssigned to: ".$assignees.
			"\n\nDescription: ".db_result($result,0,'details');
			
		/*      
			Now get the followups to this task
		*/
		$sql="SELECT project_history.field_name,project_history.old_value,project_history.date,users.user_name ".
			"FROM project_history,users ".
			"WHERE project_history.mod_by=users.user_id AND project_history.field_name = 'details' ".
			"AND project_task_id='$project_task_id' ORDER BY project_history.date DESC";
		$result2=db_query($sql);
		$rows=db_numrows($result2);
		if ($result2 && $rows > 0) {
			$body .= "\n\nFollow-Ups:";
			for ($i=0; $i<$rows;$i++) {
				$body .= "\n\n-------------------------------------------------------";
				$body .= "\nDate: ".date($sys_datefmt,db_result($result2,$i,'date'));
				$body .= "\nBy: ".db_result($result2,$i,'user_name');
				$body .= "\n\nComment:\n".util_unconvert_htmlspecialchars(db_result($result2,$i,'old_value'));
			}
		}
		$body .= "\n\n-------------------------------------------------------".
			"\nFor more info, visit:".
			"\n\nhttp://$GLOBALS[sys_default_domain]/pm/task.php?func=detailtask&project_task_id=".
				db_result($result,0,'project_task_id')."&group_id=".
				db_result($result,0,'group_id')."&group_project_id=".db_result($result,0,'group_project_id');
		
		$subject="[Task #".db_result($result,0,'project_task_id').'] '.
			util_unconvert_htmlspecialchars(db_result($result,0,'summary'));
		
		
		// Append the list of additional receiptients
		if ($more_addresses) {
			$to .= ', ' . $more_addresses;
		}
		
		// If this is a new task, or if send all tasks == 1,
		// append the new_task_address for the group
		if (($new_task && db_result($result,0,'new_task_address')) || db_result($result,0,'send_all_tasks')) {
			$to .= ', ' . db_result($result,0,'new_task_address');
		}
		
		$more='From: noreply@'.$GLOBALS['sys_default_domain'];
		
		mail($to,$subject,$body,$more);
		
		$feedback .= " Task Update Sent ";
		
	} else {	
	
		$feedback .= " Could Not Send Task Update ";
		echo db_error();
		
	}       
}       

?>
