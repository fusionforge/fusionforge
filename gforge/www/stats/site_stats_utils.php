<?php
/**
  *
  * SourceForge Sitewide Statistics - stats common module
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


function stats_util_sum_array( $sum, $add ) {
	while( list( $key, $val ) = each( $add ) ) {
		$sum[$key] += $val;
	}
	return $sum;
}

/**
 *	generates the trove list in a select box format.
 *	contains the odd choices of "-2" and "-1" which mean "All projects
 *	and "special project list" respectively
 */
function stats_generate_trove_pulldown( $selected_id = 0 ) {
	
	$res = db_query("
		SELECT trove_cat_id,fullpath
		FROM trove_cat
		ORDER BY fullpath");
	
	print '
		<SELECT name="trovecatid">';

		print '
			<OPTION VALUE="-2">All Projects</OPTION>
			<OPTION VALUE="-1">Special Project List</OPTION>';

	while ( $row = db_fetch_array($res) ) {
		print	'
			<OPTION value="' . $row['trove_cat_id'] . '"'
			. ( $selected_id == $row["trove_cat_id"] ? " SELECTED" : "" )
			. ">" . $row["fullpath"] . '</OPTION>';
	}

	print '
		</SELECT>';
}


function stats_trove_cat_to_name( $trovecatid ) {

	$res = db_query("
		SELECT fullpath
		FROM trove_cat
		WHERE trove_cat_id = '$trovecatid'");

	if ( $row = db_fetch_array($res) ) {
		return $row["fullpath"];
	} else { 
		return " ( $trovecatid returned no category name ) ";
	}
}


function stats_generate_trove_grouplist( $trovecatid ) {
	
	$results = array();

	$res = db_query("
		SELECT *
		FROM trove_group_link
		WHERE trove_cat_id='$trovecatid'");

	print db_error( $res );

	$i = 0;
	while ( $row = db_fetch_array($res) ) {
		$results[$i++] = $row["group_id"];
	}

	return $results;
}


function stats_site_projects_form( $report='last_30', $orderby = 'downloads', $projects = 0, $trovecat = 0 ) {
	
	print '<FORM action="projects.php" method="get">' . "\n";
	print '<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#eeeeee">' . "\n";

	print '<tr><td><b>Projects in trove category: </b></td><td>';
	stats_generate_trove_pulldown( $trovecat );
	print '</td></tr>';

	print '<tr><td><b>OR enter Special Project List: </b></td>';
	print '<td> <INPUT type="text" width="100" name="projects" value="'. $projects . '">';
	print '  (<B>comma separated</B> group_id\'s) </td></tr>';

	print '<tr><td><b>Report: </b></td><td>';

	$reports_ids=array();
	$reports_ids[]='last_30';
	$reports_ids[]='all';

	$reports_names=array();
	$reports_names[]='Last 30 Days';
	$reports_names[]='All Time';

	echo html_build_select_box_from_arrays($reports_ids, $reports_names, 'report', $report, false);

	print ' </td></tr>';

	print '<tr><td><b>View By: </b></td><td>';
	$orderby_vals = array("downloads",
				"site_views",
				"subdomain_views",
				"msg_posted",
				"bugs_opened",
				"bugs_closed",
				"support_opened",
				"support_closed",
				"patches_opened",
				"patches_closed",
				"tasks_opened",
				"tasks_closed",
				"cvs_checkouts",
				"cvs_commits",
				"cvs_adds");

	print html_build_select_box_from_arrays ( $orderby_vals, $orderby_vals, "orderby", $orderby, false );
	print '</td></tr>';

	print '<tr><td colspan="2" align="center"> <INPUT type="submit" value="Generate Report"> </td></tr>';

	print '</table>' . "\n";
	print '</FORM>' . "\n";

}

/**
 *	New function to separate out the SQL so it may be reused in other
 *	potential reports.
 *
 */
function stats_site_project_result( $report, $orderby, $projects, $trove ) {

	//
	//	Determine if we are looking at ALL projects, 
	//	a trove category, or a specific list
	//
	if ($trove == '-2') {
		//do a query of ALL groups
		$grp_str='';
	} elseif ($trove == '-1') {
		//do a query of just a specific list of passed in groups
		$grp_str=" AND g.group_id IN (" . $projects . ") ";
	} else {
		//do a query of 
		$grp_str=" AND EXISTS 
			(SELECT group_id 
				FROM trove_group_link 
				WHERE trove_cat_id ='$trove' 
				AND g.group_id=trove_group_link.group_id) ";
	}

	if ($report == 'last_30') {

		$sql = "SELECT g.group_id, 
		g.group_name,
		SUM(s.downloads) AS downloads, 
		SUM(s.site_views) AS site_views, 
		SUM(s.subdomain_views) AS subdomain_views, 
		SUM(s.msg_posted) AS msg_posted, 
		SUM(s.bugs_opened) AS bugs_opened, 
		SUM(s.bugs_closed) AS bugs_closed, 
		SUM(s.support_opened) AS support_opened, 
		SUM(s.support_closed) AS support_closed, 
		SUM(s.patches_opened) AS patches_opened, 
		SUM(s.patches_closed) AS patches_closed, 
		SUM(s.tasks_opened) AS tasks_opened, 
		SUM(s.tasks_closed) AS tasks_closed, 
		SUM(s.cvs_checkouts) AS cvs_checkouts, 
		SUM(s.cvs_commits) AS cvs_commits, 
		SUM(s.cvs_adds) AS cvs_adds 
		FROM 
			stats_project_last_30 s, groups g
		WHERE 
			s.group_id = g.group_id
			$grp_str
		GROUP BY g.group_id, g.group_name
		ORDER BY $orderby DESC ";

	} else {

		$sql = "SELECT g.group_id, 
	   	g.group_name,
		s.downloads, 
		s.site_views, 
		s.subdomain_views, 
		s.msg_posted, 
		s.bugs_opened,
		s.bugs_closed,
		s.support_opened,
		s.support_closed,
		s.patches_opened,
		s.patches_closed,
		s.tasks_opened,
		s.tasks_closed,
		s.cvs_checkouts,
		s.cvs_commits,
		s.cvs_adds
		FROM 
			stats_project_all s, groups g
		WHERE 
			s.group_id = g.group_id
			$grp_str
		ORDER BY $orderby DESC ";

	}

	return db_query( $sql, 50, 0, SYS_DB_STATS);

}

function stats_site_projects( $report, $orderby, $projects, $trove ) {

	$res=stats_site_project_result( $report, $orderby, $projects, $trove );

	   // if there are any rows, we have valid data (or close enough).
	if ( db_numrows( $res ) > 1 ) {

		?>
		<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>

		<TR valign="top" bgcolor="#eeeeee">
			<TD><B>Group Name</B></TD>
			<TD align="right" COLSPAN="2"><B>Page Views</B></TD>
			<TD align="right"><B>Downloads</B></TD>
			<TD align="right" COLSPAN="2"><B>Bugs</B></TD>
			<TD align="right" COLSPAN="2"><B>Support</B></TD>
			<TD align="right" COLSPAN="2"><B>Patches</B></TD>
			<TD align="right" COLSPAN="2"><B>All Trkr</B></TD>
			<TD align="right" COLSPAN="2"><B>Tasks</B></TD>
			<TD align="right" COLSPAN="3"><B>CVS</B></TD>
		</TR>

		<?php

		// Build the query string to resort results.
		$uri_string = "projects.php?report=" . $report;
		if ( $trove_cat > 0 ) {
			$uri_string .= "&trovecatid=" . $trove_cat;
		}
		if ( $trove_cat == -1 ) { 
			$uri_string .= "&projects=" . urlencode( implode( " ", $projects) );
		}
		$uri_string .= "&orderby=";

		?>
		<TR valign="top" bgcolor="#eeeeee">
			<TD align="right">&nbsp;</TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>site_views">Site</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>subdomain_views">Subdomain</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>downloads">Total</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>bugs_opened">Opn</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>bugs_closed">Cls</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>support_opened">Opn</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>support_closed">Cls</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>patches_opened">Opn</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>patches_closed">Cls</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>artifacts_opened">Opn</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>artifacts_closed">Cls</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>tasks_opened">Opn</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>tasks_closed">Cls</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>cvs_checkouts">CO's</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>cvs_commits">Comm's</A></TD>
			<TD align="right"><A HREF="<?php echo $uri_string; ?>cvs_adds">Adds</A></TD>
			</TR>
		<?php
	
		$i = $offset;	
		while ( $row = db_fetch_array($res) ) {
			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . ($i + 1) . '. <A HREF="/project/stats/?group_id=' . $row["group_id"] . '">' . $row["group_name"] . '</A></TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["site_views"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["subdomain_views"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["downloads"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["bugs_opened"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["bugs_closed"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["support_opened"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["support_closed"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["patches_opened"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["patches_closed"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["artifacts_opened"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["artifacts_closed"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["tasks_opened"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["tasks_opened"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["cvs_checkouts"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["cvs_commits"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format( $row["cvs_adds"],0 ) . '</TD>'
				. '</TR>' . "\n";
			$i++;
			$sum = stats_util_sum_array( $sum, $row );
		}

		?>
		</TABLE>
		<?php

	} else {
		echo "Query returned no valid data.\n";
		echo "<BR><HR><BR>\n $sql \n<BR><HR><BR>\n\n";
		echo db_error();
	}

}

?><?php

function stats_site_projects_daily( $span ) {

	//
	//  We now only have 30 & 7-day views
	//
	if ( $span != 30 && $span != 7) {
		$span = 7;
	}

	$sql="SELECT * FROM stats_site_last_30 
		ORDER BY month DESC, day DESC";

	if ($span == 30) {
		$res = db_query($sql, -1, 0, SYS_DB_STATS);
	} else {
		$res = db_query($sql,  7, 0, SYS_DB_STATS);
	}

	echo db_error();

	   // if there are any weeks, we have valid data.
	if ( ($valid_days = db_numrows( $res )) > 1 ) {

		?>
		<P><B>Statistics for the past <?php echo $valid_days; ?> days.</B></P>

		<P>
		<TABLE width="100%" cellpadding=0 cellspacing=0 border=0>
			<TR valign="top">
			<TD><B>Day</B></TD>
			<TD align="right"><B>Site Views</B></TD>
			<TD align="right"><B>Subdomain Views</B></TD>
			<TD align="right"><B>Downloads</B></TD>
			<TD align="right"><B>Bugs</B></TD>
			<TD align="right"><B>Support</B></TD>
			<TD align="right"><B>Patches</B></TD>
			<TD align="right"><B>Tasks</B></TD>
			<TD align="right"><B>CVS</B></TD>
			</TR>
		<?php

		while ( $row = db_fetch_array($res) ) {
			$i++;

			print	'<TR bgcolor="' . html_get_alt_row_color($i) . '">'
				. '<TD>' . gmstrftime("%d %b %Y", mktime(0,0,1,substr($row["month"],4,2),$row["day"],substr($row["month"],0,4)) ) . '</TD>'
				. '<TD align="right">' . number_format( $row["site_page_views"],0 ) . '</TD>'
				. '<TD align="right">' . number_format( $row["subdomain_views"],0 ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " (" . number_format($row["bugs_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " (" . number_format($row["support_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " (" . number_format($row["patches_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " (" . number_format($row["tasks_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["cvs_checkouts"],0) . " (" . number_format($row["cvs_commits"],0) . ')</TD>'
				. '</TR>' . "\n";
		}

		?>
		</TABLE>
		<?php

	} else {
		echo "No data.";
	}
}

function stats_site_projects_monthly() {

	$sql="SELECT * FROM stats_site_months
		ORDER BY month DESC";

	$res=db_query($sql, -1, 0, SYS_DB_STATS);

	echo db_error();

	   // if there are any weeks, we have valid data.
	if ( ($valid_months = db_numrows( $res )) > 1 ) {

		?>
		<P><B>Statistics for the past <?php echo $valid_months; ?> months.</B></P>

		<P>
		<TABLE width="100%" cellpadding=0 cellspacing=0 border=0>
			<TR valign="top">
			<TD><B>Month</B></TD>
			<TD align="right"><B>Site Views</B></TD>
			<TD align="right"><B>Subdomain Views</B></TD>
			<TD align="right"><B>Downloads</B></TD>
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
				. '<TD>' . $row['month'] . '</TD>'
				. '<TD align="right">' . number_format( $row["site_page_views"],0 ) . '</TD>'
				. '<TD align="right">' . number_format( $row["subdomain_views"],0 ) . '</TD>'
				. '<TD align="right">' . number_format( $row["downloads"],0 ) . '</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " (" . number_format($row["bugs_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " (" . number_format($row["support_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " (" . number_format($row["patches_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["artifacts_opened"],0) . " (" . number_format($row["artifacts_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " (" . number_format($row["tasks_closed"],0) . ')</TD>'
				. '<TD align="right">&nbsp;&nbsp;' . number_format($row["cvs_checkouts"],0) . " (" . number_format($row["cvs_commits"],0) . ')</TD>'
				. '</TR>' . "\n";
		}

		?>
		</TABLE>
		<?php

	} else {
		echo "No data.";
	}
}

function stats_site_agregate( ) {

	$res = db_query("SELECT * FROM stats_site_all", -1, 0, SYS_DB_STATS);
	$site_totals = db_fetch_array($res);

	$sql	= "SELECT COUNT(*) AS count FROM groups WHERE status='A'";
	$res = db_query( $sql );
	$groups = db_fetch_array($res);

	$sql	= "SELECT COUNT(*) AS count FROM users WHERE status='A'";
	$res = db_query( $sql );
	$users = db_fetch_array($res);
	

	?>
	<P><B>Current Agregate Statistics for All Time</B></P>

	<P><TABLE width="100%" cellpadding=0 cellspacing=0 border=0>
	<TR valign="top">
		<TD><B>Site Views</B></TD>
		<TD><B>Subdomain Views</B></TD>
		<TD><B>Downloads</B></TD>
		<TD><B>Developers</B></TD>
		<TD><B>Projects</B></TD>
	</TR>

	<TR>
		<TD><?php echo number_format( $site_totals["site_page_views"],0 ); ?></TD>
		<TD><?php echo number_format( $site_totals["subdomain_views"],0 ); ?></TD>
		<TD><?php echo number_format( $site_totals["downloads"],0 ); ?></TD>
		<TD><?php echo number_format( $users["count"],0 ); ?></TD>
		<TD><?php echo number_format( $groups["count"],0 ); ?></TD>
		</TR>

	</TABLE>
	<?php
}


?>
