<?php
/**
  *
  * SourceForge Project/Task Manager (PM)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

pm_header(array('title'=>'Add a New Task','pagename'=>'pm_addtask','group_project_id'=>$group_project_id));

?>

<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<INPUT TYPE="HIDDEN" NAME="func" VALUE="postaddtask">
<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php echo $group_id; ?>">
<INPUT TYPE="HIDDEN" NAME="group_project_id" VALUE="<?php echo $group_project_id; ?>">

<TABLE BORDER="0" WIDTH="100%">
	<TR>
		<TD>
			<B>Percent Complete:</B>
			<BR>
			<?php echo pm_show_percent_complete_box(); ?>
		</TD>
		<TD>
			<B>Priority:</B>
			<BR>
			<?php echo build_priority_select_box(); ?>
		</td>
	</TR>

  	<TR>
		<TD COLSPAN="2"><B>Task Summary:</B>
		<BR>
		<INPUT TYPE="text" name="summary" size="40" MAXLENGTH="65">
		</td>
	</TR>
	<TR>
		<TD COLSPAN="2"><B>Task Details:</B>
		<BR>
		<TEXTAREA NAME="details" ROWS="5" COLS="40" WRAP="SOFT"></TEXTAREA></td>
	</TR>
	<TR>
    		<TD COLSPAN="2"><B>Start Date:</B>
		<BR>
		<?php
		echo pm_show_month_box ('start_month',date('m', time()));
		echo pm_show_day_box ('start_day',date('d', time()));
		echo pm_show_year_box ('start_year',date('Y', time()));
		echo pm_show_hour_box ('start_hour',date('G', time()));
		?>
			<BR><a href="calendar.php">View Calendar</a>
		 </td>

	</TR>
	<TR>
		<TD COLSPAN="2"><B>End Date:</B>
		<BR>
		<?php
		echo pm_show_month_box ('end_month',date('m', time()));
		echo pm_show_day_box ('end_day',date('d', time()));
		echo pm_show_year_box ('end_year',date('Y', time()));
		echo pm_show_hour_box ('end_hour',date('G', time()));
		?>
		</td>

	</TR>
	<TR>
		<TD>
		<B>Assigned To:</B>
		<BR>
		<?php
		echo pm_multiple_assigned_box ('assigned_to[]',$group_id);
		?>
		</td>
		<TD>
		<B>Dependent On Task:</B>
		<BR>
		<?php
		echo pm_multiple_task_depend_box ('dependent_on[]',$group_project_id);
		?>
		</TD>
	</TR>
	<TR>
		<TD COLSPAN="2"><B>Hours:</B>
		<BR>
		<INPUT TYPE="text" name="hours" size="5">
		</td>
	</TR>
	<TR>
		<TD COLSPAN="2">
		<INPUT TYPE="submit" value="Submit" name="submit">
		</td>
		</form>
	</TR>
</TABLE>
<?php

pm_footer(array());

?>
