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

pm_header(array('title'=>$Language->getText('pm_detailtask','title'),'pagename'=>'pm_detailtask','group_project_id'=>$group_project_id));

?>

<table border="0" width="100%">

        <tr>
                <td><strong><?php echo $Language->getText('pm_detailtask','submitted_by') ?>:</strong><br /><?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</td>
        </tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm_detailtask','category') ?>:</strong><br />
		<?php echo $pt->getCategoryName(); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm_detailtask','pct_complete') ?>:</strong><br />
		<?php echo $pt->getPercentComplete(); ?>%
		</td>

		<td>
		<strong><?php echo $Language->getText('pm_detailtask','priority') ?>:</strong><br />
		<?php echo $pt->getPriority(); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm_detailtask','start_date') ?>:</strong><br />
		<?php echo date('Y-m-d', $pt->getStartDate() ); ?>
		</td>
		<td>
		<strong><?php echo $Language->getText('pm_detailtask','end_date') ?>:</strong><br />
		<?php echo date('Y-m-d', $pt->getEndDate() ); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm_detailtask','task_summary') ?>:</strong><br />
		<?php echo $pt->getSummary(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm_detailtask','original_comment') ?>:</strong><br />
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

		$result2=db_query("SELECT users.user_name AS User_Name FROM users,project_assigned_to 
			WHERE users.user_id=project_assigned_to.assigned_to_id AND project_task_id='$project_task_id'");
		ShowResultSet($result2,$Language->getText('pm_detailtask','assigned_to'), false, false);
		?>
		</td>
		<td valign="top">
		<?php
		/*
			Get the list of ids this is dependent on and convert to array
			to pass into multiple select box
		*/
		$result2=db_query("SELECT project_task.summary FROM project_dependencies,project_task 
			WHERE is_dependent_on_task_id=project_task.project_task_id 
			AND project_dependencies.project_task_id='$project_task_id'");
		ShowResultSet($result2,$Language->getText('pm_detailtask','dependend_on_task'), false, false);
		?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm_detailtask','hours') ?>:</strong><br />
		<?php echo $pt->getHours(); ?>
		</td>

		<td>
		<strong><?php echo $Language->getText('pm_detailtask','status') ?>:</strong><br />
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
	<?php
		$hookParams['task_id']=$project_task_id;
		plugin_hook("task_extra_detail",$hookParams);
	?>
	<tr>
		<td colspan="2">
			<?php echo $pt->showHistory(); ?>
		</td>
	</tr>

</table>
<?php

pm_footer(array());

?>
