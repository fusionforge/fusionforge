#! /usr/bin/php
<?php
require_once('squal_pre.php');
require_once('common/pm/ProjectTasksForUser.class');
require_once('common/include/cron_utils.php');

$res = db_query("SELECT user_id, realname, email FROM users WHERE user_id > 100 ORDER BY user_id");
$now = time();
$today = date("n/j/y");

for ($i=0; $i<db_numrows($res);$i++) {

	$user_id = db_result($res, $i, 'user_id');
	$realname = db_result($res, $i, 'realname');
	$email = db_result($res, $i, 'email');
	$user_object = &user_get_object($user_id);

	if (!$user_object || !is_object($user_object)) {
		$err .= "Could not get User object with ID: $user_id\n";
	}
	else {

		$projectTasksForUser = new ProjectTasksForUser($user_object);
		$userTasks =& $projectTasksForUser->getTasksForToday();
	
		$subject = 'Tasks for '.$realname.' for '.$today;
	
		if (count($userTasks) > 0) {
	
			ob_start();
	
			foreach ($userTasks as $task) {
	
				$end_date = date("n/j/y", $task->getEndDate());
	
				$projectGroup =& $task->getProjectGroup();
				$group =& $projectGroup->getGroup();
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
			util_send_message($email, $subject, $messagebody);
		}
	}
}

cron_entry(22,$err);
?>