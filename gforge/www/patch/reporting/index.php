<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php,v 1.6 2000/11/16 20:15:59 pfalcon Exp $

require('pre.php');
require('../patch_utils.php');
require('../../project/stats/project_stats_utils.php');
require('tool_reports.php');

$page_title="Patch Reporting System";
$bar_colors=array("yellow","green");
$bar_colors=array("#FFD600","#489E48");

function patch_reporting_header($group_id) {
        reports_header($group_id,
        	array('aging','tech','category'),
                array('Aging Report','Patches by Technician','Patches by Category')
	);
}

function patch_quick_report($group_id,$title,$subtitle1,$sql1,$subtitle2,$sql2) {
        global $bar_colors;

       	patch_header(array ("title"=>$title));
       	patch_reporting_header($group_id);
       	echo "\n<H1>$title</H1>";

        reports_quick_graph($subtitle1,$sql1,$sql2,$bar_colors);

	patch_footer(array());
}


if ($group_id && user_ismember($group_id/*,"C2"*/)) {

	include ($DOCUMENT_ROOT.'/include/HTML_Graphs.php');

	if ($what) {
		/*
			Update the database
		*/

                $period_clause=period2sql($period,$span,"open_date");

		if ($what=="aging") {

			patch_header(array ("title"=>"Aging Report"));
			patch_reporting_header($group_id);
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
                                     "FROM patch ".
                                     "WHERE close_date > 0 ".
                                     "AND (open_date >= $start AND open_date <= $end) ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=((int)(db_result($result, 0,0)*1000))/1000;
			}

			GraphIt($names, $values,
                        "Average Turnaround Time For Closed Patches (days)");

			echo "<P>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT count(*) ".
                                     "FROM patch ".
                                     "WHERE open_date >= $start ".
                                     "AND open_date <= $end ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($start))." to ".date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, "Number of Patches Submitted");

			echo "<P>";

			for ($counter=1; $counter<=$span; $counter++) {

				$start=($time_now-($counter*$sub_duration));
				$end=($time_now-(($counter-1)*$sub_duration));

				$sql="SELECT count(*) ".
                                     "FROM patch ".
                                     "WHERE open_date <= $end ".
                                     "AND (close_date >= $end OR close_date < 1 OR close_date is null) ".
                                     "AND group_id='$group_id'";

				$result = db_query($sql);

				$names[$counter-1]=date("Y-m-d",($end));
				$values[$counter-1]=db_result($result, 0,0);
			}

			GraphIt($names, $values, "Number of Patches Still Open");

			echo "<P>";

			patch_footer(array());

		} else if ($what=="category") {

			$sql1="SELECT patch_category.category_name AS Category, count(*) AS Count ".
                              "FROM patch_category,patch ".
			      "WHERE patch_category.patch_category_id=patch.patch_category_id ".
                              "AND patch.patch_status_id = '1' ".
                              "AND patch.group_id='$group_id' ".
                              $period_clause .
			      "GROUP BY Category";
			$sql2="SELECT patch_category.category_name AS Category, count(*) AS Count ".
                              "FROM patch_category,patch ".
			      "WHERE patch_category.patch_category_id=patch.patch_category_id ".
                              "AND patch.patch_status_id <> '3' ".
                              "AND patch.group_id='$group_id' ".
                              $period_clause .
		 	      "GROUP BY Category";

                	patch_quick_report($group_id,
                        		  "Patches By Category",
                        		  "Open Patches By Category",$sql1,
                        		  "All Patches By Category",$sql2);

		} else if ($what=="tech") {

			$sql1="SELECT users.user_name AS Technician, count(*) AS Count ".
                              "FROM users,patch ".
			      "WHERE users.user_id=patch.assigned_to ".
                              "AND patch.patch_status_id = '1' ".
                              "AND patch.group_id='$group_id' ".
                              $period_clause .
			      "GROUP BY Technician";

			$sql2="SELECT users.user_name AS Technician, count(*) AS Count ".
                              "FROM users,patch ".
			      "WHERE users.user_id=patch.assigned_to ".
                              "AND patch.patch_status_id <> '3' ".
                              "AND patch.group_id='$group_id' ".
                              $period_clause .
			      "GROUP BY Technician";

                	patch_quick_report($group_id,
                        		  "Patches By Technician",
                        		  "Open Patches By Technician",$sql1,
                        		  "All Patches By Technician",$sql2);

		} else {
                	exit_missing_param();
                }

	} else {
		/*
			Show main page
		*/
		patch_header(array ("title"=>$page_title));

		echo "\n<H1>$page_title</H1>";
		patch_reporting_header($group_id);

		patch_footer(array());

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
