<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');

if (!session_loggedin()) {
	exit_not_logged_in();
}

global $Language;

$report=new Report();
if ($report->isError()) {
	exit_error('Error',$report->getErrorMessage());
}

$week = getStringFromRequest('week');
$project_task_id = getStringFromRequest('project_task_id');

if (getStringFromRequest('submit')) {
	$report_date = getStringFromRequest('report_date');
	$time_code = getStringFromRequest('time_code');
	$old_time_code = getStringFromRequest('old_time_code');
	$hours = getStringFromRequest('hours');
	
/*
	if (getStringFromRequest('update')) {
		
		if ($project_task_id && $report_date && $time_code) {
			$res=db_query("UPDATE rep_time_tracking 
				SET time_code='$time_code', hours='$hours'
				WHERE user_id='".user_getid()."'
				AND report_date='$report_date'
				AND project_task_id='$project_task_id'
				AND time_code='$old_time_code'");
			if (!$res || db_affected_rows($res) < 1) {
				exit_error('Error',db_error());
			} else {
				$feedback='Successfully Updated';
			}
		}

	} else
*/
	if (getStringFromRequest('delete')) {
		if ($project_task_id && $report_date && $old_time_code) {
			$res=db_query("DELETE FROM rep_time_tracking 
				WHERE user_id='".user_getid()."'
				AND report_date='$report_date'
				AND project_task_id='$project_task_id'
				AND time_code='$old_time_code'");
			if (!$res || db_affected_rows($res) < 1) {
				exit_error('Error',db_error());
			} else {
				$feedback=_('Successfully Deleted');
			}
		} else {
			echo "$project_task_id && $report_date && $old_time_code";
		}

	} elseif (getStringFromRequest('add')) {
		$days_adjust = getStringFromRequest('days_adjust');

		if ($project_task_id && $week && $days_adjust && $time_code && $hours) {

			//$date_list = split('[- :]',$report_date,5);
        	//$report_date = mktime($date_list[3],$date_list[4],0,$date_list[1],$date_list[2],$date_list[0]);
			//make it 12 NOON of the report_date
			$report_date=($week + ($days_adjust*REPORT_DAY_SPAN))+(12*60*60);
			$res=db_query("INSERT INTO rep_time_tracking (user_id,week,report_date,project_task_id,time_code,hours) 
				VALUES ('".user_getid()."','$week','$report_date','$project_task_id','$time_code','$hours')");
			if (!$res || db_affected_rows($res) < 1) {
				exit_error('Error',db_error());
			} else {
				$feedback.=_('Successfully Added');
			}
		} else {
			echo "$project_task_id && $week && $days_adjust && $time_code && $hours";
			exit_error('Error',_('All Fields Are Required.'));
		}

	}
}

if ($week) {
	$group_project_id = getStringFromRequest('group_project_id');

	report_header(_('Time Tracking'));

	if (!$group_project_id) {
	    if ( $sys_database_type == "mysql" ) {
			$sql="SELECT pgl.group_project_id,CONCAT(g.group_name, '**', pgl.project_name)
		} else {
			$sql="SELECT pgl.group_project_id,g.group_name || '**' || pgl.project_name ";
		}
		$sql.="
		FROM groups g, project_group_list pgl, user_group ug
		WHERE ug.user_id='".user_getid()."' 
		AND ug.group_id=g.group_id
		AND g.group_id=pgl.group_id
		ORDER BY group_name,project_name";
		$respm=db_query($sql);
	}
	?>
	<h3><?php echo $Language->getText('reporting_ta','time_entries',date('Y-m-d',$week)); ?></h3>
	<p>
	<?php
	if ( $sys_database_type == "mysql" ) {
		$sql="SELECT pt.project_task_id, CONCAT(pgl.project_name, '**', pt.summary) AS name, 
	} else {
		$sql="SELECT pt.project_task_id, pgl.project_name || '**' || pt.summary AS name, ";
	}
	$sql .= "
			rtt.hours, rtt.report_date, rtc.category_name, rtt.time_code
			FROM groups g, project_group_list pgl, project_task pt, rep_time_tracking rtt,
			rep_time_category rtc
			WHERE rtt.week='$week'
			AND rtt.time_code=rtc.time_code
			AND rtt.user_id='".user_getid()."'
			AND g.group_id=pgl.group_id
			AND pgl.group_project_id=pt.group_project_id
			AND pt.project_task_id=rtt.project_task_id 
		ORDER BY rtt.report_date";
	}
	$res=db_query($sql);
	$rows=db_numrows($res);
	if ($group_project_id || $rows) {

		$title_arr[]=_('Project/Task');
		$title_arr[]=_('Date');
		$title_arr[]=_('Hours');
		$title_arr[]=_('Category');
		$title_arr[]=' ';

		echo $HTML->listTableTop ($title_arr);

		while ($r=&db_fetch_array($res)) {
			echo '<form action="'.getStringFromServer('PHP_SELF').'?week='.$week.'&project_task_id='.$r['project_task_id'].'" method="post" />
			<input type="hidden" name="submit" value="1" />
			<input type="hidden" name="report_date" value="'.$r['report_date'] .'" />
			<input type="hidden" name="old_time_code" value="'.$r['time_code'] .'" />
			<tr '.$HTML->boxGetAltRowStyle($xi++).'>
				<td align="middle">'.$r['name'].'</td>
				<td align="middle">'. date( 'D, M d, Y',$r['report_date']) .'</td>
				<td align="middle"><!-- <input type="text" name="hours" value="'. $r['hours'] .'" size="3" maxlength="3" /> -->'.$r['hours'].'</td>
				<td align="middle"><!-- '.report_time_category_box('time_code',$r['time_code']).' -->'.$r['category_name'].'</td>
				<td align="middle"><!-- <input type="submit" name="update" value="Update" /> -->
				<input type="submit" name="delete" value="'. _('Delete').'" /></td>
			</tr></form>';
			$total_hours += $r['hours'];
		}
		if ($group_project_id) {

			$respt=db_query("SELECT project_task_id,summary FROM project_task WHERE group_project_id='$group_project_id'");

			echo '<form action="'.getStringFromServer('PHP_SELF').'?week='.$week.'" method="post" />
			<input type="hidden" name="submit" value="1" />
			<tr '.$HTML->boxGetAltRowStyle($xi++).'>
				<td align="middle">'. html_build_select_box ($respt,'project_task_id',false,false) .'</td>
				<td align="middle"><input type="text" name="report_date" value="'. date('Y-m-d',$week) .'" size="10" maxlength="10" /></td>
				<td align="middle"><input type="text" name="hours" value="" size="3" maxlength="3" /></td>
				<td align="middle">'.report_time_category_box('time_code',false).'</td>
				<td align="middle"><input type="submit" name="add" value="'.
				_('Add').'" /><input type="submit" name="cancel" value="'._('Add').'" /></td>
			</tr></form>';

		}
		echo '<tr '.$HTML->boxGetAltRowStyle($xi++).'><td colspan="2"><strong>'._('Total Hours').':</strong></td><td><strong>'.$total_hours.'</strong></td><td colspan="2"></td></tr>';
		echo $HTML->listTableBottom();

	}
	if (!$group_project_id) {
		?>
		<p>
		<h3><?php echo _('Add Entry'); ?></h3>
		<p>
		<?php echo _('Choose a Project/Subproject in the Task Manager. You will then have to choose a Task and category to record your time in.'); ?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get" />
		<input type="hidden" name="week" value="<?php echo $week; ?>" />
		<table>
		<tr>
			<td><strong><?php echo _('Task Manager Project'); ?>:</strong></td>
			<td><?php echo html_build_select_box ($respm,'group_project_id',false,false); ?></td>
			<td><input type="submit" name="submit" value="<?php echo _('Next'); ?>" /></td>
		</tr>
		</table>
		</form>
		<p>
		<h3>Change Week</h3>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get" />
		<?php echo report_weeks_box($report,'week'); ?><input type="submit" name="submit" value="<?php echo _('Change Week'); ?>" />
		</form>
		<?php
	}
//
//	First Choose A Week to add/update/delete time sheet info
//
} else {

	report_header(_('Time Tracking'));

	?>
	<h3><?php echo _('Choose A Week to Record Or Edit Your Time.'); ?></h3>
	<p>
	<?php echo _('After you choose a week, you will be prompted to choose a Project/Subproject in the Task Manager.'); ?>
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get" />
	<strong><?php echo _('Week Starting'); ?>:</strong><br />
	<?php echo report_weeks_box($report,'week'); ?>
	<p>
	<input type="submit" name="submit" value="<?php echo _('Next'); ?>" />
	</form>
	<?php

}

report_footer();

?>
