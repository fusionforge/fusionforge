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

pm_header(array('title'=>'Add a New Task','pagename'=>'pm_addtask','group_project_id'=>$group_project_id));

?>

<form action="<?php echo "$PHP_SELF?group_id=$group_id&group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postaddtask">
<input type="hidden" name="add_artifact_id[]" value="<?php echo $related_artifact_id; ?>">

<table border="0" width="100%">

	<tr>
		<td>
		<b>Category:</b><br>
		<?php echo $pg->categoryBox('category_id'); ?> <a href="/pm/admin/?<?php echo "group_id=$group_id&add_cat=1&group_project_id=$group_project_id"; ?>">(admin)</a>
		</td>

		<td><font size="-1">
		<input type="submit" value="Submit Changes" name="submit"></font>
		</td>
	</tr>

	<tr>
		<td>
			<b>Percent Complete:</b><br>
			<?php echo $pg->percentCompleteBox(); ?>
		</td>
		<td>
			<b>Priority:</b><br>
			<?php echo build_priority_select_box(); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<b>Task Summary:</b><br>
		<input type="text" name="summary" size="40" maxlength="65" VALUE="<?php echo $related_artifact_summary; ?>">
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<b>Task Details:</b><br>
		<textarea name="details" rows="5" cols="40" wrap="soft"></textarea></td>
	</tr>

	<tr>
		<td colspan="2">
		<b>Start Date:</b><br>
		<?php
		echo $pg->showMonthBox ('start_month',date('m', time()));
		echo $pg->showDayBox ('start_day',date('d', time()));
		echo $pg->showYearBox ('start_year',date('Y', time()));
		echo $pg->showHourBox ('start_hour',date('G', time()));
		echo $pg->showMinuteBox ('start_minute', date('i', 15*(time()%15)));
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
		echo $pg->showMonthBox ('end_month',date('m', (time()+604800)));
		echo $pg->showDayBox ('end_day',date('d', (time()+604800)));
		echo $pg->showYearBox ('end_year',date('Y', (time()+604800)));
		echo $pg->showHourBox ('end_hour',date('G', (time()+604800)));
		echo $pg->showMinuteBox ('end_minute', date('i', 15*((time()+604800)%15)));
		?>
		</td>

	</tr>

	<tr>
		<td valign="top">
		<b>Assigned To:</b><br>
		<?php
		echo $pt->multipleAssignedBox();
		?>
		</td>
		<td valign="top">
		<b>Dependent On Task:</b><br>
		<?php
		echo $pt->multipleDependBox();
		?><br>
		You should choose only tasks which must be completed before this task can start.
		</td>
	</tr>

	<tr>
		<td>
		<b>Estimated Hours:</b><br>
		<input type="text" name="hours" size="5">
		</td>

		<td>
		<input type="submit" value="Submit" name="submit">
		</td>
		</form>
	</tr>

</table>
<?php

pm_footer(array());

?>
