<?php
/**
 * GForge Project Management Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'pm/include/ProjectGroupHTML.class.php';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'pm/ProjectCategory.class.php';
require_once $gfwww.'project/stats/project_stats_utils.php';
require_once $gfwww.'include/tool_reports.php';

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group_id = getIntFromRequest('group_id');

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error('Error',$g->getErrorMessage());
}

$perm =& $g->getPermission( session_get_user() );
if (!$perm->isPMAdmin()) {
	exit_permission_denied();
}


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
	   	echo "\n<h1>$title</h1>";

		reports_quick_graph($subtitle1,$qpa1,$qpa2,$bar_colors);

		if ($comment) echo $comment;

	pm_footer(array());
}


include_once $gfwww.'include/HTML_Graphs.php';

$what = getStringFromRequest('what');
if ($what) {
	$period = getStringFromRequest('period');
	$span = getStringFromRequest('span');

	/*
		Update the database
	*/

	$period_threshold = time() - period2seconds($period, $span) ;

	if ($what=="aging") {
		$start = getIntFromRequest('start');
		$end = getIntFromRequest('end');

		pm_header(array ("title"=>_('Aging Report')));
		pm_reporting_header($group_id);
		echo "\n<h1>"._('Aging Report')."</h1>";

		$time_now=time();
//		echo $time_now."<p>";

		if (!$period || $period=="lifespan") {
			$period="month";
			$span=12;
		}

		if (!$span) {
			$span=1;
		}
		$sub_duration=period2seconds($period,1);
//		echo $sub_duration,"<br />";

		for ($counter=1; $counter<=$span; $counter++) {

			$start=($time_now-($counter*$sub_duration));
			$end=($time_now-(($counter-1)*$sub_duration));



			$result = db_query_params ('SELECT avg((end_date-start_date)/(24*60*60)) 
FROM project_task,project_group_list 
WHERE end_date > 0 
AND (start_date >= $1 AND start_date <= $2) 
AND project_task.status_id=2 
AND project_group_list.group_project_id=project_task.group_project_id 
AND project_group_list.group_id=$3 ',
			array($start,
				$end,
				$group_id));

			$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
			$values[$counter-1]=((int)(db_result($result, 0,0)*1000))/1000;
		}

		GraphIt($names, $values, _('Average duration for closed tasks (days)'));

		echo "<p />";

		for ($counter=1; $counter<=$span; $counter++) {

			$start=($time_now-($counter*$sub_duration));
			$end=($time_now-(($counter-1)*$sub_duration));

			$result = db_query_params ('SELECT count(*) 
FROM project_task,project_group_list 
WHERE start_date >= $1 
AND start_date <= $2 
AND project_group_list.group_project_id=project_task.group_project_id 
AND project_group_list.group_id=$3 ',
			array($start,
				$end,
				$group_id));

			$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
			$values[$counter-1]=db_result($result, 0,0);
		}

		GraphIt($names, $values, _('Number of started tasks'));

		echo "<p />";

		for ($counter=1; $counter<=$span; $counter++) {

			$start=($time_now-($counter*$sub_duration));
			$end=($time_now-(($counter-1)*$sub_duration));



			$result = db_query_params ('SELECT count(*) 
FROM project_task,project_group_list 
WHERE start_date <= $1 
AND (end_date >= $2 OR end_date < 1 OR end_date is null) 
AND project_group_list.group_project_id=project_task.group_project_id 
AND project_group_list.group_id=$3 ',
			array($end,
				$end,
				$group_id));

			$names[$counter-1]=date("Y-m-d",($end));
			$values[$counter-1]=db_result($result, 0,0);
		}

		GraphIt($names, $values, _('Number of tasks still not completed'));

		echo "<p />";

		pm_footer(array());

	} else if ($what=="subproject") {
		$qpa1 = db_construct_qpa (false,
					  'SELECT project_group_list.project_name AS Subproject, count(*) AS Count 
FROM project_group_list,project_task 
WHERE project_group_list.group_project_id=project_task.group_project_id 
AND project_task.status_id = 1
AND project_group_list.group_id=$1
AND start_date >= $2
GROUP BY Subproject',
					  array ($group_id,
						 $period_threshold)) ;
		$qpa2 = db_construct_qpa ('SELECT project_group_list.project_name AS Subproject, count(*) AS Count 
FROM project_group_list,project_task 
WHERE project_group_list.group_project_id=project_task.group_project_id 
AND project_task.status_id <> 3
AND project_group_list.group_id=$1
AND start_date >= $2
GROUP BY Subproject',
					  array ($group_id,
						 $period_threshold)) ;

		pm_quick_report($group_id,
			  _('Tasks By Category'),
			  _('Open Tasks By Category'), $qpa1,
			  _('All Tasks By Category'), $qpa2);

	} else if ($what=="tech") {
		$qpa1 = db_construct_qpa ('SELECT users.user_name AS Technician, count(*) AS Count 
FROM users,project_group_list,project_task,project_assigned_to 
WHERE users.user_id=project_assigned_to.assigned_to_id 
AND project_assigned_to.project_task_id=project_task.project_task_id 
AND project_task.group_project_id=project_group_list.group_project_id 
AND project_task.status_id = 1
AND project_group_list.group_id=$1
AND start_date >= $2
GROUP BY Technician',
					  array ($group_id,
						 $period_threshold)) ;

		$qpa2 = db_construct_qpa ('SELECT users.user_name AS Technician, count(*) AS Count 
FROM users,project_group_list,project_task,project_assigned_to 
WHERE users.user_id=project_assigned_to.assigned_to_id 
AND project_assigned_to.project_task_id=project_task.project_task_id 
AND project_task.group_project_id=project_group_list.group_project_id 
AND project_task.status_id <> 3
AND project_group_list.group_id=$1
AND start_date >= $2
GROUP BY Technician',
					  array ($group_id,
						 $period_threshold)) ;

		pm_quick_report($group_id,
		  _('Tasks By Assignee'),
		  _('Open Tasks By Assignee'), $qpa1,
		  _('All Tasks By Assignee'), $qpa2,
		  _('<p>Note that same task can be assigned to several technicians. Such task will be counted for each of them.</p>'));

	} else {
		exit_missing_param();
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

?>
