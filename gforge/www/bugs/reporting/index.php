<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php,v 1.27 2000/11/27 12:24:56 pfalcon Exp $

require('pre.php');
require('../bug_utils.php');
require('../../project/stats/project_stats_utils.php');
require('tool_reports.php');

$page_title="Bug Reporting System";
//$bar_colors=array("red","blue");
$bar_colors=array("#F76D6D","#6D6DF7");

function bug_reporting_header($group_id) {
        reports_header($group_id,
        	array('aging','tech','category','bug_group','resolution'),
                array('Aging Report','Bugs by Technician','Bugs by Category','Bugs by Bug Group','Bugs by Resolution')
	);
}

function bugs_quick_report($group_id,$title,$subtitle1,$sql1,$subtitle2,$sql2) {
        global $bar_colors;

       	bug_header(array ("title"=>$title));
       	bug_reporting_header($group_id);
       	echo "\n<H1>$title</H1>";

        reports_quick_graph($subtitle1,$sql1,$sql2,$bar_colors);

	bug_footer(array());
}


if ($group_id && user_ismember($group_id/*,"B2"*/)) {

	include ($DOCUMENT_ROOT.'/include/HTML_Graphs.php');

	if ($what) {

                $period_clause=period2sql($period,$span,"date");

		if ($what=="aging") {

			bug_header(array ("title"=>"Aging Report"));
			bug_reporting_header($group_id);
			echo "\n<H1>Aging Report</H1>";

			$time_now=time();
//			echo $time_now."<P>";

		        if (!$period || $period=="lifespan") {
		        	$period="month";
                                $span=12;
		        }

			if (!$span) $span=1;
                        $sub_duration=period2seconds($period,1);
//                        echo $sub_duration,"<br>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT avg((close_date-date)/(24*60*60)) ".
                                     "FROM bug ".
                                     "WHERE close_date > 0 ".
                                     "AND (date >= $start AND date <= $end) ".
                                     "AND resolution_id <> '2' ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=((int)(db_result($result, 0,0)*1000))/1000;
			}

			GraphIt($names, $values,
                        "Average Turnaround Time For Closed Bugs (days)");

			echo "<P>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT count(*) FROM bug WHERE date >= $start AND date <= $end AND resolution_id <> '2' AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, "Number of Bugs Submitted");

			echo "<P>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT count(*) FROM bug WHERE date <= $end AND (close_date >= $end OR close_date < 1 OR close_date is null) AND resolution_id <> '2' AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, "Number of Bugs Still Open");

			echo "<P>";

			bug_footer(array());

		} else if ($what=="category") {

			$sql1="SELECT bug_category.category_name AS Category, count(*) AS Count FROM bug_category,bug ".
				"WHERE bug_category.bug_category_id=bug.category_id AND bug.status_id <> '3' AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Category";
			$sql2="SELECT bug_category.category_name AS Category, count(*) AS Count FROM bug_category,bug ".
				"WHERE bug_category.bug_category_id=bug.category_id AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Category";

                	bugs_quick_report($group_id,
                        		  "Bugs By Category",
                        		  "Open Bugs By Category",$sql1,
                        		  "All Bugs By Category",$sql2);

		} else if ($what=="tech") {

			$sql1="SELECT users.user_name AS Technician, count(*) AS Count FROM users,bug ".
				"WHERE users.user_id=bug.assigned_to AND bug.status_id <> '3' AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Technician";

			$sql2="SELECT users.user_name AS Technician, count(*) AS Count FROM users,bug ".
				"WHERE users.user_id=bug.assigned_to AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Technician";

                	bugs_quick_report($group_id,
                        		  "Bugs By Technician",
                        		  "Open Bugs By Technician",$sql1,
                        		  "All Bugs By Technician",$sql2);

		} else if ($what=="bug_group") {

			$sql1="SELECT bug_group.group_name AS Bug_Group_Name, count(*) AS Count FROM bug_group,bug ".
				"WHERE bug_group.bug_group_id=bug.bug_group_id AND bug.status_id <> '3' AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Bug_Group_Name";
			$sql2="SELECT bug_group.group_name AS Bug_Group_Name, count(*) AS Count FROM bug_group,bug ".
				"WHERE bug_group.bug_group_id=bug.bug_group_id AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Bug_Group_Name";

                	bugs_quick_report($group_id,
                        		  "Bugs By Bug Group",
                        		  "Open Bugs By Bug Group",$sql1,
                        		  "All Bugs By Bug Group",$sql2);

		} else if ($what=="resolution") {

			$sql1="SELECT bug_resolution.resolution_name AS Resolution, count(*) AS Count FROM bug_resolution,bug ".
				"WHERE bug_resolution.resolution_id=bug.resolution_id AND bug.status_id <> '3' AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Resolution";
			$sql2="SELECT bug_resolution.resolution_name AS Resolution, count(*) AS Count FROM bug_resolution,bug ".
				"WHERE bug_resolution.resolution_id=bug.resolution_id AND bug.resolution_id <> '2' AND bug.group_id='$group_id' ".
                                $period_clause .
				"GROUP BY Resolution";

                	bugs_quick_report($group_id,
                        		  "Bugs By Resolution",
                        		  "Open Bugs By Resolution",$sql1,
                        		  "All Bugs By Resolution",$sql2);

		} else {
                	exit_missing_param();
                }

	} else {
		/*
			Show main page
		*/
		bug_header(array ("title"=>$page_title));

		echo "\n<H1>$page_title</H1>";
		bug_reporting_header($group_id);

		bug_footer(array());

	}

} else {

	// Cannot show reports

	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}

}
?>
