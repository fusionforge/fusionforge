#! /usr/bin/php
<?php
//ruben - it's missing the copyright at the top
require_once('squal_pre.php');
require_once('common/pm/ProjectTasksForUser.class');
require_once('common/include/cron_utils.php');

//ruben - query should include a join against project_task so you only fetch users that have open tasks
$res = db_query("SELECT user_id, realname, email FROM users WHERE user_id > 100 ORDER BY user_id");
$now = time();
$today = date("n/j/y");

//ruben, no comments
for ($i=0; $i<db_numrows($res);$i++) {

	$user_id = db_result($res, $i, 'user_id');
	$realname = db_result($res, $i, 'realname');
	$email = db_result($res, $i, 'email');
	$user_object = &user_get_object($user_id);

	if (!$user_object || !is_object($user_object)) {
		$err .= "Could not get User object with ID: $user_id\n";
	} else {

		$projectTasksForUser = new ProjectTasksForUser($user_object);
		if (!$projectTasksForUser || !is_object($projectTasksForUser) || $projectTasksForUser->isError()) {
			continue;
		}
		$userTasks =& $projectTasksForUser->getTasksForToday();
	
		$subject = 'Tasks for '.$realname.' for '.$today;
	
		if (count($userTasks) > 0) {
	
			ob_start();

			foreach ($userTasks as $task) {
	
				$end_date = date("n/j/y", $task->getEndDate());
	
				$projectGroup =& $task->getProjectGroup();
//No error checks
				$group =& $projectGroup->getGroup();
//No error checks
				if ($group->getID() != $last_group) {
					echo $group->getPublicName().":\n";
				}
				if ($projectGroup->getID() != $last_projectgroup) {
					echo $projectGroup->getName().":\n";
				}
				echo $task->getSummary().":\n";
				echo '***'.
				(($now>$task->getEndDate())? 'overdue' : "due $end_date").
				"***\n";
				echo 'http://'.$sys_default_domain.'/pm/task.php?func=detailtask&project_task_id='.
				$task->getID().'&group_id='.$group->getID().'&group_project_id='.$projectGroup->getID();
				echo "\n\n";
	
				$last_group = $group->getID();
				$last_projectgroup = $projectGroup->getID();
			}
	
			$messagebody = ob_get_contents();
			ob_end_clean();
//			util_send_message($email, $subject, $messagebody);
			echo "\n\n\n***************************************************\n$messagebody";
		}
	}
}

cron_entry(22,$err);
?>
