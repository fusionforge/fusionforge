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
require_once('common/reporting/Report.class.php');

session_require( array('group'=>$sys_stats_group) );

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$start = getStringFromRequest('start');
$end = getStringFromRequest('end');
$tstat = getStringFromRequest('tstat');

if (!$start) {
	$z =& $report->getWeekStartArr();
	$start = $z[count($z)-1];
}
if (!$end) {
	$end=$z[0];
}
if (!$tstat) {
	$tstat='1';
}

$n[]=_('Any');
$n[]=_('Open');
$n[]=_('closed');

$l[]='1,2';
$l[]='1';
$l[]='2';

echo report_header(_('User Summary Report'));

	?>
	<h3><?php echo _('User Summary Report'); ?></h3>
	<p>
	<?php echo _('Choose the range from the pop-up boxes below. The report will list all tasks with an open date in that range.'); ?>
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
	<table>
		<tr>
			<td><strong><?php echo _('Start'); ?>:</strong><br /><?php echo report_weeks_box($report, 'start', $start); ?></td>
			<td><strong><?php echo _('End'); ?>:</strong><br /><?php echo report_weeks_box($report, 'end', $end); ?></td>
			<td><strong><?php echo _('Task Status'); ?>:</strong><br /><?php echo html_build_select_box_from_arrays($l,$n,'tstat',$tstat,false); ?></td>
			<td><input type="submit" name="submit" value="<?php echo _('Refresh'); ?>"></td>
		</tr>
	</table>
	</form>
	<p>
	<?php

$sql="SELECT users.realname,users.user_id,users.user_name, ps.status_name, pgl.group_id, pt.group_project_id, 
pt.summary, pt.hours, pt.end_date, pt.project_task_id, pt.hours, sum(rtt.hours) AS remaining_hrs,
(select sum(hours) from rep_time_tracking 
	WHERE user_id=users.user_id 
	AND project_task_id=pt.project_task_id
	AND report_date BETWEEN '$start' AND '$end') AS cumulative_hrs
FROM users, project_assigned_to pat, project_status ps, project_group_list pgl, project_task pt
LEFT JOIN rep_time_tracking rtt USING (project_task_id)
WHERE users.user_id=pat.assigned_to_id
AND pgl.group_project_id=pt.group_project_id
AND pat.project_task_id=pt.project_task_id
AND pt.status_id=ps.status_id
AND pt.status_id IN ($tstat)
AND pt.start_date BETWEEN '$start' AND '$end'
GROUP BY realname, users.user_id, user_name, status_name, pgl.group_id, pt.group_project_id, 
	summary, pt.hours, end_date, pt.project_task_id, pt.hours";

$res=db_query($sql);
if (!$res || db_numrows($res) < 1) {
	echo _('No matches found').db_error();
} else {
	$tableHeaders = array(
		_('Name'),
		_('Task'),
		_('Status'),
		_('Cum. Hrs'),
		_('Rem. Hrs'),
		_('End Date')
	);
	echo $HTML->listTableTop($tableHeaders);
	$last_name='';
	for ($i=0; $i<db_numrows($res); $i++) {
		$name=db_result($res,$i,'realname');
		if ($last_name != $name) {
			echo '
		<tr '.$HTML->boxGetAltRowStyle(0).'>
			<td colspan="6"><strong>'.$name.'</strong></td>
		</tr>';
			$last_name = $name;
		}
		echo '
		<tr '.$HTML->boxGetAltRowStyle(1).'>
			<td>&nbsp;</td>
			<td>'.util_make_link ('/pm/task.php?func=detailtask&group_id='.db_result($res,$i,'group_id') .'&project_task_id='.db_result($res,$i,'project_task_id') .'&group_project_id='.db_result($res,$i,'group_project_id'),db_result($res,$i,'summary')) .'
			</td>
			<td>'.db_result($res,$i,'status_name').'</td>
			<td>'.number_format(db_result($res,$i,'cumulative_hrs'),1).'</td>
			<td>'.number_format((db_result($res,$i,'hours')-db_result($res,$i,'remaining_hrs')),1).'</td>
			<td>'.date(_('Y-m-d H:i'),db_result($res,$i,'end_date')).'</td>
		</tr>';

		$task=db_result($res,$i,'project_task_id');
		$sql2="SELECT g.group_name, g.group_id, agl.group_artifact_id, agl.name, a.artifact_id, a.summary
		FROM project_task_artifact pta, artifact a, artifact_group_list agl, groups g
		WHERE pta.project_task_id='$task'
		AND pta.artifact_id=a.artifact_id
		AND a.group_artifact_id=agl.group_artifact_id
		AND agl.group_id=g.group_id";

		$res2=db_query($sql2);
		$last_tracker='';
		if (!$res2 || db_numrows($res2) < 1) {
			echo db_error();
		} else {
			for ($j=0; $j<db_numrows($res2); $j++) {
				$tracker=db_result($res2,$j,'group_name'). '*' .db_result($res2,$j,'name');
				echo '
		<tr '.$HTML->boxGetAltRowStyle(1).'>
			<td colspan="3">&nbsp;</td>
			<td>';
				if ($last_tracker != $tracker) {
					$last_tracker = $tracker;
					echo $tracker;
				} else {
					echo '&nbsp;';
				}
				echo '
			</td>
			<td colspan="2">'.util_make_link ('/tracker/?func=detail&atid='.db_result($res2,$j,'group_artifact_id'). '&group_id='.db_result($res2,$j,'group_id'). '&aid='.db_result($res2,$j,'artifact_id'), db_result($res2,$j,'summary')).'
			</td>
		</tr>';
			}
			$last_tracker='';
		}

	}
	echo $HTML->listTableBottom();

}

echo report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
