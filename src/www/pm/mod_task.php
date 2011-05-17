<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfwww.'include/note.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';

pm_header(array('title'=>_('Modify Task'),'pagename'=>'pm_modtask','group_project_id'=>$group_project_id));

echo notepad_func();

?>

<form id="modtaskform" action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id&amp;group_project_id=$group_project_id"; ?>" method="post">
<input type="hidden" name="func" value="postmodtask" />
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>" />

<table border="0" width="100%" class="mod_task">

	<tr>
		<td><strong><?php echo _('Submitted by') ?>:</strong><br />
			<?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</td>
		<td><input type="submit" value="<?php echo _('Submit') ?>" name="submit" /></td>
		<td><strong>Task ID:</strong> <?php echo $project_task_id; ?> @ <?php
		echo forge_get_config ('web_host') ; ?></td>
	</tr>

	<tr>
		<td>
			<strong><?php echo _('Category') ?></strong><br />
			<?php echo $pg->categoryBox('category_id',$pt->getCategoryID()); ?> <?php util_make_link ('/pm/admin/?<?php echo "group_id=$group_id&amp;add_cat=1&amp;group_project_id='.$group_project_id,_('admin')); ?>
		</td>

		<td>
			<strong><?php echo _('Subproject'); ?>:</strong><br />
			<?php echo $pg->groupProjectBox('new_group_project_id',$group_project_id,false); ?>
		</td>

		<td>
			<strong><a href="<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>">Permalink</a>:</strong><br />
			<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Percent Complete') ?>:</strong><br />
		<?php echo $pg->percentCompleteBox('percent_complete',$pt->getPercentComplete()); ?>
		</td>

		<td>
		<strong><?php echo _('Priority') ?>:</strong><br />
		<?php echo build_priority_select_box('priority',$pt->getPriority()); ?>
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
		<strong><?php echo _('Task Summary') ?>:</strong><br />
		<input type="text" name="summary" size="65" maxlength="65" value="<?php echo $pt->getSummary(); ?>" />
		</td>
		<td colspan="2">
		<a href="<?php echo getStringFromServer('PHP_SELF')."?func=deletetask&amp;project_task_id=$project_task_id&amp;group_id=$group_id&amp;group_project_id=$group_project_id"; ?>"><?php echo _('Delete this task') ?></a>
		</td>
		<td>
		<?php echo util_make_link("/export/rssAboTask.php?tid=" .
		    $project_task_id, html_image('ic/rss.png',
		    16, 16, array('border' => '0')) . " " .
		    _('Subscribe to task'));
		?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
		<strong><?php echo _('Original Comment') ?>:</strong><br />
		<?php 
			$sanitizer = new TextSanitizer();
        	$body = $sanitizer->SanitizeHtml($pt->getDetails());

			if (strpos($body,'<') === false) {
				echo nl2br($pt->getDetails());
			} else {
				echo $body;
			}
		?>
		<p />
		<strong><?php echo _('Add A Comment') ?>:</strong><?php echo notepad_button('document.forms.modtaskform.details') ?><br />
<?php
$GLOBALS['editor_was_set_up']=false;
$params = array() ;
$params['name'] = 'details';
$params['width'] = "800";
$params['height'] = "300";
$params['body'] = "";
$params['group'] = $group_id;
plugin_hook("text_editor",$params);
if (!$GLOBALS['editor_was_set_up']) {
    echo '<textarea name="details" rows="5" cols="80"></textarea>';
}
unset($GLOBALS['editor_was_set_up']);
?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
		<strong><?php echo _('Start Date') ?>:</strong><br />
		<?php
		echo $pg->showMonthBox ('start_month',date('m', $pt->getStartDate()));
		echo $pg->showDayBox ('start_day',date('d', $pt->getStartDate()));
		echo $pg->showYearBox ('start_year',date('Y', $pt->getStartDate()));
		echo $pg->showHourBox ('start_hour',date('G', $pt->getStartDate()));
		echo $pg->showMinuteBox ('start_minute',date('i',$pt->getStartDate()));
		?><br /><?php echo _('The system will modify your start/end dates if you attempt to create a start date earlier than the end date of any tasks you depend on.') ?>
		<br /><a href="calendar.php?group_id=<?php echo $group_id; ?>&amp;group_project_id=<?php echo $group_project_id; ?>" target="_blank"><?php echo _('View Calendar') ?></a>
		</td>
	</tr>

	<tr>
		<td colspan="3">
		<strong><?php echo _('End Date') ?>:</strong><br />
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
		<strong><?php echo _('Assigned to') ?>:</strong><br />
		<?php
		/*
			List of possible users that this one could be assigned to
		*/
		echo $pt->multipleAssignedBox ();
		?>
		</td>

		<td valign="top" colspan="2">
		<strong><?php echo _('Dependent on task') ?>:</strong><br />
		<?php
		/*
			List of possible tasks that this one could depend on
		*/

		echo $pt->multipleDependBox();
		?><br />
		<?php echo _('You should choose only tasks which must be completed before this task can start.') ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Estimated Hours') ?>:</strong><br />
		<input type="text" name="hours" size="5" value="<?php echo $pt->getHours(); ?>" />
		</td>

		<td colspan="2">
		<strong><?php echo _('Status') ?>:</strong><br />
		<?php
		echo $pg->statusBox('status_id', $pt->getStatusID(), false );
		?>
		</td>
	</tr>
	<input type="hidden" name="duration" value="<?php echo $pt->getDuration(); ?>" />
	<input type="hidden" name="parent_id" value="<?php echo $pt->getParentID(); ?>" />
<!--
//will add duration and parent selection boxes
	<tr>
		<td>
		<strong><?php echo _('Estimated Hours') ?>:</strong><br />
		<input type="text" name="hours" size="5" value="<?php echo $pt->getHours(); ?>" />
		</td>

		<td colspan="2">
		<strong><?php echo _('Status') ?></strong><br />
		<?php
//		echo $pg->statusBox('status_id', $pt->getStatusID(), false );
		?>
		</td>
	</tr>
-->
	<tr>
		<td colspan="3">
			<?php echo $pt->showDependentTasks(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
			<?php echo $pt->showRelatedArtifacts(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
			<?php echo $pt->showMessages(); ?>
		</td>
	</tr>
	<?php
		$hookParams['task_id']=$project_task_id;
		plugin_hook("task_extra_detail",$hookParams);
	?>
	<tr>
		<td colspan="3">
			<?php echo $pt->showHistory(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="3" style="text-align:center">
		<input type="submit" value="<?php echo _('Submit') ?>" name="submit" />
		</td>
	</tr>

</table>
</form>

<h3><?php echo _('Time tracking'); ?></h3>

<?php
$title_arr = array();
$title_arr[]=_('Week');
$title_arr[]=_('Day');
$title_arr[]=_('Estimated Hours');
$title_arr[]=_('Category');
$title_arr[]=_('User');
$title_arr[]=' ';

$xi = 0;

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage(),'pm');
}

echo '<form action="/reporting/timeadd.php" method="post" />
	<input type="hidden" name="project_task_id" value="'.$project_task_id.'" />
	<input type="hidden" name="submit" value="1" />';
echo $HTML->listTableTop ($title_arr);
echo '<tr '.$HTML->boxGetAltRowStyle($xi++).'>
		<td style="text-align:center">'. report_weeks_box($report, 'week') .'</td>
		<td style="text-align:center">'. report_day_adjust_box($report, 'days_adjust') .'</td>
		<td style="text-align:center"><input type="text" name="hours" value="" size="3" maxlength="3" /></td>
		<td style="text-align:center">'.report_time_category_box('time_code',false).'</td>
		<td>&nbsp;</td>
		<td style="text-align:center"><input type="submit" name="add" value="'._('Add').'" /><input type="submit" name="cancel" value="'._('Cancel').'" /></td>
	</tr></form>';
	
//setenv("TZ=" . $user_timezone); //restore the user's timezone
	
//
//	Display Time Recorded for this task
//


$res=db_query_params ('SELECT users.realname, rep_time_tracking.report_date, rep_time_tracking.hours, rep_time_category.category_name
	FROM users,rep_time_tracking,rep_time_category
	WHERE 
	users.user_id=rep_time_tracking.user_id
	AND rep_time_tracking.time_code=rep_time_category.time_code
	AND rep_time_tracking.project_task_id=$1',
			array($project_task_id));
$total_hours =0;
for ($i=0; $i<db_numrows($res); $i++) {

	echo '
	<tr '.$HTML->boxGetAltRowStyle($xi++).'>
	<td>&nbsp;</td>
	<td>'.date(_('Y-m-d H:i'),db_result($res,$i,'report_date')).'</td>
	<td>'.db_result($res,$i,'hours').'</td>
	<td>'.db_result($res,$i,'category_name').'</td>
	<td>'.db_result($res,$i,'realname').'</td>
	<td>&nbsp;</td></tr>';
	$total_hours += db_result($res,$i,'hours');
	
}

echo '
<tr '.$HTML->boxGetAltRowStyle($xi++).'>
<td><strong>'._('Total').':</strong></td>
<td>&nbsp;</td>
<td>'.$total_hours.'</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td></tr>';

echo $HTML->listTableBottom();

pm_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
