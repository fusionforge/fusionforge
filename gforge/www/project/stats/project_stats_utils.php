<?php


   // week_to_dates
function week_to_dates( $week, $year = 0 ) {

	if ( $year == 0 ) {
		$year = gmstrftime("%Y", time() );
	} 

	   // One second into the New Year!
	$beginning = gmmktime(0,0,0,1,1,$year);
	while ( gmstrftime("%U", $beginning) < 1 ) {
		   // 86,400 seconds? That's almost exactly one day!
		$beginning += 86400;
	}
	$beginning += (86400 * 7 * ($week - 1));
	$end = $beginning + (86400 * 6);

	return array( $beginning, $end );
}


   // stats_project_daily
function stats_project_daily( $group_id, $span = 7 ) {

	if (! $span ) { 
		$span = 7;
	}

	   // Get information about the date $span days ago 
	$begin_date = localtime( (time() - ($span * 86400)), 1);
	$year = $begin_date["tm_year"] + 1900;
	$month = sprintf("%02d", $begin_date["tm_mon"] + 1);
	$day = $begin_date["tm_mday"];
/*
	$sql  = "SELECT month,day,AVG(group_ranking) AS group_ranking, ".
		"AVG(group_metric) AS group_metric, ".
		"SUM(downloads) AS downloads, ".
		"SUM(site_views + subdomain_views) AS page_views, ".
		"SUM(msg_posted) AS msg_posted, ".
		"SUM(bugs_opened) AS bugs_opened, ".
		"SUM(bugs_closed) AS bugs_closed, ".
		"SUM(support_opened) AS support_opened, ".
		"SUM(support_closed) AS support_closed, ".
		"SUM(patches_opened) AS patches_opened, ".
		"SUM(patches_closed) AS patches_closed, ".
		"SUM(tasks_opened) AS tasks_opened, ".
		"SUM(tasks_closed) AS tasks_closed, ".
		"SUM(cvs_commits) AS cvs_commits, ".
		"SUM(cvs_adds) AS cvs_adds ".
		"FROM stats_project ".
		"WHERE ( (( month = " . $year . $month . " AND day >= " . $day . " ) OR ".
		"( month > " . $year . $month . " )) AND group_id = " . $group_id . " ) ".
		"GROUP BY month,day ".
		"ORDER BY month DESC, day DESC";
*/
	$sql  = "SELECT month,day,group_ranking,group_metric,downloads, site_views + subdomain_views AS page_views, ".
		"msg_posted,bugs_opened,bugs_closed,support_opened,support_closed,patches_opened, ".
		"patches_closed,tasks_opened,tasks_closed,cvs_commits,cvs_adds ".
		"FROM stats_project ".
		"WHERE ( (( month = " . $year . $month . " AND day >= " . $day . " ) OR ".
		"( month > " . $year . $month . " )) AND group_id = " . $group_id . " ) ".
		"ORDER BY month DESC, day DESC";

	   // Executions will continue until morale improves.
	$res = db_query( $sql );

	   // if there are any days, we have valid data.
	if ( ($valid_days = db_numrows( $res )) > 0 ) {

		print '<P><B>Statistics for the past ' . $valid_days . ' days</B></P>';

		print	'<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>'
			. '<TR valign="top">'
			. '<TD><B>Date</B></TD>'
			. '<TD><B>Rank</B></TD>'
			. '<TD align="right"><B>Page Views</B></TD>'
			. '<TD align="right"><B>D/l</B></TD>'
			. '<TD align="right"><B>Bugs</B></TD>'
			. '<TD align="right"><B>Support</B></TD>'
			. '<TD align="right"><B>Patches</B></TD>'
			. '<TD align="right"><B>Tasks</B></TD>'
			. '<TD align="right"><B>CVS</B></TD>'
			. '</TR>' . "\n";
		
		while ( $row = db_fetch_array($res) ) {
			$i++;
			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . gmstrftime("%e %b %Y", gmmktime(0,0,0,substr($row["month"],4,2),$row["day"],substr($row["month"],0,4)) ) . '</TD>'
				//. '<TD>' . $row["month"] . " " . $row["day"] . '</TD>'
				. '<TD>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </TD>'
				. '<TD align="right">' . number_format( $row["page_views"] ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"] ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["bugs_opened"] . " ( " . $row["bugs_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["support_opened"] . " ( " . $row["support_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["patches_opened"] . " ( " . $row["patches_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["tasks_opened"] . " ( " . $row["tasks_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["cvs_commits"] . '</TD>'
				. '</TR>' . "\n";
		}

		print '</TABLE>';

	} else {
		echo "Project did not exist on this date.<P>";
		echo db_error();
	}

}


   // stats_project_weekly
function stats_project_weekly( $group_id, $span = 8 ) {

	if (! $span ) { 
		$span = 8;
	}

	   // Get information about the date $span weeks ago 
	$begin_date = localtime( (time() - ($span * 7 * 86400)), 1);
	$week = gmstrftime("%U", (time() - ($span * 7 * 86400)) );
	$year = $begin_date["tm_year"] + 1900;
	$month = sprintf("%02d", $begin_date["tm_mon"] + 1);
	$day = $begin_date["tm_mday"];

	$sql  = "SELECT month,week,AVG(group_ranking) AS group_ranking, ".
		"AVG(group_metric) AS group_metric, ".
		"SUM(downloads) AS downloads, ".
		"SUM(site_views + subdomain_views) AS page_views, ".
		"SUM(msg_posted) AS msg_posted, ".
		"SUM(bugs_opened) AS bugs_opened, ".
		"SUM(bugs_closed) AS bugs_closed, ".
		"SUM(support_opened) AS support_opened, ".
		"SUM(support_closed) AS support_closed, ".
		"SUM(patches_opened) AS patches_opened, ".
		"SUM(patches_closed) AS patches_closed, ".
		"SUM(tasks_opened) AS tasks_opened, ".
		"SUM(tasks_closed) AS tasks_closed, ".
		"SUM(cvs_commits) AS cvs_commits, ".
		"SUM(cvs_adds) AS cvs_adds ".
		"FROM stats_project ".
		"WHERE ( (( month > " . $year . "00 AND week > " . $week . " ) OR ( month > " . $year . $month . ")) ".
		"AND group_id = " . $group_id . " ) ".
		"GROUP BY month,week ORDER BY month DESC, week DESC";

	   // Executions will continue until morale improves.
	$res = db_query( $sql );

	   // if there are any weeks, we have valid data.
	if ( ($valid_weeks = db_numrows( $res )) > 0 ) {

		print '<P><B>Statistics for the past ' . ($valid_weeks - 1) . ' weeks, plus the week-in-progress.</B></P>';

		print	'<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>'
			. '<TR valign="top">'
			. '<TD><B>Week</B></TD>'
			. '<TD><B>Rank</B></TD>'
			. '<TD align="right"><B>Page Views</B></TD>'
			. '<TD align="right"><B>D/l</B></TD>'
			. '<TD align="right"><B>Bugs</B></TD>'
			. '<TD align="right"><B>Support</B></TD>'
			. '<TD align="right"><B>Patches</B></TD>'
			. '<TD align="right"><B>Tasks</B></TD>'
			. '<TD align="right"><B>CVS</B></TD>'
			. '</TR>' . "\n";

		$today = time();

		while ( $row = db_fetch_array($res) ) {
			$i++;

			$w_begin = $w_end = 0;
			list($w_begin, $w_end) = week_to_dates($row["week"]);
			//if ( $w_end > $today ) {
			//	$w_end = $today;
			//}

			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . $row["week"] . "&nbsp;(" . gmstrftime("%D", $w_begin) . " -> " . strftime("%D", $w_end) . ') </TD>'
				. '<TD>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </TD>'
				. '<TD align="right">' . number_format( $row["page_views"] ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"] ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["bugs_opened"] . " ( " . $row["bugs_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["support_opened"] . " ( " . $row["support_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["patches_opened"] . " ( " . $row["patches_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["tasks_opened"] . " ( " . $row["tasks_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["cvs_commits"] . '</TD>'
				. '</TR>' . "\n";
		}

		print '</TABLE>';

	} else {
		echo "Project did not exist on this date.";
		echo db_error();
	}

}

?><?php

   // stats_project_monthly
function stats_project_monthly( $group_id, $span = 4 ) {

	if (! $span ) { 
		$span = 4;
	}

	   // Get information about the date $span months ago 
	$begin_date = localtime( time(), 1 );
	$year = $begin_date["tm_year"] + 1900;
	$month = $begin_date["tm_mon"] + 1 - $span;
	while ( $month < 1 ) {
		$month += 12;
		$year -= 1;
	}

	$sql  = "SELECT month,AVG(group_ranking) AS group_ranking,".
		"AVG(group_metric) AS group_metric, ".
		"SUM(downloads) AS downloads, ".
		"SUM(site_views + subdomain_views) AS page_views, ".
		"SUM(msg_posted) AS msg_posted, ".
		"SUM(bugs_opened) AS bugs_opened, ".
		"SUM(bugs_closed) AS bugs_closed, ".
		"SUM(support_opened) AS support_opened, ".
		"SUM(support_closed) AS support_closed, ".
		"SUM(patches_opened) AS patches_opened, ".
		"SUM(patches_closed) AS patches_closed, ".
		"SUM(tasks_opened) AS tasks_opened, ".
		"SUM(tasks_closed) AS tasks_closed, ".
		"SUM(cvs_commits) AS cvs_commits, ".
		"SUM(cvs_adds) AS cvs_adds ".
		"FROM stats_project ".
		"WHERE ( month > " . $year . sprintf("%02d", $month) . " AND group_id = " . $group_id . " ) ".
		"GROUP BY month ORDER BY month DESC";

	   // Executions will continue until morale improves.
	$res = db_query( $sql );

	   // if there are any weeks, we have valid data.
	if ( ($valid_months = db_numrows( $res )) > 1 ) {

		print '<P><B>Statistics for the past ' . $valid_months . ' months.</B></P>';

		print	'<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>'
			. '<TR valign="top">'
			. '<TD><B>Month</B></TD>'
			. '<TD><B>Rank</B></TD>'
			. '<TD align="right"><B>Page Views</B></TD>'
			. '<TD align="right"><B>D/l</B></TD>'
			. '<TD align="right"><B>Bugs</B></TD>'
			. '<TD align="right"><B>Support</B></TD>'
			. '<TD align="right"><B>Patches</B></TD>'
			. '<TD align="right"><B>Tasks</B></TD>'
			. '<TD align="right"><B>CVS</B></TD>'
			. '</TR>' . "\n";

		while ( $row = db_fetch_array($res) ) {
			$i++;

			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . gmstrftime("%B %Y", mktime(0,0,1,substr($row["month"],4,2),1,substr($row["month"],0,4)) ) . '</TD>'
				. '<TD>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </TD>'
				. '<TD align="right">' . number_format( $row["page_views)"] ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"] ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["bugs_opened"] . " ( " . $row["bugs_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["support_opened"] . " ( " . $row["support_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["patches_opened"] . " ( " . $row["patches_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["tasks_opened"] . " ( " . $row["tasks_closed"] . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . $row["cvs_commits"] . '</TD>'
				. '</TR>' . "\n";
		}

		print '</TABLE>';

	} else {
		echo "Project did not exist on this date.";
		echo db_error();
	}
}


// stats_site_alltime
function stats_site_agregate( $group_id ) {

	$sql  = "SELECT group_id,COUNT(day) AS count, ".
		"AVG(group_ranking) AS group_ranking, ".
		"AVG(group_metric) AS group_metric, ".
		"SUM(downloads) AS downloads, ".
		"SUM(site_views + subdomain_views) AS page_views, ".
		"AVG(developers) AS developers, ".
		"SUM(msg_posted) AS msg_posted, ".
		"SUM(bugs_opened) AS bugs_opened, ".
		"SUM(bugs_closed) AS bugs_closed, ".
		"SUM(support_opened) AS support_opened,".
		"SUM(support_closed) AS support_closed, ".
		"SUM(patches_opened) AS patches_opened, ".
		"SUM(patches_closed) AS patches_closed, ".
		"SUM(tasks_opened) AS tasks_opened, ".
		"SUM(tasks_closed) AS tasks_closed, ".
		"SUM(cvs_commits) AS cvs_commits, ".
		"SUM(cvs_adds) AS cvs_adds ".
		"FROM stats_project ".
		"WHERE group_id = " . $group_id . " ".
		"GROUP BY group_id ";


	   // Executions will continue until morale improves.
	$res = db_query( $sql );
	$row = db_fetch_array($res);

	   // if there are any days, we have valid data.
	if ( 1 ) {

		print '<P><B>Statistics for All Time</B></P>';

		print	'<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>'
			. '<TR valign="top">'
			. '<TD><B>Lifespan</B></TD>'
			. '<TD><B>Rank</B></TD>'
			. '<TD align="right"><B>Page Views</B></TD>'
			. '<TD align="right"><B>D/l</B></TD>'
			. '<TD align="right"><B>Developers</B></TD>'
			. '<TD align="right"><B>Bugs</B></TD>'
			. '<TD align="right"><B>Support</B></TD>'
			. '<TD align="right"><B>Patches</B></TD>'
			. '<TD align="right"><B>Tasks</B></TD>'
			. '<TD align="right"><B>CVS</B></TD>'
			. '</TR>' . "\n";

		print	'<TR bgcolor="' . html_get_alt_row_color(1) . '">'
			. '<TD>' . $row["day"] . ' days </TD>'
			. '<TD>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </TD>'
			. '<TD align="right">' . number_format( $row["page_views"] ) . '</TD>'
			. '<TD align="right">' . number_format( $row["downloads"] ) . '</TD>'
			. '<TD align="right">' . $row["developers"] . '</TD>'
			. '<TD align="right">' . $row["bugs_opened"] . " ( " . $row["bugs_closed"] . ' )</TD>'
			. '<TD align="right">' . $row["support_opened"] . " ( " . $row["support_closed"] . ' )</TD>'
			. '<TD align="right">' . $row["patches_opened"] . " ( " . $row["patches_closed"] . ' )</TD>'
			. '<TD align="right">' . $row["tasks_opened"] . " ( " . $row["tasks_closed"] . ' )</TD>'
			. '<TD align="right">' . $row["cvs_commits"] . '</TD>'
			. '</TR>' . "\n";

		print '</TABLE>';

	} else {
		echo "Project does not seem to exist.";
	}
}


function period2seconds($period_name,$span) {
	if (!$period_name || $period_name=="lifespan") {
		return "";
	}

	if (!$span) $span=1;

	if ($period_name=="day") {
		return 60*60*24*$span;
	} else if ($period_name=="week") {
		return 60*60*24*7*$span;
	} else if ($period_name=="month") {
		return 60*60*24*30*$span;
	} else if ($period_name=="year") {
		return 60*60*24*365*$span;
	}
}

function period2sql($period_name,$span,$field_name) {
	$time_now=time();
	$seconds=period2seconds($period_name,$span);

	if (!$seconds) return "";

	return "AND $field_name>=" . (string)($time_now-$seconds) ." \n";
}

?>
