<?php
/**
  *
  * Project Statistics Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

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

	//
	//	We now only have 30 & 7-day views
	//
	if ( $span != 30 && $span != 7) { 
		$span = 7;
	}

	$sql="SELECT * FROM stats_project_last_30 
		WHERE group_id='$group_id' ORDER BY month DESC, day DESC";

	if ($span == 30) {
		$res = db_query($sql, -1, 0, SYS_DB_STATS);
	} else {
		$res = db_query($sql,  7, 0, SYS_DB_STATS);
	}

	echo db_error();

   // if there are any days, we have valid data.
	if ( ($valid_days = db_numrows( $res )) > 0 ) {
		?>
		<P><B>Statistics for the past <?php echo $valid_days; ?> days</B></P>

		<P><TABLE width="100%" cellpadding=0 cellspacing=1 border=0>
			<TR valign="top">
			<TD><B>Date</B></TD>
			<TD><B>Rank</B></TD>
			<TD align="right"><B>Page Views</B></TD>
			<TD align="right"><B>D/l</B></TD>
			<TD align="right"><B>Bugs</B></TD>
			<TD align="right"><B>Support</B></TD>
			<TD align="right"><B>Patches</B></TD>
			<TD align="right"><B>All Trkr</B></TD>
			<TD align="right"><B>Tasks</B></TD>
			<TD align="right"><B>CVS</B></TD>
			</TR>

		<?php
		
		while ( $row = db_fetch_array($res) ) {
			$i++;
			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . gmstrftime("%e %b %Y", gmmktime(0,0,0,substr($row["month"],4,2),$row["day"],substr($row["month"],0,4)) ) . '</TD>'
				//. '<TD>' . $row["month"] . " " . $row["day"] . '</TD>'
				. '<TD>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </TD>'
				. '<TD align="right">' . number_format( $row["subdomain_views"] + $row['site_views'],0 ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " ( " . number_format($row["bugs_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " ( " . number_format($row["support_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " ( " . number_format($row["patches_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["artifacts_opened"],0) . " ( " . number_format($row["artifacts_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " ( " . number_format($row["tasks_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["cvs_commits"],0) . '</TD>'
				. '</TR>' . "\n";
		}

		?>
		</TABLE>
		<?php

	} else {
		echo "Project did not exist on this date.<P>";
		echo db_error();
	}

}

   // stats_project_monthly
function stats_project_monthly( $group_id ) {

	$res = db_query("
		SELECT * FROM stats_project_months 
		WHERE group_id='$group_id'
		ORDER BY group_id DESC, month DESC
	", -1, 0, SYS_DB_STATS);

	   // if there are any weeks, we have valid data.
	if ( ($valid_months = db_numrows( $res )) > 1 ) {

		?>
		<P><B>Statistics for the past <?php echo $valid_months; ?> months.</B></P>

		<P><TABLE width="100%" cellpadding=0 cellspacing=1 border=0>
			<TR valign="top">
			<TD><B>Month</B></TD>
			<TD><B>Rank</B></TD>
			<TD align="right"><B>Page Views</B></TD>
			<TD align="right"><B>D/l</B></TD>
			<TD align="right"><B>Bugs</B></TD>
			<TD align="right"><B>Support</B></TD>
			<TD align="right"><B>Patches</B></TD>
			<TD align="right"><B>All Trkr</B></TD>
			<TD align="right"><B>Tasks</B></TD>
			<TD align="right"><B>CVS</B></TD>
			</TR>

		<?php

		while ( $row = db_fetch_array($res) ) {
			$i++;

			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . gmstrftime("%B %Y", mktime(0,0,1,substr($row["month"],4,2),1,substr($row["month"],0,4)) ) . '</TD>'
				. '<TD>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </TD>'
				. '<TD align="right">' . number_format( $row["subdomain_views"] + $row['site_views'],0 ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " ( " . number_format($row["bugs_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " ( " . number_format($row["support_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " ( " . number_format($row["patches_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["artifacts_opened"],0) . " ( " . number_format($row["artifacts_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " ( " . number_format($row["tasks_closed"],0) . ' )</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["cvs_commits"],0) . '</TD>'
				. '</TR>' . "\n";
		}

		?>
		</TABLE>
		<?php

	} else {
		echo "Project did not exist on this date.";
		echo db_error();
	}
}

function stats_project_all( $group_id ) {

	$res = db_query("
		SELECT *
		FROM stats_project_all
		WHERE group_id='$group_id'
	", -1, 0, SYS_DB_STATS);
	$row = db_fetch_array($res);
//	echo db_error();

	?>
	<P><B>Statistics for All Time</B></P>

	<P><TABLE width="100%" cellpadding=0 cellspacing=1 border=0>
		<TR valign="top">
		<TD><B>Lifespan</B></TD>
		<TD><B>Rank</B></TD>
		<TD align="right"><B>Page Views</B></TD>
		<TD align="right"><B>D/l</B></TD>
		<TD align="right"><B>Bugs</B></TD>
		<TD align="right"><B>Support</B></TD>
		<TD align="right"><B>Patches</B></TD>
		<TD align="right"><B>All Trkr</B></TD>
		<TD align="right"><B>Tasks</B></TD>
		<TD align="right"><B>CVS</B></TD>
		</TR>

	<TR bgcolor="<?php echo html_get_alt_row_color(1); ?>">
		<TD><?php echo $row["day"]; ?> days </TD>
		<TD><?php echo sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]); ?> ) </TD>
		<TD align="right"><?php echo number_format( $row["subdomain_views"] + $row['site_views'],0); ?></TD>
		<TD align="right"><?php echo number_format( $row["downloads"],0); ?></TD>
		<TD align="right"><?php echo number_format($row["bugs_opened"],0) . " ( " . number_format($row["bugs_closed"],0); ?> )</TD>
		<TD align="right"><?php echo number_format($row["support_opened"],0) . " ( " . number_format($row["support_closed"],0); ?> )</TD>
		<TD align="right"><?php echo number_format($row["patches_opened"],0) . " ( " . number_format($row["patches_closed"],0); ?> )</TD>
		<TD align="right"><?php echo number_format($row["artifacts_opened"],0) . " ( " . number_format($row["artifacts_closed"],0); ?> )</TD>
		<TD align="right"><?php echo number_format($row["tasks_opened"],0) . " ( " . number_format($row["tasks_closed"],0); ?> )</TD>
		<TD align="right"><?php echo number_format($row["cvs_commits"],0); ?></TD>
		</TR>

	</TABLE>

	<?php

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
