<?php
/**
 * FusionForge : Project Management Facility
 *
 * Copyright 1999/2000, Sourceforge.net Tim Perdue
 * Copyright 2002 GForge, LLC, Tim Perdue
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2015, Franck Villaume - TrivialDev
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

global $HTML;

if (getStringFromRequest('commentsort') == 'anti') {
	$sort_comments_chronologically = false;
} else {
	$sort_comments_chronologically = true;
}

html_use_jquerydatetimepicker();
pm_header(array('title' => _('Modify Task'), 'pagename' => 'pm_modtask', 'group_project_id' => $group_project_id));

echo notepad_func();
echo init_datetimepicker();

echo $HTML->openForm(array('action' => '/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id, 'id' => 'modtaskform', 'method' => 'post'));
?>

<input type="hidden" name="func" value="postmodtask" />
<input type="hidden" name="project_task_id" value="<?php echo $project_task_id; ?>" />
<input type="hidden" name="duration" value="<?php echo $pt->getDuration(); ?>" />
<input type="hidden" name="parent_id" value="<?php echo $pt->getParentID(); ?>" />

<table class="fullwidth mod_task">

	<tr>
		<td><strong><?php echo _('Submitted by') . _(': '); ?></strong><br />
			<?php echo $pt->getSubmittedRealName(); ?> (<?php echo $pt->getSubmittedUnixName(); ?>)</td>
		<td><input type="submit" value="<?php echo _('Submit') ?>" name="submit" /></td>
		<td><strong><?php echo _('Task Id') . _(': '); ?></strong> <?php echo $project_task_id; ?> @ <?php
		echo forge_get_config ('web_host') ; ?></td>
	</tr>

	<tr>
		<td>
			<strong><?php echo _('Category') . _(': '); ?></strong><br />
			<?php echo $pg->categoryBox('category_id',$pt->getCategoryID()); ?>
			<?php echo util_make_link('/pm/admin/?group_id='.$group_id.'&add_cat=1&group_project_id='.$group_project_id,_('Admin')); ?>
		</td>

		<td>
			<strong><?php echo _('Subproject') . _(': '); ?></strong><br />
			<?php echo $pg->groupProjectBox('new_group_project_id',$group_project_id,false); ?>
		</td>

		<td>
			<strong><?php echo util_make_link('/pm/t_follow.php/'.$project_task_id, _('Permalink'))._(':'); ?></strong><br />
			<?php echo util_make_url("/pm/t_follow.php/" . $project_task_id); ?>
		</td>
	</tr>

	<tr>
		<td>
		<strong><?php echo _('Percent Complete')._(': '); ?></strong><br />
		<?php $pg->percentCompleteBox('percent_complete',$pt->getPercentComplete()); ?>
		</td>

		<td>
		<strong><?php echo _('Priority')._(': '); ?></strong><br />
		<?php echo build_priority_select_box('priority',$pt->getPriority()); ?>
		</td>

		<td>
		<strong><? echo _('Task Detail Information (JSON)')._(': '); ?></strong><br />
		<?php echo util_make_link('/pm/t_lookup.php?tid='.$project_task_id, 'application/json').
		_(' or ').
		util_make_link('/pm/t_lookup.php?text=1&tid='.$project_task_id, 'text/plain') ?>
		</td>
	</tr>

	<tr>
		<td>
		<label for="summary"><strong><?php echo _('Task Summary')._(': '); ?></strong></label><br />
		<input id="summary" type="text" name="summary" size="65" maxlength="65" value="<?php echo $pt->getSummary(); ?>" />
		</td>
		<td colspan="2">
		<?php echo util_make_link('/pm/task.php?func=deletetask&project_task_id='.$project_task_id.'&group_id='.$group_id.'&group_project_id='.$group_project_id, $HTML->getDeletePic(_('Delete this task'), _('Delete this task')).' '._('Delete this task')); ?>
		</td>
		<td>
		<?php echo util_make_link('/export/rssAboTask.php?tid='.$project_task_id,
						html_image('ic/rss.png', 16, 16).' '._('Subscribe to task'));
		?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
		<p>
		<label for="details"><strong><?php echo _('Original Comment') . _(': '); ?></strong></label>
		<?php echo notepad_button('document.forms.modtaskform.details'); ?>
		</p>
		<?php
			$sanitizer = new TextSanitizer();
			$body = $sanitizer->SanitizeHtml($pt->getDetails());

			$GLOBALS['editor_was_set_up'] = false;
			$params = array();
			$params['id'] = 'details';
			$params['name'] = 'details';
			$params['width'] = "800";
			$params['height'] = "300";
			$params['body'] = $body;
			$params['group'] = $group_id;
			plugin_hook("text_editor", $params);
			if (!$GLOBALS['editor_was_set_up']) {
				echo html_e('textarea', array('name' => 'details', 'rows' => '5', 'cols' => '80'), $body, false);
			}
			unset($GLOBALS['editor_was_set_up']);
		?>
		<p>
		<label for="followup"><strong><?php echo _('Add A Comment') . _(': '); ?></strong></label>
		<?php echo notepad_button('document.forms.modtaskform.followup') ?>
		</p>
<?php
$GLOBALS['editor_was_set_up']=false;
$params = array() ;
$params['id'] = 'followup';
$params['name'] = 'followup';
$params['width'] = "800";
$params['height'] = "300";
$params['body'] = "";
$params['group'] = $group_id;
plugin_hook("text_editor",$params);
if (!$GLOBALS['editor_was_set_up']) {
	echo html_e('textarea', array('name' => 'followup', 'rows' => '5', 'cols' => '80'), '', false);
}
unset($GLOBALS['editor_was_set_up']);
?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
		<strong><?php echo _('Start Date') . _(': '); ?></strong><br />
		<input class="datetimepicker" type="text" name="start_date" value="<?php echo date(_('Y-m-d H:i'), $pt->getStartDate()); ?>">
		<br /><?php echo _('The system will modify your start/end dates if you attempt to create a start date earlier than the end date of any tasks you depend on.') ?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
		<strong><?php echo _('End Date') . _(': '); ?></strong><br />
		<input class="datetimepicker" name="end_date" type="text" value="<?php echo date(_('Y-m-d H:i'), $pt->getEndDate()); ?>"
		?>
		</td>
	</tr>

	<tr>
		<td class="top">
		<strong><?php echo _('Assigned to') . _(': '); ?></strong><br />
		<?php
		/*
			List of possible users that this one could be assigned to
		*/
		echo $pt->multipleAssignedBox ();
		?>
		</td>

		<td class="top" colspan="2">
		<strong><?php echo _('Dependent on task') . _(': '); ?></strong><br />
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
		<label for="hours"><strong><?php echo _('Estimated Hours') . _(': '); ?></strong></label>
		<input id="hours" type="number" name="hours" size="5" value="<?php echo $pt->getHours(); ?>" />
		</td>

		<td colspan="2">
		<strong><?php echo _('Status') . _(': '); ?></strong><br />
		<?php
		echo $pg->statusBox('status_id', $pt->getStatusID(), false);
		?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
			<?php $pt->showDependentTasks(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
			<?php $pt->showRelatedArtifacts(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="3">
			<?php $pt->showMessages($sort_comments_chronologically, '/pm/task.php?func=detailtask&project_task_id='.$project_task_id.'&group_id='.$group_id.'&group_project_id='.$group_project_id); ?>
		</td>
	</tr>
	<?php
		$hookParams['task_id'] = $project_task_id;
		$hookParams['group_id'] = $group_id;
		plugin_hook("task_extra_detail", $hookParams);
	?>
	<tr>
		<td colspan="3">
			<?php $pt->showHistory(); ?>
		</td>
	</tr>

	<tr>
		<td colspan="3" style="text-align:center">
		<input type="submit" value="<?php echo _('Submit') ?>" name="submit" />
		</td>
	</tr>

</table>
<?php
echo $HTML->closeForm();
echo html_e('h2', array(), _('Time tracking'));

$title_arr = array();
$title_arr[]=_('Week');
$title_arr[]=_('Day');
$title_arr[]=_('Estimated Hours');
$title_arr[]=_('Category');
$title_arr[]=_('User');
$title_arr[]=' ';

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage(), 'pm');
}
$report->setStartDate($pt->ProjectGroup->Group->getStartDate());

echo $HTML->openForm(array('action' => '/reporting/timeadd.php', 'id' => 'time-tracking', 'method' => 'post'));
echo '<input type="hidden" name="project_task_id" value="'.$project_task_id.'" />
	<input type="hidden" name="submit" value="1" />';
echo $HTML->listTableTop($title_arr);
echo '<tr>
		<td class="align-center">'. report_weeks_box($report, 'week') .'</td>
		<td class="align-center">'. report_day_adjust_box() .'</td>
		<td class="align-center"><input id="time-tracking-hours" type="text" required="required" name="hours" value="" size="3" maxlength="3" /></td>
		<td class="align-center">'.report_time_category_box('time_code',false).'</td>
		<td></td>
		<td class="align-center"><input type="submit" name="add" value="'._('Add').'" /><input type="submit" name="cancel" value="'._('Cancel').'" /></td>
	</tr>';

//setenv("TZ=" . $user_timezone); //restore the user's timezone

//
//	Display Time Recorded for this task
//


$res=db_query_params('SELECT users.realname, rep_time_tracking.report_date, rep_time_tracking.hours, rep_time_category.category_name
	FROM users,rep_time_tracking,rep_time_category
	WHERE
	users.user_id=rep_time_tracking.user_id
	AND rep_time_tracking.time_code=rep_time_category.time_code
	AND rep_time_tracking.project_task_id=$1',
			array($project_task_id));
$total_hours = 0;
for ($i=0; $i<db_numrows($res); $i++) {

	echo '
	<tr>
	<td></td>
	<td>'.date(_('Y-m-d H:i'),db_result($res,$i,'report_date')).'</td>
	<td>'.db_result($res,$i,'hours').'</td>
	<td>'.db_result($res,$i,'category_name').'</td>
	<td>'.db_result($res,$i,'realname').'</td>
	<td></td></tr>';
	$total_hours += db_result($res,$i,'hours');

}

echo '
<tr>
<td><strong>'._('Total')._(': ').'</strong></td>
<td></td>
<td>'.$total_hours.'</td>
<td></td>
<td></td>
<td></td>
</tr>';

echo $HTML->listTableBottom();
echo $HTML->closeForm();

pm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
