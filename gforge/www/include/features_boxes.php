<?php

//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2001 (c) VA Linux Systems
// http://sourceforge.net
//
// $Id$
//

function show_features_boxes() {
	GLOBAL $HTML,$Language;
	$return .= $HTML->boxTop($Language->getText('home','sourceforge_statistics', $GLOBALS[sys_name]),0);
	$return .= show_sitestats();
	$return .= $HTML->boxMiddle($Language->getText('home','top_project_downloads'));
	$return .= show_top_downloads();
	$return .= $HTML->boxMiddle($Language->getText('home','highest_ranked_users'));
	$return .= show_highest_ranked_users();
	$return .= $HTML->boxMiddle($Language->getText('home','most_active_this_week'));
	$return .= show_highest_ranked_projects();
	$return .= $HTML->boxBottom(0);
	return $return;
}

function show_top_downloads() {

	// TODO yesterday is now defined as two days ago.  Quick fix
	//      to allow download list to be cached before nightly
	//      aggregation is done. jbyers 2001.03.19
	//
	$month = date("Ym",time()-(2*3600*24));
	$day = date("d",time()-(2*3600*24));

	$res_topdown = db_query("
		SELECT groups.group_id,
		groups.group_name,
		groups.unix_group_name,
		frs_dlstats_grouptotal_agg.downloads 
		FROM frs_dlstats_grouptotal_agg,groups 
		WHERE 
		frs_dlstats_grouptotal_agg.group_id=groups.group_id 
		ORDER BY downloads DESC
	", 10, 0, SYS_DB_STATS);
//	echo db_error();

	if (db_numrows($res_topdown) == 0) {
		return 'No Stats Available';
	}
	// print each one
	while ($row_topdown = db_fetch_array($res_topdown)) {
		if ($row_topdown['downloads'] > 0) 
			$return .= "(" . number_format($row_topdown[downloads], 0) . ") <a href=\"/projects/$row_topdown[unix_group_name]/\">"
			. "$row_topdown[group_name]</a><br />\n";
	}
	$return .= '<div align="center"><a href="/top/">[ More ]</a></div>';
	
	return $return;

}


function stats_getprojects_active_public() {
	$res_count = db_query("SELECT count(*) AS count FROM groups WHERE status='A' AND is_public=1");
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['count'];
	} else {
		return "error";
	}
}

function stats_getprojects_active() {
	$res_count = db_query("SELECT count(*) AS count FROM groups WHERE status='A'");
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['count'];
	} else {
		return "error";
	}
}

function stats_getprojects_total() {
	$res_count = db_query("SELECT count(*) AS count FROM groups WHERE status='A' OR status='H'");
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['count'];
	} else {
		return "error";
	}
}

function stats_getusers() {
	$res_count = db_query("SELECT count(*) AS count FROM users WHERE status='A'");
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['count'];
	} else {
		return "error";
	}
}

function stats_getpageviews_total() {
	$res_count = db_query("SELECT SUM(site_views) AS site, SUM(subdomain_views) AS subdomain FROM stats_site");
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return ($row_count['site'] + $row_count['subdomain']);
	} else {
		return "error";
	}
}

function stats_downloads_total() {
	$res_count = db_query("SELECT SUM(downloads) AS downloads FROM stats_site");
	if (db_numrows($res_count) > 0) {
		$row_count = db_fetch_array($res_count);
		return $row_count['downloads'];
	} else {
		return "error";
	}
}

function show_sitestats() {
	$return .= 'Hosted Projects: <strong>'.number_format(stats_getprojects_active()).'</strong>';
	$return .= '<br />Registered Users: <strong>'.number_format(stats_getusers()).'</strong>';
	return $return;
}

function show_newest_projects() {
	$sql =	"SELECT group_id,unix_group_name,group_name,register_time FROM groups " .
		"WHERE is_public=1 AND status='A' AND type=1 " .
		"ORDER BY register_time DESC";
	$res_newproj = db_query($sql,10);

	if (!$res_newproj || db_numrows($res_newproj) < 1) {
		return "No Stats".db_error();
	} else {
		while ( $row_newproj = db_fetch_array($res_newproj) ) {
			if ( $row_newproj['register_time'] ) {
				$return .= "(" . date("m/d",$row_newproj['register_time'])  . ") "
				. "<a href=\"/projects/$row_newproj[unix_group_name]/\">"
				. "$row_newproj[group_name]</a><br />";
			}
		}
	}
	return $return;
}

function show_highest_ranked_users() {

	//select out the users information to show the top users on the site
	$sql="SELECT users.user_name,users.realname,user_metric.metric
		FROM user_metric,users 
		WHERE users.user_id=user_metric.user_id AND user_metric.ranking < 11 
		ORDER BY ranking ASC";
	$res=db_query($sql);
	$rows=db_numrows($res);
	if (!$res || $rows<1) {
		return 'None Found. '.db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			$return .= ($i+1).' - ('. number_format(db_result($res,$i,'metric'),4) .') <a href="/users/'. db_result($res,$i,'user_name') .'">'. db_result($res,$i,'realname') .'</a><br />'; 
		}
	}
	$return .= '<div align="center"><a href="/top/topusers.php">[ More ]</a></div>';
	return $return;
}

function show_highest_ranked_projects() {
	$sql="SELECT groups.group_name,groups.unix_group_name,groups.group_id,".
		"project_weekly_metric.ranking,project_weekly_metric.percentile ".
		"FROM groups,project_weekly_metric ".
		"WHERE groups.group_id=project_weekly_metric.group_id ".
		"AND groups.is_public=1 ".
		"AND groups.type=1 ".
		"ORDER BY ranking ASC";
	$result=db_query($sql,20);
	if (!$result || db_numrows($result) < 1) {
		return "No Stats".db_error();
	} else {
		while ($row=db_fetch_array($result)) {
			$return .= '<strong>( '.$row['percentile'].'% )</strong>'
				.' <a href="/projects/'.$row['unix_group_name'].
			'/">'.$row['group_name'].'</a><br />';
		}
		$return .= '<div align="center"><a href="/top/mostactive.php?type=week">[ More ]</a></div>';
	}
	return $return;
}

?>
