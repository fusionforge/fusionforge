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

require_once('note.php');

pm_header(array('title'=>$Language->getText('pm_modtask','title'),'pagename'=>'pm_modtask','group_project_id'=>$group_project_id));

?>

<form action="<?php echo "$PHP_SELF?group_id=$group_id&amp;group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postmodtask" />
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>" />

<table border="0" width="100%">

	<tr>
		<td><strong><?php echo $Language->getText('pm_modtask','submitted_by') ?>:</strong><br />
			<?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</td>
		<td><input type="submit" value="<?php echo $Language->getText('general','submit') ?>" name="submit" /></td>
	</tr>

	<tr>
		<td>
			<strong><?php echo $Language->getText('pm','category') ?>:</strong><br />
			<?php echo $pg->categoryBox('category_id',$pt->getCategoryID()); ?> <a href="/pm/admin/?<?php echo "group_id=$group_id&amp;add_cat=1&amp;group_project_id=$group_project_id"; ?>">(admin)</a>
		</td>

		<td>
			<strong><?php echo $Language->getText('pm_detailtask','subproject'); ?>:</strong><br />
			<?php echo $pg->groupProjectBox('new_group_project_id',$group_project_id,false); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm','percent_complete') ?>:</strong><br />
		<?php echo $pg->percentCompleteBox('percent_complete',$pt->getPercentComplete()); ?>
		</td>

		<td>
		<strong><?php echo $Language->getText('pm','priority') ?>:</strong><br />
		<?php echo build_priority_select_box('priority',$pt->getPriority()); ?>
		</td>



	</tr>

  	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','summary') ?>:</strong><br />
		<input type="text" name="summary" size="40" maxlength="65" value="<?php echo $pt->getSummary(); ?>" />
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm_modtask','original_comment') ?>:</strong><br />
		<?php echo nl2br( $pt->getDetails() ); ?>
		<p />
		<strong><?php echo $Language->getText('pm_modtask','add_comment') ?>:</strong><?php echo notepad_button('document.forms[1].details') ?><br />
		<textarea name="details" rows="5" cols="40" wrap="soft"></textarea>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','start_date') ?>:</strong><br />
		<?php
		echo $pg->showMonthBox ('start_month',date('m', $pt->getStartDate()));
		echo $pg->showDayBox ('start_day',date('d', $pt->getStartDate()));
		echo $pg->showYearBox ('start_year',date('Y', $pt->getStartDate()));
		echo $pg->showHourBox ('start_hour',date('G', $pt->getStartDate()));
		echo $pg->showMinuteBox ('start_minute',date('i',$pt->getStartDate()));
		?><br /><?php echo $Language->getText('pm','date_note') ?>
		<br /><a href="calendar.php?group_id=<?php echo $group_id; ?>&amp;group_project_id=<?php echo $group_project_id; ?>" target="_blank"><?php echo $Language->getText('pm','view_calendar') ?></a>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','end_date') ?>:</strong><br />
		<?php
		echo $pg->showMonthBox ('end_month',date('m', $pt->getEndDate()));
		echo $pg->showDayBox ('end_day',date('d', $pt->getEndDate()));
		echo $pg->showYearBox ('end_year',date('Y', $pt->getEndDate()));
		echo $pg->showHourBox ('end_hour',date('G', $pt->getEndDate()));
		echo $pg->showMinuteBox ('end_minute',date('i', $pt->getEndDate()));
		?>
		</td>
	</tr>

	<tr>
		<td valign="top">
		<strong><?php echo $Language->getText('pm','assigned_to') ?>:</strong><br />
		<?php
		/*
			List of possible users that this one could be assigned to
		*/
		echo $pt->multipleAssignedBox ();
		?>
		</td>

		<td valign="top">
		<strong><?php echo $Language->getText('pm','dependent') ?>:</strong><br />
		<?php
		/*
			List of possible tasks that this one could depend on
		*/

		echo $pt->multipleDependBox();
		?><br />
		<?php echo $Language->getText('pm','dependent_note') ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm','hours') ?>:</strong><br />
		<input type="text" name="hours" size="5" value="<?php echo $pt->getHours(); ?>" />
		</td>

		<td>
		<strong><?php echo $Language->getText('pm','status') ?>:</strong><br />
		<?php
		echo $pg->statusBox('status_id', $pt->getStatusID(), false );
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

	<tr>
		<td colspan="2" align="center">
		<input type="submit" value="<?php echo $Language->getText('general','submit') ?>" name="submit" />
		</td>
	</tr>

</table>
</form>
<?php

pm_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
