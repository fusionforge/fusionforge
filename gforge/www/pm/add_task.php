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

pm_header(array('title'=>$Language->getText('pm_addtask','title'),'pagename'=>'pm_addtask','group_project_id'=>$group_project_id));

?>

<form action="<?php echo "$PHP_SELF?group_id=$group_id&group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postaddtask">
<input type="hidden" name="add_artifact_id[]" value="<?php echo $related_artifact_id; ?>">

<table border="0" width="100%">

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm','category') ?>:</strong><br />
		<?php echo $pg->categoryBox('category_id'); ?> <a href="/pm/admin/?<?php echo "group_id=$group_id&add_cat=1&group_project_id=$group_project_id"; ?>">(<?php echo $Language->getText('pm','admin') ?>)</a>
		</td>

		<td><font size="-1">
		<input type="submit" value=<?php echo $Language->getText('general','submit') ?> name="submit"></font>
		</td>
	</tr>

	<tr>
		<td>
			<strong><?php echo $Language->getText('pm','percent_complete') ?>:</strong><br />
			<?php echo $pg->percentCompleteBox(); ?>
		</td>
		<td>
			<strong><?php echo $Language->getText('pm','priority') ?>:</strong><br />
			<?php echo build_priority_select_box(); ?>
		</td>
	</tr>

  	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','summary') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="summary" size="40" maxlength="65" value="<?php echo $related_artifact_summary; ?>">
		</td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','details') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<textarea name="details" rows="5" cols="40" wrap="soft"></textarea></td>
	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','start_date') ?>:</strong><br />
		<?php
		echo $pg->showMonthBox ('start_month',date('m', time()));
		echo $pg->showDayBox ('start_day',date('d', time()));
		echo $pg->showYearBox ('start_year',date('Y', time()));
		echo $pg->showHourBox ('start_hour',date('G', time()));
		echo $pg->showMinuteBox ('start_minute', date('i', 15*(time()%15)));
		?><br /><?php echo $Language->getText('pm','date_note') ?>
			<br /><a href="calendar.php?group_id=<?php echo $group_id; ?>&amp;group_project_id=<?php echo $group_project_id; ?>" target="_blank"><?php echo $Language->getText('pm','view_calendar') ?></a>
		</td>

	</tr>

	<tr>
		<td colspan="2">
		<strong><?php echo $Language->getText('pm','end_date') ?>:</strong><br />
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
		<strong><?php echo $Language->getText('pm','assigned_to') ?>:</strong><br />
		<?php
		echo $pt->multipleAssignedBox();
		?>
		</td>
		<td valign="top">
		<strong><?php echo $Language->getText('pm','dependent') ?>:</strong><br />
		<?php
		echo $pt->multipleDependBox();
		?><br />
		<?php echo $Language->getText('pm_addtask','dependent_note') ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo $Language->getText('pm','hours') ?>:</strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="hours" size="5">
		</td>

		<td>
		<input type="submit" value="<?php echo $Language->getText('general','submit') ?>" name="submit">
		</td>
		</form>
	</tr>

</table>
<?php

pm_footer(array());

?>
