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

pm_header(array('title'=>'View A Task','pagename'=>'pm_detailtask','group_project_id'=>$group_project_id));

?>

<table border="0" width="100%">
	<tr>
		<td colspan="2">
		<b>Category:</b><br>
		<?php echo $pt->getCategoryName(); ?>
		</td>
	</tr>

	<tr>
		<td>
		<b>Percent Complete:</b><br>
		<?php echo $pt->getPercentComplete(); ?>%
		</td>

		<td>
		<b>Priority:</b><br>
		<?php echo $pt->getPriority(); ?>
		</td>
	</tr>

	<tr>
		<td>
		<b>Start Date:</b><br>
		<?php echo date('Y-m-d', $pt->getStartDate() ); ?>
		</td>
		<td>
		<b>End Date:</b><br>
		<?php echo date('Y-m-d', $pt->getEndDate() ); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<b>Task Summary:</b><br>
		<?php echo $pt->getSummary(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<b>Original Comment:</b><br>
		<?php echo nl2br($pt->getDetails()); ?>
		</td>
	</tr>

	<tr>
		<td valign="top">
		<?php
		/*
			Get the list of ids this is assigned to and convert to array
			to pass into multiple select box
		*/

		$result2=db_query("SELECT users.user_name AS User_Name FROM users,project_assigned_to ".
			"WHERE users.user_id=project_assigned_to.assigned_to_id AND project_task_id='$project_task_id'");
		ShowResultSet($result2,'Assigned To');
		?>
		</td>
		<td valign="top">
		<?php
		/*
			Get the list of ids this is dependent on and convert to array
			to pass into multiple select box
		*/
		$result2=db_query("SELECT project_task.summary FROM project_dependencies,project_task ".
			"WHERE is_dependent_on_task_id=project_task.project_task_id AND project_dependencies.project_task_id='$project_task_id'");
		ShowResultSet($result2,'Dependent On Task');
		?>
		</td>
	</tr>

	<tr>
		<td>
		<b>Hours:</b><br>
		<?php echo $pt->getHours(); ?>
		</td>

		<td>
		<b>Status:</b><br>
		<?php
		echo $pt->getStatusName();
		?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php echo $pt->showDependentTasks(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php echo $pt->showRelatedArtifacts(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php echo $pt->showMessages(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
			<?php echo $pt->showHistory(); ?>
		</td>
	</tr>

</table>
<?php

pm_footer(array());

?>
