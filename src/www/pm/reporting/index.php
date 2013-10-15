<?php
/**
 * FusionForge Project Management Facility : Tasks
 *
 * Copyright 1999-2000 (c) Tim Perdue - Sourceforge
 * Copyright 2002 (c) GForge LLC
 * Copyright 2010 (c) FusionForge Team
 * Copyrgith 2013, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'pm/include/ProjectGroupHTML.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'pm/ProjectCategory.class.php';
require_once $gfwww.'project/stats/project_stats_utils.php';
require_once $gfwww.'include/tool_reports.php';
require_once $gfcommon.'reporting/report_utils.php';

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group_id = getIntFromRequest('group_id');

if (!$group_id) {
	exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error($g->getErrorMessage(),'pm');
}

session_require_perm ('pm_admin', $group_id) ;

$page_title=_('Task Reporting System');
$bar_colors=array("pink","violet");

function pm_reporting_header($group_id) {
		reports_header($group_id,
			array('aging','tech','subproject'),
			array(_('Aging Report'),
				_('Report by Assignee'),
				_('Report by Subproject')));
}

function pm_quick_report($group_id,$title,$subtitle1,$qpa1,$subtitle2,$qpa2,$comment="") {
	global $bar_colors;

	pm_header(array ("title"=>$title));
	pm_reporting_header($group_id);

	reports_quick_graph($subtitle1,$qpa1,$qpa2,$bar_colors);

	if ($comment) echo $comment;

	pm_footer(array());
}

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotplugindateAxisRenderer();
html_use_jqueryjqplotpluginBar();

$what = getStringFromRequest('what');
if ($what) {
	$period = getStringFromRequest('period');
	$span = getStringFromRequest('span');

	/*
		Update the database
	*/

	$period_threshold = period2timestamp($period, $span) ;

	if ($what=="aging") {
		$start = getIntFromRequest('start');
		$end = getIntFromRequest('end');

		pm_header(array ("title"=>_('Aging Report')));
		pm_reporting_header($group_id);

		$time_now=time();

		if (!$period || $period=="lifespan") {
			$period="month";
			$span=12;
		}

		if (!$span) {
			$span=1;
		}
		$sub_duration=period2seconds($period,1);

		$values = array();
		$labels = array();
		$labels[0] = _('Average duration for closed tasks (days)');
		$labels[1] = _('Number of started tasks');

		for ($counter=1; $counter<=$span; $counter++) {

			$start = ($time_now-($counter*$sub_duration));
			$end = ($time_now-(($counter-1)*$sub_duration));

			if ($end < $g->getStartDate()) {
				break;
			}

			$resAvgClosedTask = db_query_params ('SELECT avg((end_date-start_date)/(24*60*60))
								FROM project_task,project_group_list
								WHERE end_date > 0
								AND (start_date >= $1 AND start_date <= $2)
								AND project_task.status_id=2
								AND project_group_list.group_project_id=project_task.group_project_id
								AND project_group_list.group_id=$3 ',
								array($start, $end, $group_id));

			$resStartTasks = db_query_params ('SELECT count(*)
								FROM project_task,project_group_list
								WHERE start_date >= $1
								AND start_date <= $2
								AND project_group_list.group_project_id=project_task.group_project_id
								AND project_group_list.group_id=$3 ',
								array($start, $end, $group_id));

			$ticks[$counter-1] = date("Y-m-d", ($start))
								. ' ' . _('to') . ' '
								. date("Y-m-d",($end));
			$values[0][$counter-1] = ((int)(db_result($resAvgClosedTask, 0, 0)*1000))/1000;
			$values[1][$counter-1] = (int)db_result($resStartTasks, 0, 0);
		}

		report_pm_hbar(1, $values, $ticks, $labels);

		$values = array();
		$labels = array();
		$ticks = array();
		for ($counter = 1; $counter <= $span; $counter++) {

			$start = ($time_now - ($counter * $sub_duration));
			$end = ($time_now - (($counter - 1 ) * $sub_duration));

			if ($end < $g->getStartDate()) {
				break;
			}

			$resNotCompleted = db_query_params ('SELECT count(*)
								FROM project_task,project_group_list
								WHERE start_date <= $1
								AND (end_date >= $2 OR end_date < 1 OR end_date is null)
								AND project_group_list.group_project_id=project_task.group_project_id
								AND project_group_list.group_id=$3 ',
								array($end, $end, $group_id));

			$ticks[$counter-1] = date("Y-m-d", ($end));
			$values[0][$counter-1] = db_result($resNotCompleted, 0, 0);
		}

		$labels[] = _('Number of tasks still not completed');
		report_pm_hbar(2, $values, $ticks, $labels);

		pm_footer(array());

	} elseif ($what=="subproject") {
		$qpa1 = db_construct_qpa (false,
					  'SELECT project_group_list.project_name AS subproject, count(*) AS Count
FROM project_group_list,project_task
WHERE project_group_list.group_project_id=project_task.group_project_id
AND project_task.status_id = 1
AND project_group_list.group_id=$1', array ($group_id));
		if ($period_threshold) {
			$qpa1 = db_construct_qpa($qpa1, ' AND start_date >= $1 ', array ($period_threshold));
		}
		$qpa1 = db_construct_qpa($qpa1, ' GROUP BY subproject');

		$qpa2 = db_construct_qpa(false, 'SELECT project_group_list.project_name AS subproject, count(*) AS Count
FROM project_group_list,project_task
WHERE project_group_list.group_project_id=project_task.group_project_id
AND project_task.status_id <> 3
AND project_group_list.group_id=$1', array ($group_id));
		if ($period_threshold) {
			$qpa2 = db_construct_qpa($qpa2, ' AND start_date >= $1 ', array ($period_threshold));
		}
		$qpa2 = db_construct_qpa($qpa2, ' GROUP BY subproject');

		pm_quick_report($group_id,
			  _('Tasks By Category'),
			  _('Open Tasks By Category'), $qpa1,
			  _('All Tasks By Category'), $qpa2);

	} elseif ($what=="tech") {
		$qpa1 = db_construct_qpa(false, 'SELECT users.user_name AS technician, count(*) AS Count
FROM users,project_group_list,project_task,project_assigned_to
WHERE users.user_id=project_assigned_to.assigned_to_id
AND project_assigned_to.project_task_id=project_task.project_task_id
AND project_task.group_project_id=project_group_list.group_project_id
AND project_task.status_id = 1
AND project_group_list.group_id=$1', array ($group_id));
		if ($period_threshold) {
			$qpa1 = db_construct_qpa($qpa1, ' AND start_date >= $1 ', array ($period_threshold));
		}
		$qpa1 = db_construct_qpa($qpa1, ' GROUP BY technician');

		$qpa2 = db_construct_qpa(false, 'SELECT users.user_name AS technician, count(*) AS Count
FROM users,project_group_list,project_task,project_assigned_to
WHERE users.user_id=project_assigned_to.assigned_to_id
AND project_assigned_to.project_task_id=project_task.project_task_id
AND project_task.group_project_id=project_group_list.group_project_id
AND project_task.status_id <> 3
AND project_group_list.group_id=$1', array ($group_id));
		if ($period_threshold) {
			$qpa2 = db_construct_qpa($qpa2, ' AND start_date >= $1 ', array ($period_threshold));
		}
		$qpa2 = db_construct_qpa($qpa2, ' GROUP BY technician');

		pm_quick_report($group_id,
		  _('Tasks By Assignee'),
		  _('Open Tasks By Assignee'), $qpa1,
		  _('All Tasks By Assignee'), $qpa2,
		  '<p>' . _('Note that same task can be assigned to several technicians. Such task will be counted for each of them.') . '</p>');

	} else {
		exit_missing_param('','','pm');
	}

} else {
	/*
		Show main page
	*/
	pm_header(array ("title"=>$page_title));

	pm_reporting_header($group_id);

	pm_footer(array());

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
