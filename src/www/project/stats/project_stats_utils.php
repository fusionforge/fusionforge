<?php
/**
 * Project Statistics Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
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
	global $HTML;

	//
	//	We now only have 30 & 7-day views
	//
	if ( $span != 30 && $span != 7) {
		$span = 7;
	}

	$sql="SELECT * FROM stats_project_vw
		WHERE group_id=$1 ORDER BY month DESC, day DESC";

	if ($span == 30) {
		$res = db_query_params($sql, array($group_id), 30, 0, 'DB_STATS');
	} else {
		$res = db_query_params($sql, array($group_id),  7, 0, 'DB_STATS');
	}

	echo db_error('DB_STATS');

   // if there are any days, we have valid data.
	if ( ($valid_days = db_numrows( $res )) > 0 ) {
		?>
		<p><strong><?php printf(_('Statistics for the past %1$s days'), $valid_days ) ?></strong></p>

		<p><table width="100%" cellpadding="0" cellspacing="1" border="0">
			<tr valign="top">
			<td><strong><?php echo _('Date') ?></strong></td>
			<td><strong><?php echo _('Rank') ?></strong></td>
			<td align="right"><strong><?php echo _('Page Views') ?> </strong></td>
			<td align="right"><strong><?php echo _('D/l') ?></strong></td>
			<td align="right"><strong><?php echo _('Bugs') ?></strong></td>
			<td align="right"><strong><?php echo _('Support') ?></strong></td>
			<td align="right"><strong><?php echo _('Patches') ?></strong></td>
			<td align="right"><strong><?php echo _('All Trkr') ?></strong></td>
			<td align="right"><strong><?php echo _('Tasks') ?></strong></td>
			<td align="right"><strong><?php echo _('CVS') ?></strong></td>
			</tr>

		<?php
		$i=0;
		while ( $row = db_fetch_array($res) ) {
			$i++;
			print	'<tr ' . $HTML->boxGetAltRowStyle($i) . '>'
				. '<td>' . gmstrftime("%e %b %Y", gmmktime(0,0,0,substr($row["month"],4,2),$row["day"],substr($row["month"],0,4)) ) . '</td>'
				//. '<td>' . $row["month"] . " " . $row["day"] . '</td>'
				. '<td>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </td>'
				. '<td align="right">' . number_format( $row["subdomain_views"] + $row['site_views'],0 ) . '</td>'
				. '<td align="right">' . number_format( $row["downloads"],0 ) . '</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " ( " . number_format($row["bugs_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " ( " . number_format($row["support_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " ( " . number_format($row["patches_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["artifacts_opened"],0) . " ( " . number_format($row["artifacts_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " ( " . number_format($row["tasks_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["cvs_commits"],0) . '</td>'
				. '</tr>' . "\n";
		}

		?>
		</table></p>
		<?php

	} else {
		echo _('Project did not exist on this date.');
		echo db_error('DB_STATS') .'</p>';
	}

}

   // stats_project_monthly
function stats_project_monthly( $group_id ) {
	global $HTML;
	$res = db_query_params("
		SELECT * FROM stats_project_months
		WHERE group_id=$1
		ORDER BY group_id DESC, month DESC
	",array($group_id), -1, 0, 'DB_STATS');

	   // if there are any weeks, we have valid data.
	if ( ($valid_months = db_numrows( $res )) > 1 ) {

		?>
		<p><strong><?php printf(_('Statistics for the past %1$s months.'), $valid_months ) ?></strong></p>

		<p><table width="100%" cellpadding="0" cellspacing="1" border="0">
			<tr valign="top">
			<td><strong><?php echo _('Lifespan') ?></strong></td>
			<td><strong><?php echo _('Rank') ?></strong></td>
			<td align="right"><strong><?php echo _('Page Views') ?></strong></td>
			<td align="right"><strong><?php echo _('D/l') ?></strong></td>
			<td align="right"><strong><?php echo _('Bugs') ?></strong></td>
			<td align="right"><strong><?php echo _('Support') ?></strong></td>
			<td align="right"><strong><?php echo _('Patches') ?></strong></td>
			<td align="right"><strong><?php echo _('All Trkr') ?></strong></td>
			<td align="right"><strong><?php echo _('Tasks') ?></strong></td>
			<td align="right"><strong><?php echo _('CVS') ?></strong></td>
			</tr>

		<?php

		while ( $row = db_fetch_array($res) ) {
			$i++;

			print	'<tr ' . $HTML->boxGetAltRowStyle($i) . '>'
				. '<td>' . gmstrftime("%B %Y", mktime(0,0,1,substr($row["month"],4,2),1,substr($row["month"],0,4)) ) . '</td>'
				. '<td>' . sprintf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]) . ' ) </td>'
				. '<td align="right">' . number_format( $row["subdomain_views"] + $row['site_views'],0 ) . '</td>'
				. '<td align="right">' . number_format( $row["downloads"],0 ) . '</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " ( " . number_format($row["bugs_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " ( " . number_format($row["support_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " ( " . number_format($row["patches_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["artifacts_opened"],0) . " ( " . number_format($row["artifacts_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " ( " . number_format($row["tasks_closed"],0) . ' )</td>'
				. '<td align="right">&nbsp;&nbsp;' . number_format($row["cvs_commits"],0) . '</td>'
				. '</tr>' . "\n";
		}

		?>
		</table></p>
		<?php

	} else {
		echo _('Project did not exist on this date.')."<p>";
		echo db_error('DB_STATS');
	}
}

function stats_project_all( $group_id ) {
	global $HTML;
	$res = db_query_params("
		SELECT *
		FROM stats_project_all_vw
		WHERE group_id=$1
	", array($group_id), -1, 0, 'DB_STATS');
	$row = db_fetch_array($res);

	?>
	<p><strong><?php echo _('Statistics for All Time') ?></strong></p>

	<p><table width="100%" cellpadding="0" cellspacing="1" border="0">
		<tr valign="top">
			<td><strong><?php echo _('Lifespan') ?></strong></td>
			<td><strong><?php echo _('Rank') ?></strong></td>
			<td align="right"><strong><?php echo _('Page Views') ?></strong></td>
			<td align="right"><strong><?php echo _('D/l') ?></strong></td>
			<td align="right"><strong><?php echo _('Bugs') ?></strong></td>
			<td align="right"><strong><?php echo _('Support') ?></strong></td>
			<td align="right"><strong><?php echo _('Patches') ?></strong></td>
			<td align="right"><strong><?php echo _('All Trkr') ?> </strong></td>
			<td align="right"><strong><?php echo _('Tasks') ?></strong></td>
			<td align="right"><strong><?php echo _('CVS') ?></strong></td>
		</tr>

	<tr <?php echo $HTML->boxGetAltRowStyle(1); ?>>
		<td><?php echo $row["day"]; ?> <?php echo _('Days') ?> </td>
		<td><?php printf("%d", $row["group_ranking"]) . " ( " . sprintf("%0.2f", $row["group_metric"]); ?> ) </td>
		<td align="right"><?php echo number_format( $row["subdomain_views"] + $row['site_views'],0); ?></td>
		<td align="right"><?php echo number_format( $row["downloads"],0); ?></td>
		<td align="right"><?php echo number_format($row["bugs_opened"],0) . " ( " . number_format($row["bugs_closed"],0); ?> )</td>
		<td align="right"><?php echo number_format($row["support_opened"],0) . " ( " . number_format($row["support_closed"],0); ?> )</td>
		<td align="right"><?php echo number_format($row["patches_opened"],0) . " ( " . number_format($row["patches_closed"],0); ?> )</td>
		<td align="right"><?php echo number_format($row["artifacts_opened"],0) . " ( " . number_format($row["artifacts_closed"],0); ?> )</td>
		<td align="right"><?php echo number_format($row["tasks_opened"],0) . " ( " . number_format($row["tasks_closed"],0); ?> )</td>
		<td align="right"><?php echo number_format($row["cvs_commits"],0); ?></td>
		</tr>

	</table></p>

	<?php

}


function period2seconds($period_name,$span) {
	if (!$period_name || $period_name=="lifespan") {
		return "";
	}

	if (!is_int ($span) || !$span) $span=1;

	if ($period_name=="day") {
		return 60*60*24*$span;
	} else if ($period_name=="week") {
		return 60*60*24*7*$span;
	} else if ($period_name=="month") {
		return 60*60*24*30*$span;
	} else if ($period_name=="year") {
		return 60*60*24*365*$span;
	} else {
		return $span;
	}
}

function period2sql($period_name,$span,$field_name) {
	$time_now=time();
	$seconds=period2seconds($period_name,$span);

	if (!$seconds) return "";

	return "AND $field_name>=" . (string)($time_now-$seconds);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
