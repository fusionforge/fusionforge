<?php
/**
 * Sitewide Statistics
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$allowed_orderby_vals = array('downloads',
			      'site_views',
			      'subdomain_views',
			      'msg_posted',
			      'bugs_opened',
			      'bugs_closed',
			      'support_opened',
			      'support_closed',
			      'patches_opened',
			      'patches_closed',
			      'tasks_opened',
			      'tasks_closed',
			      'cvs_checkouts',
			      'cvs_commits',
			      'cvs_adds');

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
	if (!is_array($sum)) {
		$sum = array();
	}
	while( list( $key, $val ) = each( $add ) ) {
		if (!isset($sum[$key])) {
			$sum[$key] = 0;
		}
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
	$res = db_query_params ('
		SELECT trove_cat_id,fullpath
		FROM trove_cat
		ORDER BY fullpath',
			array());
	
	print '
		<select name="trovecatid">';

		print '
			<option value="-2">'._('All Projects').'</option>
			<option value="-1">'._('Special Projects').'</option>';

	while ( $row = db_fetch_array($res) ) {
		print	'
			<option value="' . $row['trove_cat_id'] . '"'
			. ( $selected_id == $row["trove_cat_id"] ? " selected=\"selected\"" : "" )
			. ">" . $row["fullpath"] . '</option>';
	}

	print '
		</select>';
}


function stats_trove_cat_to_name( $trovecatid ) {

	$res = db_query_params ('
		SELECT fullpath
		FROM trove_cat
		WHERE trove_cat_id = $1',
			array($trovecatid));

	if ( $row = db_fetch_array($res) ) {
		return $row["fullpath"];
	} else { 
		return sprintf(_(" (no category found with ID %d)"), $trovecatid) ;
	}
}


function stats_generate_trove_grouplist( $trovecatid ) {
	
	$results = array();

	$res = db_query_params ('
		SELECT *
		FROM trove_group_link
		WHERE trove_cat_id=$1',
			array($trovecatid));

	print db_error( $res );

	$i = 0;
	while ( $row = db_fetch_array($res) ) {
		$results[$i++] = $row["group_id"];
	}

	return $results;
}


function stats_site_projects_form( $report='last_30', $orderby = 'downloads', $projects = 0, $trovecat = 0 ) {
	global $allowed_orderby_vals ;

	print '<form action="projects.php" method="get">' . "\n";
	print '<table width="100%" cellpadding="0" cellspacing="0" class="tableheading">' . "\n";

	print '<tr><td><strong>'._('Projects in trove category:').'</strong></td><td>';
	stats_generate_trove_pulldown( $trovecat );
	print '</td></tr>';

	print '<tr><td><strong>'._('OR enter Special Project List:').'</strong></td>';
	print '<td> <input type="text" width="100" name="projects" value="'. $projects . '" />';
	print '  ('._('<strong>comma separated</strong> group_id\'s)').'</td></tr>';

	print '<tr><td><strong>'._('Report:').'</strong></td><td>';

	$reports_ids=array();
	$reports_ids[]='last_30';
	$reports_ids[]='all';

	$reports_names=array();
	$reports_names[]=_('last_30');
	$reports_names[]=_('all');

	echo html_build_select_box_from_arrays($reports_ids, $reports_names, 'report', $report, false);

	print ' </td></tr>';

	print '<tr><td><strong>'._('View by:').'</strong></td><td>';

	print html_build_select_box_from_arrays ( $allowed_orderby_vals, $allowed_orderby_vals, "orderby", $orderby, false );
	print '</td></tr>';

	print '<tr><td colspan="2" style="text-align:center"> <input type="submit" value="'._('Generate Report').'" /> </td></tr>';

	print '</table>' . "\n";
	print '</form>' . "\n";

}

/**
 *	New function to separate out the SQL so it may be reused in other
 *	potential reports.
 *
 */
function stats_site_project_result( $report, $orderby, $projects, $trove ) {
	global $allowed_orderby_vals ;

	if (!$orderby) {
		$order_clause = 'group_name ASC' ;
	} else {
		$order_clause = util_ensure_value_in_set ($orderby,
							   $allowed_orderby_vals) ;
		$order_clause .= ' DESC, group_name ASC';
	}

	if ($report == 'last_30') {
		return db_query_params ('
SELECT g.group_id, g.group_name,
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
FROM stats_project_vw s, groups g
WHERE s.group_id = g.group_id
GROUP BY g.group_id, g.group_name
ORDER BY ' . $order_clause,
					array ()) ;
	} else {
		return db_query_params ('
SELECT g.group_id, g.group_name, s.downloads, s.site_views,
       s.subdomain_views, s.msg_posted, s.bugs_opened, s.bugs_closed,
       s.support_opened, s.support_closed, s.patches_opened,
       s.patches_closed, s.tasks_opened, s.tasks_closed,
       s.cvs_checkouts, s.cvs_commits, s.cvs_adds
FROM stats_project_all_vw s, groups g
WHERE s.group_id = g.group_id
ORDER BY ' . $order_clause,
					array ()) ;
	}
}

function stats_site_projects( $report, $orderby, $projects, $trove ) {
	$i=0;
	$offset=0;
	$trove_cat=0;
	$res=stats_site_project_result( $report, $orderby, $projects, $trove );
	// if there are any rows, we have valid data (or close enough).
	if ( db_numrows( $res ) > 1 ) {

		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">

		<tr valign="top" align="right" class="tableheading">
			<td><strong><?php echo _('Project Name'); ?></strong></td>
			<td colspan="2"><strong><?php echo _('Page Views'); ?></strong></td>
			<?php if (forge_get_config('use_frs')) { ?>
			<td><strong><?php echo _('Downloads'); ?></strong></td>
			<?php } ?>
			<?php if (forge_get_config('use_tracker')) { ?>
			<td colspan="2"><strong><?php echo _('Bugs'); ?></strong></td>
			<td colspan="2"><strong><?php echo _('Support'); ?></strong></td>
			<td colspan="2"><strong><?php echo _('Patches'); ?></strong></td>
			<td colspan="2"><strong><?php echo _('All Trkr'); ?></strong></td>
			<?php } ?>
			<?php if (forge_get_config('use_pm')) { ?>
			<td colspan="2"><strong><?php echo _('Tasks'); ?></strong></td>
			<?php } ?>
			<?php if (forge_get_config('use_scm')) { ?>
			<td colspan="3"><strong><?php echo _('SCM'); ?></strong></td>
			<?php } ?>
			<?php plugin_hook('stats_header_table'); ?>
		</tr>

		<?php

		// Build the query string to resort results.
		$uri_string = "projects.php?report=" . $report;
		if ( $trove_cat > 0 ) {
			$uri_string .= "&amp;trovecatid=" . $trove_cat;
		}
		if ( $trove_cat == -1 ) { 
			$uri_string .= "&amp;projects=" . urlencode( implode( " ", $projects) );
		}
		$uri_string .= "&amp;orderby=";

		?>
		<tr valign="top" align="right" class="tableheading">
			<td>&nbsp;</td>
			<td><a href="<?php echo $uri_string; ?>site_views"><?php echo _('Site'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>subdomain_views"><?php echo _('Subdomain'); ?></a></td>
			<?php if (forge_get_config('use_frs')) { ?>
			<td><a href="<?php echo $uri_string; ?>downloads"><?php echo _('Total'); ?></a></td>
			<?php } ?>
			<?php if (forge_get_config('use_tracker')) { ?>
			<td><a href="<?php echo $uri_string; ?>bugs_opened"><?php echo _('Opened'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>bugs_closed"><?php echo _('Closed'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>support_opened"><?php echo _('Opened'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>support_closed"><?php echo _('Closed'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>patches_opened"><?php echo _('Opened'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>patches_closed"><?php echo _('Closed'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>artifacts_opened"><?php echo _('Opened'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>artifacts_closed"><?php echo _('Closed'); ?></a></td>
			<?php } ?>
			<?php if (forge_get_config('use_pm')) { ?>
			<td><a href="<?php echo $uri_string; ?>tasks_opened"><?php echo _('Opened'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>tasks_closed"><?php echo _('Closed'); ?></a></td>
			<?php } ?>
			<?php if (forge_get_config('use_scm')) { ?>
			<td><a href="<?php echo $uri_string; ?>cvs_checkouts"><?php echo _('Checkouts'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>cvs_commits"><?php echo _('Commits'); ?></a></td>
			<td><a href="<?php echo $uri_string; ?>cvs_adds"><?php echo _('Adds'); ?></a></td>
			<?php } ?>
			<?php plugin_hook('stats_detail_header_table'); ?>
			</tr>
		<?php
	
		$i = $offset;	
		while ( $row = db_fetch_array($res) ) {
			print	'<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle($i) . ' align="right">'
				. '<td>' . ($i + 1)." " . util_make_link ('/project/stats/?group_id='.$row["group_id"], $row["group_name"]) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["site_views"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["subdomain_views"],0 ) . '</td>';
			if (forge_get_config('use_frs')) {
				print '<td>&nbsp;&nbsp;' . number_format( $row["downloads"],0 ) . '</td>';
			}
			if (forge_get_config('use_tracker')) {
				print '<td>&nbsp;&nbsp;' . number_format( $row["bugs_opened"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["bugs_closed"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["support_opened"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["support_closed"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["patches_opened"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["patches_closed"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["artifacts_opened"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["artifacts_closed"],0 ) . '</td>';
			}
			if (forge_get_config('use_pm')) {
				print '<td>&nbsp;&nbsp;' . number_format( $row["tasks_opened"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["tasks_opened"],0 ) . '</td>';
			}
			if (forge_get_config('use_scm')) {
				print '<td>&nbsp;&nbsp;' . number_format( $row["cvs_checkouts"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["cvs_commits"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format( $row["cvs_adds"],0 ) . '</td>';
			}
			$hook_params = array();
			$hook_params['group_id'] = $row["group_id"];
			plugin_hook('stats_data',$hook_params);
			print '</tr>' . "\n";
			$i++;
			$sum = stats_util_sum_array( $sum, $row );
		}

		?>
		</table></p>
		<?php

	} else {
		echo _('Query returned no valid data.')."\n";
		echo db_error();
	}

}

?><?php

function stats_site_projects_daily( $span ) {
	$i=0;
	//
	//  We now only have 30 & 7-day views
	//
	$span = util_ensure_value_in_set ($span,
					  array (7, 30)) ;
	$res = db_query_params ('SELECT * FROM stats_site_vw ORDER BY month DESC, day DESC',
				array (),
				$span);
	echo db_error();

	   // if there are any weeks, we have valid data.
	if ( ($valid_days = db_numrows( $res )) > 1 ) {

		?>
		<h2><?php printf(_('Statistics for the past %1$s days'), $valid_days); ?></h2>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr valign="top" align="right">
			<td><strong><?php echo _('Day'); ?></strong></td>
			<td><strong><?php echo _('Site Views'); ?></strong></td>
			<td><strong><?php echo _('Subdomain Views'); ?></strong></td>
			<td><strong><?php echo _('Downloads'); ?></strong></td>
			<td><strong><?php echo _('Bugs'); ?></strong></td>
			<td><strong><?php echo _('Support'); ?></strong></td>
			<td><strong><?php echo _('Patches'); ?></strong></td>
			<td><strong><?php echo _('Tasks'); ?></strong></td>
			<td><strong><?php echo _('SCM'); ?></strong></td>
			</tr>
		<?php
	
		while ( $row = db_fetch_array($res) ) {
			 $i++;

			print	'<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle($i) . ' align="right">'
				. '<td>' . gmstrftime("%d %b %Y", mktime(0,0,1,substr($row["month"],4,2),$row["day"],substr($row["month"],0,4)) ) . '</td>'
				. '<td>' . number_format( $row["site_page_views"],0 ) . '</td>'
				. '<td>' . number_format( $row["subdomain_views"],0 ) . '</td>'
				. '<td>' . number_format( $row["downloads"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " (" . number_format($row["bugs_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " (" . number_format($row["support_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " (" . number_format($row["patches_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " (" . number_format($row["tasks_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["cvs_checkouts"],0) . " (" . number_format($row["cvs_commits"],0) . ')</td>'
				. '</tr>' . "\n";
		}

		?>
		</table>
		<?php

	} else {
		echo _('No Data');
	}
}

function stats_site_projects_monthly() {
	$i=0;

	$res = db_query_params ('SELECT * FROM stats_site_months
		ORDER BY month DESC',
			array ());

	echo db_error();

	// if there are any weeks, we have valid data.
	if ( ($valid_months = db_numrows( $res )) > 1 ) {

		?>

		<h2><?php printf(_('Statistics for the past %1$s months'), $valid_months); ?></h2>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr valign="top" align="right">
			<td><strong><?php echo _('Month'); ?>Month</strong></td>
			<td><strong><?php echo _('Site Views'); ?></strong></td>
			<td><strong><?php echo _('Subdomain Views'); ?></strong></td>
			<td><strong><?php echo _('Downloads'); ?></strong></td>
			<td><strong><?php echo _('Bugs'); ?></strong></td>
			<td><strong><?php echo _('Support'); ?></strong></td>
			<td><strong><?php echo _('Patches'); ?></strong></td>
			<td><strong><?php echo _('All Trkr'); ?></strong></td>
			<td><strong><?php echo _('Tasks'); ?></strong></td>
			<td><strong><?php echo _('SCM'); ?></strong></td>
			</tr>
		<?php

		while ( $row = db_fetch_array($res) ) {
			$i++;

			print	'<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle($i) . ' align="right">'
				. '<td>' . $row['month'] . '</td>'
				. '<td>' . number_format( $row["site_page_views"],0 ) . '</td>'
				. '<td>' . number_format( $row["subdomain_views"],0 ) . '</td>'
				. '<td>' . number_format( $row["downloads"],0 ) . '</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["bugs_opened"],0) . " (" . number_format($row["bugs_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["support_opened"],0) . " (" . number_format($row["support_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["patches_opened"],0) . " (" . number_format($row["patches_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["artifacts_opened"],0) . " (" . number_format($row["artifacts_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["tasks_opened"],0) . " (" . number_format($row["tasks_closed"],0) . ')</td>'
				. '<td>&nbsp;&nbsp;' . number_format($row["cvs_checkouts"],0) . " (" . number_format($row["cvs_commits"],0) . ')</td>'
				. '</tr>' . "\n";
		}

		?>
		</table>
		<?php

	} else {
		echo _('No Data');
	}
}

function stats_site_aggregate( ) {
	$res = db_query_params ('SELECT * FROM stats_site_all_vw',
			array ());
	$site_totals = db_fetch_array($res);


	$res = db_query_params ('SELECT COUNT(*) AS count FROM groups WHERE status=$1',
			array ('A'));
	$groups = db_fetch_array($res);


	$res = db_query_params ('SELECT COUNT(*) AS count FROM users WHERE status=$1',
			array ('A'));
	$users = db_fetch_array($res);
	

	?>
	<h2><?php echo _('Current Aggregate Statistics for All Time'); ?></h2>

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr valign="top">
		<td><strong><?php echo _('Site Views'); ?></strong></td>
		<td><strong><?php echo _('Subdomain Views'); ?></strong></td>
		<td><strong><?php echo _('Downloads'); ?></strong></td>
		<td><strong><?php echo _('Developers'); ?></strong></td>
		<td><strong><?php echo _('Projects'); ?></strong></td>
	</tr>

	<tr>
		<td><?php echo number_format( $site_totals["site_page_views"],0 ); ?></td>
		<td><?php echo number_format( $site_totals["subdomain_views"],0 ); ?></td>
		<td><?php echo number_format( $site_totals["downloads"],0 ); ?></td>
		<td><?php echo number_format( $users["count"],0 ); ?></td>
		<td><?php echo number_format( $groups["count"],0 ); ?></td>
		</tr>

	</table>
	<?php
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
