<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   index.php,v 1.7 2003/02/03 09:31:03 rspisser Exp
  *
  */


require_once('pre.php');
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once('www/project/stats/project_stats_utils.php');
require_once('tool_reports.php');

$page_title=$Language->getText('tracker_reporting','title');
//$bar_colors=array("red","blue");
$bar_colors=array("#F76D6D","#6D6DF7");

$group =& group_get_object($group_id);
exit_assert_object($group, 'Group');

$perm =& $group->getPermission( session_get_user() );
exit_assert_object($perm, 'Permission');

function reporting_header($group_id) {
	global $atid,$perm,$group_id;
	global $Language;
    if ($perm->isAdmin()) {
        $alevel=' >= 0';
    } else {
        $alevel=' > 1';
    }
    $sql="SELECT agl.group_artifact_id,agl.name
        FROM artifact_group_list agl,artifact_perm ap
        WHERE agl.group_artifact_id=ap.group_artifact_id
        AND ap.user_id='". user_getid() ."'
        AND ap.perm_level $alevel
        AND agl.group_id='$group_id'";
    $res=db_query($sql);

	
	reports_header(
		$group_id,
		array('aging','tech','category','group','resolution'),
		array($Language->getText('tracker_reporting','aging_report'),$Language->getText('tracker_reporting','dist_by_technician'),$Language->getText('tracker_reporting','dist_by_category'),$Language->getText('tracker_reporting','dist_by_group'),$Language->getText('tracker_reporting','dist_by_resolution')),
		'<strong>'.$Language->getText('tracker_reporting','artifact_type').': </strong>'
		 .html_build_select_box($res,'atid',$atid,false)
		 .'<br /><br />'
	);
}

function quick_report($group_id,$title,$subtitle1,$sql1,$subtitle2,$sql2) {
	global $bar_colors;
	global $Language;

	$group_name=array(group_getname($group_id));
	echo site_project_header(array("title"=>$title,'group'=>$group_id,'pagename'=>'tracker_reporting','sectionvals'=>$group_name));
	reporting_header($group_id);
	echo "\n<h1>$title</h1>";

	reports_quick_graph($subtitle1,$sql1,$sql2,$bar_colors);

	echo site_project_footer(array());
}


if ($perm->isMember()) {

	include_once('www/include/HTML_Graphs.php');

	if ($what) {

		$period_clause=period2sql($period,$span,"open_date");

		if ($what=="aging") {

			$group_name=array(group_getname($group_id));
			site_project_header(array ("title"=>$Language->getText('tracker_reporting','aging_report'),'group'=>$group_id,'pagename'=>'tracker_reporting','sectionvals'=>$group_name));
			reporting_header($group_id);
			echo "\n<h1>".$Language->getText('tracker_reporting','aging_report')."</h1>";

			$time_now=time();
//			echo $time_now."<p>";

			if (!$period || $period=="lifespan") {
				$period="month";
				$span=12;
			}

			if (!$span) $span=1;
			$sub_duration=period2seconds($period, 1);

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="	SELECT avg((close_date-open_date)/(24*60*60)) 
					FROM artifact
					WHERE close_date > 0 
					AND (open_date >= '$start' AND open_date <= '$end') 
					AND resolution_id <> '2' 
					AND group_artifact_id='$atid'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=((int)(db_result($result, 0,0)*1000))/1000;
			}

			GraphIt(
				$names, $values,
				$Language->getText('tracker_reporting','average_turnaround')
			);

			echo "<p>&nbsp;</p>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="	SELECT count(*)
					FROM artifact
					WHERE open_date >= '$start'
					AND open_date <= '$end'
					AND resolution_id <> '2'
					AND group_artifact_id='$atid'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, $Language->getText('tracker_reporting','items_submitted'));

			echo "<p>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="	SELECT count(*)
					FROM artifact
					WHERE open_date <= '$end'
					AND (close_date >= '$end' OR close_date < 1 OR close_date is null)
					AND resolution_id <> '2'
					AND group_artifact_id='$atid'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, $Language->getText('tracker_reporting','items_open'));

			echo "<p>&nbsp;</p>";

			site_project_footer(array());

		} else if ($what=="category") {

			// Open
			$sql1="
				SELECT artifact_category.category_name AS Category,
				       count(*) AS Count
				FROM artifact_category,artifact 
				WHERE artifact_category.id=artifact.category_id
				AND artifact.status_id = '1'
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause 
				GROUP BY Category";

			// All
			$sql2="
				SELECT artifact_category.category_name AS Category,
				       count(*) AS Count
				FROM artifact_category,artifact
				WHERE artifact_category.id=artifact.category_id
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause
				GROUP BY Category";

			quick_report(
				$group_id,
				$Language->getText('tracker_reporting','dist_by_category'),
				$Language->getText('tracker_reporting','open_items_by_technician'),$sql1,
				$Language->getText('tracker_reporting','all_by_technician'),$sql2
			);

		} else if ($what=="tech") {

			// Open
			$sql1="
				SELECT users.user_name AS Technician, count(*) AS Count
				FROM users,artifact
				WHERE users.user_id=artifact.assigned_to
				AND artifact.status_id = '1'
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause
				GROUP BY Technician";

			// All
			$sql2="
				SELECT users.user_name AS Technician, count(*) AS Count
				FROM users,artifact 
				WHERE users.user_id=artifact.assigned_to
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause
				GROUP BY Technician";

			quick_report(
				$group_id,
				$Language->getText('tracker_reporting','dist_by_technician'),
				$Language->getText('tracker_reporting','open_items_by_technician'),$sql1,
				$Language->getText('tracker_reporting','all_items_by_technician'),$sql2
			);

		} else if ($what=="group") {

			// Open
			$sql1="
				SELECT artifact_group.group_name AS Group_Name,
				      count(*) AS Count FROM artifact_group,artifact 
				WHERE artifact_group.id=artifact.artifact_group_id
				AND artifact.status_id = '1'
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause 
				GROUP BY Group_Name";

			// All
			$sql2="
				SELECT artifact_group.group_name AS Group_Name,
				      count(*) AS Count FROM artifact_group,artifact
				WHERE artifact_group.id=artifact.artifact_group_id
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause
				GROUP BY Group_Name";

			quick_report(
				$group_id,
				$Language->getText('tracker_reporting','dist_by_group'),
				$Language->getText('tracker_reporting','open_by_group'),$sql1,
				$Language->getText('tracker_reporting','all_by_group'),$sql2
			);

		} else if ($what=="resolution") {

			// Open
			$sql1="
				SELECT artifact_resolution.resolution_name AS Resolution,
				       count(*) AS Count
				FROM artifact_resolution,artifact
				WHERE artifact_resolution.id=artifact.resolution_id
				AND artifact.status_id = '1'
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause 
				GROUP BY Resolution";

			// All
			$sql2="
				SELECT artifact_resolution.resolution_name AS Resolution,
				       count(*) AS Count
				FROM artifact_resolution,artifact
				WHERE artifact_resolution.id=artifact.resolution_id
				AND artifact.resolution_id <> '2'
				AND artifact.group_artifact_id='$atid'
				$period_clause
				GROUP BY Resolution";

			quick_report(
				$group_id,
				$Language->getText('tracker_reporting','dist_by_resolution'),
				$Language->getText('tracker_reporting','open_items_by_resolution'),$sql1,
				$Language->getText('tracker_reporting','all_by_resolution'),$sql2
			);

		} else {
			exit_missing_param();
		}

	} else {
		/*
			Show main page
		*/

		//required params for site_project_header();
		$params['group'] = $group_id;
		$params['toptab'] = 'tracker';
		$params['title'] = $page_title;
		$params['pagename'] = 'tracker_reporting';
		$params['sectionvals']=array(group_getname($group_id));
	
		echo site_project_header($params);

		reporting_header($group_id);

		echo site_project_footer($params);

	}

} else {

	// Cannot show reports

	exit_permission_denied();

}
?>