<?php
/**
  *
  * SourceForge Project/Task Manager (PM)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/pm/pm_utils.php');
require_once('common/pm/pm_data.php');

if ($group_id && $group_project_id) {

	$project=&group_get_object($group_id);

	if (user_isloggedin()) {
		$perm =& $project->getPermission( session_get_user() );
	}

	/*
		Verify that this group_project_id falls under this group
	*/

	//can this person view these tasks? they may have hacked past the /pm/index.php page
	if (user_isloggedin() && $perm->isMember()) {
		$public_flag='0,1';
	} else {
		$public_flag='1';
	}

	/*
		Verify that this subproject belongs to this project
	*/
	$result=db_query("SELECT * FROM project_group_list ".
		"WHERE group_project_id='$group_project_id' AND group_id='$group_id' AND is_public IN ($public_flag)");
	if (db_numrows($result) < 1) {
		exit_permission_denied();
	}

	if (!$func) {
		$func='browse';
	}
	/*
		Figure out which function we're dealing with here
	*/

	switch ($func) {

		case 'addtask' : {
			if (user_isloggedin() && $perm->isPMAdmin()) {
				include '../pm/add_task.php';
			} else {
				exit_permission_denied();
			}
			break;;
		}

		case 'postaddtask' : {
			if (user_isloggedin() && $perm->isPMAdmin()) {
				if (pm_data_create_task ($group_project_id,$start_month,$start_day,$start_year,
					$start_hour,$start_minute,$end_month,$end_day,$end_year,
					$end_hour,$end_minute,$summary,$details,$percent_complete,
					$priority,$hours,$assigned_to,$dependent_on)) {
					$feedback='Task Created Successfully';
					include '../pm/browse_task.php';
				} else {
					exit_error('ERROR',$feedback);
				}
			} else {
				exit_permission_denied();
			}
			break;;
		}

		case 'postmodtask' : {
			if (user_isloggedin() && $perm->isPMAdmin()) {
				if (pm_data_update_task ($group_project_id,$project_task_id,$start_month,$start_day,
					$start_year,$start_hour,$start_minute,$end_month,$end_day,$end_year,$end_hour,
					$end_minute,$summary,$details,$percent_complete,$priority,$hours,$status_id,
					$assigned_to,$dependent_on,$new_group_project_id,$group_id)) {
					$feedback='Task Updated Successfully';
					include '../pm/browse_task.php';
				} else {
					exit_error('ERROR',$feedback);
				}
				break;;
			} else {
				exit_permission_denied();
			}
		}

		case 'browse' : {
			include '../pm/browse_task.php';
			break;;
		}

		case 'detailtask' : {
			if (user_isloggedin() && $perm->isPMAdmin()) {
				include '../pm/mod_task.php';
			} else {
				include '../pm/detail_task.php';
			}
			break;;
		}

	}

} else {
	//browse for group first message
	if (!$group_id || !$group_project_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}
}
?>
