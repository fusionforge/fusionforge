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

pm_header(array('title'=>'Modify A Task','pagename'=>'pm_modtask','group_project_id'=>$group_project_id));

?>

<form action="<?php echo "$PHP_SELF?group_id=$group_id&group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postmodtask">
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>">

<table border="0" width="100%">

	<tr>
		<TD><B>Submitted By:</B><br><?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</TD>
	</tr>

	<tr>	
		<td>
		<b>Category:</b><br>
		<?php echo $pg->categoryBox('category_id',$pt->getCategoryID()); ?> <a href="/pm/admin/?<?php echo "group_id=$group_id&add_cat=1&group_project_id=$group_project_id"; ?>">(admin)</a>
		</td>

		<td>
		<input type="submit" value="Submit Changes" name="submit">
		</td>
	</tr>

	<tr>
		<td>
		<b>Percent Complete:</b><br>
		<?php echo $pg->percentCompleteBox('percent_complete',$pt->getPercentComplete()); ?>
		</td>

		<td>
		<b>Priority:</b><br>
		<?php echo build_priority_select_box('priority',$pt->getPriority()); ?>
		</td>



	</tr>

  	<tr>
		<td colspan="2">
		<b>Task Summary:</b><br>
		<input type="text" name="summary" size="40" MAXLENGTH="65" value="<?php echo $pt->getSummary(); ?>">
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<b>Original Comment:</b><br>
		<?php echo nl2br( $pt->getDetails() ); ?>
		<p>
		<b>Add A Comment:</b><br>
		<textarea name="details" rows="5" cols="40" wrap="soft"></textarea>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<b>Start Date:</b><br>
		<?php
		echo $pg->showMonthBox ('start_month',date('m', $pt->getStartDate()));
		echo $pg->showDayBox ('start_day',date('d', $pt->getStartDate()));
		echo $pg->showYearBox ('start_year',date('Y', $pt->getStartDate()));
		echo $pg->showHourBox ('start_hour',date('G', $pt->getStartDate()));
		echo $pg->showMinuteBox ('start_minute',date('i',$pt->getStartDate())); 
		?><br>
		The system will modify your start/end dates if you attempt to create a start date
		earlier than the end date of any tasks you depend on.
		<br><a href="calendar.php?group_id=<?php echo $group_id; ?>&amp;group_project_id=<?php echo $group_project_id; ?>" target="_blank">View Calendar</a>
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<b>End Date:</b><br>
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
		<b>Assigned To:</b><br>
		<?php
		/*
			List of possible users that this one could be assigned to
		*/
		echo $pt->multipleAssignedBox ();
		?>
		</td>

		<td valign="top">
		<b>Dependent On Task:</b><br>
		<?php
		/*
			List of possible tasks that this one could depend on
		*/

		echo $pt->multipleDependBox();
		?><br>
		You should choose only tasks which must be completed before this task can start.
		</td>
	</tr>

	<tr>
		<td>
		<b>Hours:</b><br>
		<input type="text" name="hours" size="5" value="<?php echo $pt->getHours(); ?>">
		</td>

		<td>
		<b>Status:</b><br>
		<?php
		echo $pg->statusBox('status_id', $pt->getStatusID() );
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
		<td colspan="2" ALIGN="MIDDLE">
		<input type="submit" value="Submit Changes" name="submit">
		</td>
		</form>
	</tr>

</table>
<?php

pm_footer(array());

?>
