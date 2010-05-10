<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 */
/*

	Tasks
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

pm_header(array('title'=>_('Task Detail'),'group_project_id'=>$group_project_id));

?>

<table border="0" width="100%">

        <tr>
                <td><strong><?php echo _('Submitted by') ?>:</strong><br /><?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</td>

		<td>
			<strong><a href="<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>">Permalink</a>:</strong><br />
			<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>
		</td>
        </tr>

	<tr>
		<td>
		<strong><?php echo _('Category') ?></strong><br />
		<?php echo $pt->getCategoryName(); ?>
		</td>

		<td>
		<strong>Task Detail Information (JSON):</strong><br />
		<a href="<?php echo util_make_url("/pm/t_lookup.php?tid=" . $project_task_id); ?>">application/json</a>
		or
		<a href="<?php echo util_make_url("/pm/t_lookup.php?text=1&amp;tid=" . $project_task_id); ?>">text/plain</a>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Percent Complete') ?>:</strong><br />
		<?php echo $pt->getPercentComplete(); ?>%
		</td>

		<td>
		<strong><?php echo _('Priority') ?>:</strong><br />
		<?php echo $pt->getPriority(); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Start Date') ?>:</strong><br />
		<?php echo date(_('Y-m-d'), $pt->getStartDate() ); ?>
		</td>
		<td>
		<strong><?php echo _('End Date') ?>:</strong><br />
		<?php echo date(_('Y-m-d'), $pt->getEndDate() ); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<strong><?php echo _('Task Summary') ?>:</strong><br />
		<?php echo $pt->getSummary(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo _('Original Comment') ?>:</strong><br />
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

		$result2=db_query_params ('SELECT users.user_name AS User_Name FROM users,project_assigned_to 
			WHERE users.user_id=project_assigned_to.assigned_to_id AND project_task_id=$1',
			array($project_task_id));
		ShowResultSet($result2,_('Assigned to'), false, false);
		?>
		</td>
		<td valign="top">
		<?php
		/*
			Get the list of ids this is dependent on and convert to array
			to pass into multiple select box
		*/
		$result2=db_query_params ('SELECT project_task.summary FROM project_dependencies,project_task 
			WHERE is_dependent_on_task_id=project_task.project_task_id 
			AND project_dependencies.project_task_id=$1',
			array($project_task_id));
		ShowResultSet($result2,_('Dependent on task'), false, false);
		?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Hours') ?></strong><br />
		<?php echo $pt->getHours(); ?>
		</td>

		<td>
		<strong><?php echo _('Status') ?></strong><br />
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
