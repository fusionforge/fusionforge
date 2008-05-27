#! /usr/bin/php5 -f
<?php
/**
 * Send emails to users with open tasks
 *
 * Copyright 2004 GForge, LLC
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once $gfwww.'include/squal_pre.php';
require_once $gfcommon.'pm/ProjectTasksForUser.class.php';
require_once $gfcommon.'include/cron_utils.php';

//
//  Set up this script to run as the site admin
//
$res = db_query("SELECT user_id FROM user_group WHERE admin_flags='A' AND group_id='1'");

if (!$res) {
    echo db_error();
    exit(1);
}
if (db_numrows($res) == 0) {
    exit(0);
}
$id=db_result($res,0,0);
session_set_new($id);

// Get user id's from users who have open tasks
$res = db_query("SELECT DISTINCT u.user_id, u.realname, u.email FROM users u, project_assigned_to pat, project_task_vw ptv 
		WHERE u.user_id > 100 AND u.user_id=pat.assigned_to_id AND pat.project_task_id=ptv.project_task_id 
		AND ptv.status_id=1 ORDER BY u.user_id;");

$now = time();
$today = date("n/j/y");

// for every user retrieved, get its open tasks and send an email
for ($i=0; $i<db_numrows($res);$i++) {

	$user_id = db_result($res, $i, 'user_id');
	$realname = db_result($res, $i, 'realname');
	$email = db_result($res, $i, 'email');

	// get an object of the User with the current user_id
	$user_object = &user_get_object($user_id);
	if (!$user_object || !is_object($user_object)) {
		$err .= "Could not get User object with ID: $user_id\n";
	} else {
		$projectTasksForUser = new ProjectTasksForUser($user_object);

		if (!$projectTasksForUser || !is_object($projectTasksForUser)) {
			$err .= "Could not get ProjectTasksForUser object for user with ID: $user_id\n";
			continue;
		}

		// get the tasks the user should work on, today
		$userTasks =& $projectTasksForUser->getTasksForToday();
		$last_group = 0;
		$last_projectgroup = 0;

		// start composing the email	
		$subject = 'Tasks for '.$realname.' for '.$today;

		if (count($userTasks) > 0) {
			$valid_tasks = 0;
			$debug_info = "************ DEBUG FOR USER: $user_id ************\n";
			ob_start();
			// get the data of every task and compose the email
			foreach ($userTasks as $task) {
				if ($task->getPercentComplete() == 100) {
					$debug_info .= "Task ID: ".$task->getID()." complete (100%)\n------------\n";
					continue;
				}
				$debug_info .= 'Task ID: ' . $task->getID() . "\n";

				$end_date = date("n/j/y", $task->getEndDate());

				$projectGroup =& $task->getProjectGroup();
				if ($projectGroup && is_object($projectGroup)) {

					$debug_info .= 'Project Group ID: ' . $projectGroup->getID() . "\n";

					$group =& $projectGroup->getGroup();
					if ($group && is_object($group)) {

						$debug_info .= 'Group ID: ' . $group->getID() . "\n";

						if ($group->getID() != $last_group) {
							echo $group->getPublicName().":\n";
						}
						if ($projectGroup->getID() != $last_projectgroup) {
							echo $projectGroup->getName().":\n";
						}
						echo html_entity_decode($task->getSummary()).":\n";
						echo '***'.
						(($now>$task->getEndDate())? 'overdue' : "due $end_date").
						"***\n";
						echo util_make_url ('/pm/task.php?func=detailtask&project_task_id='.
								    $task->getID().'&group_id='.$group->getID().'&group_project_id='.$projectGroup->getID());
						echo "\n\n";

						$last_group = $group->getID();
						$last_projectgroup = $projectGroup->getID();
						$valid_tasks++;
					}
				}
				$debug_info .= "------------\n";
			}
			$messagebody = ob_get_contents();
			ob_end_clean();
			if ($valid_tasks > 0) {
				util_send_message($email, $subject, $messagebody);
			}
			/* else {
				echo $debug_info."\n";
			}*/
		}
	}
}

cron_entry(22,$err);
?>
