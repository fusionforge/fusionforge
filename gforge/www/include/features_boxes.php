<?php

//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2001 (c) VA Linux Systems
// http://sourceforge.net
//
// Attempt to set up the include path, to fix problems with relative includes

require_once $gfcommon.'include/FusionForge.class.php';
require_once $gfcommon.'include/tag_cloud.php';
require_once $gfcommon.'include/Stats.class.php';

function show_features_boxes() {
	GLOBAL $HTML,$sys_use_ratings,$sys_use_frs,$sys_use_project_tags;
	
	plugin_hook ("features_boxes_top", array());
	$return = '<h2 class="skip">' . _('Features Boxes') . '</h2>';

	if ($sys_use_project_tags) {
		$return .= $HTML->boxTop(_('Tag Cloud'), 'Tag_Cloud');
		$return .= tag_cloud();
		$return .= $HTML->boxMiddle(sprintf(_('%1$s Statistics'), fusionforge_get_config ('forge_name')), 'Forge_Statistics');
	} else {
		$return .= $HTML->boxTop(sprintf(_('%1$s Statistics'), fusionforge_get_config ('forge_name')), 'Forge_Statistics');
	}
	$return .= show_sitestats();
	if ($sys_use_frs) {
		$return .= $HTML->boxMiddle(_('Top Project Downloads'), 'Top_Projects_Downloads');
		$return .= show_top_downloads();
	}
	if ($sys_use_ratings) {
		$return .= $HTML->boxMiddle(_('Highest Ranked Users'), 'Highest_Ranked_Users');
		$return .= show_highest_ranked_users();
	}
	$return .= $HTML->boxMiddle(_('Most Active This Week'), 'Most_Active_This_Week');
	$return .= show_highest_ranked_projects();
	$return .= $HTML->boxMiddle(_('Recently Registered Projects'), 'Recently_Registered_Projects');
	$return .= show_newest_projects();
	$return .= $HTML->boxBottom();
	plugin_hook ("features_boxes_bottom", array());
	return $return;
}

function show_top_downloads() {
	// TODO yesterday is now defined as two days ago.  Quick fix
	//      to allow download list to be cached before nightly
	//      aggregation is done. jbyers 2001.03.19
	//
	$month = date("Ym",time()-(2*3600*24));
	$day = date("d",time()-(2*3600*24));

	$return = '' ;

	$res_topdown = db_query_params ('
		SELECT groups.group_id,
		groups.group_name,
		groups.unix_group_name,
		frs_dlstats_grouptotal_vw.downloads
		FROM frs_dlstats_grouptotal_vw,groups
		WHERE
		frs_dlstats_grouptotal_vw.group_id=groups.group_id AND groups.is_public=1 and groups.status=$1
		ORDER BY downloads DESC
	',
					array ('A'),
					10);
//	echo db_error();

	if (db_numrows($res_topdown) == 0) {
		return _('No Stats Available');
	}
	// print each one
	while ($row_topdown = db_fetch_array($res_topdown)) {
		if ($row_topdown['downloads'] > 0) {
			$t_downloads = number_format($row_topdown['downloads']);
			$t_prj_link = util_make_link_g ($row_topdown['unix_group_name'], $row_topdown['group_id'], $row_topdown['group_name']);
		
			$return .= '<tr>';
			$return .= '<td class="width-stat-col1">' . $t_downloads . '</td>';
			$return .= '<td>' . $t_prj_link . '</td>';
			$return .= '</tr>';
		}
	}
	if ( $return != "" ) {
		/* MFaure: test required to deal with a special case encountered on zforge by 20091204 */
		$t_return = $return;
		$return = '<table summary="">' . $t_return . "</table>\n"; 
	}
	$return .= '<div class="align-center">' . util_make_link ('/top/', _('All the ranking'), array('class' => 'dot-link')) . '</div>';
	
	return $return;

}


function stats_getprojects_active_public() {
	$res_count = db_query_params ('SELECT count(*) AS count FROM groups WHERE status=$1 AND is_public=1',
			array ('A'));
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['count'];
	} else {
		return "error";
	}
}

function stats_getprojects_total() {
	$res_count = db_query_params ('SELECT count(*) AS count FROM groups WHERE status=$1 OR status=$2',
			array('A',
				'H'));
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['count'];
	} else {
		return "error";
	}
}

function stats_getpageviews_total() {
	$res_count = db_query_params ('SELECT SUM(site_views) AS site, SUM(subdomain_views) AS subdomain FROM stats_site',
			array());
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return ($row_count['site'] + $row_count['subdomain']);
	} else {
		return "error";
	}
}

function stats_downloads_total() {
	$res_count = db_query_params ('SELECT SUM(downloads) AS downloads FROM stats_site',
			array());
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['downloads'];
	} else {
		return "error";
	}
}

function show_sitestats() {
	global $sys_use_trove;
	$gforge = new FusionForge();
	$return = '<p>';
	$return .= _('Hosted Projects').': ';
	if ($sys_use_trove) {
		$return .= '<a href="softwaremap/full_list.php">';
	}
	$return .= '<strong>'.number_format($gforge->getNumberOfPublicHostedProjects()).'</strong>';
	if ($sys_use_trove) {
		$return .= '</a>';
	}
	$return .= "</p><p>";
	$return .= _('Registered Users').': <strong>'.number_format($gforge->getNumberOfActiveUsers()).'</strong>';
	$return .= "</p>\n";
	return $return;
}

function show_newest_projects() {
	$res_newproj = db_query_params ('SELECT group_id,unix_group_name,group_name,register_time FROM groups WHERE is_public=1 AND status=$1 AND type_id=1 AND register_time > 0 ORDER BY register_time DESC', array ('A'), 10);

	$return = '';

	if (!$res_newproj || db_numrows($res_newproj) < 1) {
		return _('No Stats Available')." ".db_error();
	} else {
		
		$return .= '<table summary="">' . "\n";
		while ( $row_newproj = db_fetch_array($res_newproj) ) {
			
			$t_prj_date = date(_('m/d'),$row_newproj['register_time']);
			$t_prj_link = util_make_link_g ($row_newproj['unix_group_name'],$row_newproj['group_id'],$row_newproj['group_name']);
			
			$return .= "<tr>";
			$return .= '<td class="width-stat-col1">' . $t_prj_date . "</td>";
			$return .= '<td>' . $t_prj_link . '</td>';
			$return .= "</tr>\n";
		}
		$return .= '</table>';
	}

	$return .= '<div class="align-center">'.util_make_link ('/softwaremap/full_list.php', _('All newest projects'), array('class' => 'dot-link')).'</div>';
	return $return;
}

function show_highest_ranked_users() {
	//select out the users information to show the top users on the site
	$res = db_query_params ('SELECT users.user_name,users.user_id,users.realname,user_metric.metric	FROM user_metric,users WHERE users.user_id=user_metric.user_id AND user_metric.ranking < 11 AND users.status != $1 ORDER BY ranking ASC',
				array ('D')) ;
	$rows=db_numrows($res);
	if (!$res || $rows<1) {
		return  _('No Stats Available').db_error();
	} else {
		$return = '';
		for ($i=0; $i<$rows; $i++) {
			$return .= ($i+1).' - ('. number_format(db_result($res,$i,'metric'),4) .') '
			. util_make_link_u (db_result($res,$i,'user_name'),db_result($res,$i,'user_id'),db_result($res,$i,'realname'))
			.'<br />';
		}
	}
	$return .= '<div class="align-center">'.util_make_link ('/top/topusers.php', _('All users'), array('class' => 'dot-link')).'</div>';
	return $return;
}

function show_highest_ranked_projects() {
	$statsobj = new Stats () ;
	$result = $statsobj->getMostActiveStats ('week', 0) ;
	if (!$result || db_numrows($result) < 1) {
		return _('No Stats Available')." ".db_error();
	} else {
		$return = '<table summary="">';
		$count = 0 ;
		while (($row=db_fetch_array($result)) && ($count < 20)) {
			$t_prj_activity = number_format(substr($row['ranking'],0,5),0);
			$t_prj_link = util_make_link_g ($row['unix_group_name'],$row['group_id'],$row['group_name']);
			
			$return .= "<tr>";
			$return .= '<td class="width-stat-col1">'. $t_prj_activity . "</td>";
			$return .= '<td>' . $t_prj_link . '</td>';
			$return .= "</tr>\n";
			
			$count++ ;
		}
		$return .= "</table>";
		$return .= '<div class="align-center">' . util_make_link ('/top/mostactive.php?type=week', _('All project activities'), array('class' => 'dot-link')) . '</div>';
		
	}
	return $return;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
