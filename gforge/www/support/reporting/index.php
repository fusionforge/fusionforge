<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php,v 1.3 2000/11/16 20:15:59 pfalcon Exp $

require('pre.php');
require('../support_utils.php');
require('../../project/stats/project_stats_utils.php');
require('tool_reports.php');

$page_title="Support Reporting System";
//$bar_colors=array("cyan","magenta");
$bar_colors=array("#F76DF7","#54BFBF");

function support_reporting_header($group_id) {
        reports_header($group_id,
        	array('aging','tech','category'),
                array('Aging Report','Support Requests by Technician','Support Requests by Category')
	);
}

function support_quick_report($group_id,$title,$subtitle1,$sql1,$subtitle2,$sql2) {
        global $bar_colors;

       	support_header(array ("title"=>$title));
       	support_reporting_header($group_id);
       	echo "\n<H1>$title</H1>";

        reports_quick_graph($subtitle1,$sql1,$sql2,$bar_colors);

	support_footer(array());
}


if ($group_id && user_ismember($group_id/*,"S2"*/)) {

	include ($DOCUMENT_ROOT.'/include/HTML_Graphs.php');

	if ($what) {
		/*
			Update the database
		*/

                $period_clause=period2sql($period,$span,"open_date");

		if ($what=="aging") {

			support_header(array ("title"=>"Aging Report"));
			support_reporting_header($group_id);
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

				$sql="SELECT avg((close_date-open_date)/(24*60*60)) ".
                                     "FROM support ".
                                     "WHERE close_date > 0 ".
                                     "AND (open_date >= $start AND open_date <= $end) ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=((int)(db_result($result, 0,0)*1000))/1000;
			}

			GraphIt($names, $values,
                        "Average Turnaround Time For Closed Support Requests (days)");

			echo "<P>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT count(*) ".
                                     "FROM support ".
                                     "WHERE open_date >= $start ".
                                     "AND open_date <= $end ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, "Number of Support Requests Submitted");

			echo "<P>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT count(*) ".
                                     "FROM support ".
                                     "WHERE open_date <= $end ".
                                     "AND (close_date >= $end OR close_date < 1 OR close_date is null) ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, "Number of Support Requests Still Open");

			echo "<P>";

			support_footer(array());

		} else if ($what=="category") {

			$sql1="SELECT support_category.category_name AS Category, count(*) AS Count ".
                              "FROM support_category,support ".
			      "WHERE support_category.support_category_id=support.support_category_id ".
                              "AND support.support_status_id = '1' ".
                              "AND support.group_id='$group_id' ".
                              $period_clause .
			      "GROUP BY Category";
			$sql2="SELECT support_category.category_name AS Category, count(*) AS Count ".
                              "FROM support_category,support ".
			      "WHERE support_category.support_category_id=support.support_category_id ".
                              "AND support.support_status_id <> '3' ".
                              "AND support.group_id='$group_id' ".
                              $period_clause .
		 	      "GROUP BY Category";

                	support_quick_report($group_id,
                        		  "Support Requests By Category",
                        		  "Open Support Requests By Category",$sql1,
                        		  "All Support Requests By Category",$sql2);

		} else if ($what=="tech") {

			$sql1="SELECT users.user_name AS Technician, count(*) AS Count ".
                              "FROM users,support ".
			      "WHERE users.user_id=support.assigned_to ".
                              "AND support.support_status_id = '1' ".
                              "AND support.group_id='$group_id' ".
                              $period_clause .
			      "GROUP BY Technician";

			$sql2="SELECT users.user_name AS Technician, count(*) AS Count ".
                              "FROM users,support ".
			      "WHERE users.user_id=support.assigned_to ".
                              "AND support.support_status_id <> '3' ".
                              "AND support.group_id='$group_id' ".
                              $period_clause .
			      "GROUP BY Technician";

                	support_quick_report($group_id,
                        		  "Support Requests By Technician",
                        		  "Open Support Requests By Technician",$sql1,
                        		  "All Support Requests By Technician",$sql2);

		} else {
                	exit_missing_param();
                }

	} else {
		/*
			Show main page
		*/
		support_header(array ("title"=>$page_title));

		echo "\n<H1>$page_title</H1>";
		support_reporting_header($group_id);

		support_footer(array());

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
