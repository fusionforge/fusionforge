<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$


function show_features_boxes() {
	GLOBAL $HTML,$Language;
	$return .= $HTML->box1_top($Language->SOURCEFORGE_STATISTICS,0);
	$return .= show_sitestats();
	// $return .= $HTML->box1_middle($Language->SFOS);
	// $return .= show_sfos();
	$return .= $HTML->box1_middle($Language->TOP_PROJECT_DOWNLOADS);
	$return .= show_top_downloads();
	$return .= $HTML->box1_middle($Language->HIGHEST_RANKED_USERS);
	$return .= show_highest_ranked_users();
	$return .= $HTML->box1_middle($Language->MOST_ACTIVE_THIS_WEEK);
	$return .= show_highest_ranked_projects();
	$return .= $HTML->box1_bottom(0);
	return $return;
}

/**
 *	depends on $foundry being set globally with the current foundry object
 */
function foundry_features_boxes() {
	GLOBAL $HTML;
//	$comma_sep_groups=$GLOBALS['foundry']->getProjectsCommaSep();

	$group_id=$GLOBALS['foundry']->getGroupID();

	$return .= $HTML->box1_top('Most Active',0);
	$return .= foundry_active_projects($group_id);
	$return .= $HTML->box1_middle('Top Downloads');
	$return .= foundry_top_downloads($GLOBALS['foundry']->getGroupID());
	$return .= $HTML->box1_middle('Featured Projects');
	$return .= foundry_featured_projects($GLOBALS['foundry']->getGroupID());
	$return .= $HTML->box1_bottom(0);
	return $return;
}

function foundry_active_projects($foundry_id) {

//create index projectweeklymetric_groupid_ranking on project_weekly_metric(group_id,ranking);

	$sql="SELECT groups.group_name,groups.unix_group_name,groups.group_id,
		project_weekly_metric.ranking,project_weekly_metric.percentile 
		FROM groups,project_weekly_metric 
		WHERE groups.group_id=project_weekly_metric.group_id 
		AND groups.is_public=1 
		AND groups.type=1 
		AND EXISTS (SELECT project_id FROM foundry_projects 
			WHERE project_weekly_metric.group_id=foundry_projects.project_id 
			AND foundry_projects.foundry_id='$foundry_id')
		ORDER BY project_weekly_metric.ranking ASC";

	$result=db_query($sql,20);
	if (!$result || db_numrows($result) < 1) {
		return '';//db_error();
	} else {
		while ($row=db_fetch_array($result)) {
			$return .= '<B>( '.$row['percentile'].'% )</B>'
				.' <A HREF="/projects/'.$row['unix_group_name'].
			'/">'.$row['group_name'].'</A><BR>';
		}
		$return .= '<BR><CENTER><A href="/top/mostactive.php?type=week">[ More ]</A></CENTER>';
	}
	return $return;
}

function foundry_featured_projects($group_id) {
	$sql="SELECT groups.group_name,groups.unix_group_name,".
		"groups.group_id,foundry_preferred_projects.rank ".
		"FROM groups,foundry_preferred_projects ".
		"WHERE foundry_preferred_projects.group_id=groups.group_id ".
		"AND foundry_preferred_projects.foundry_id='$group_id' ".
		"ORDER BY rank ASC";

	$res_grp=db_query($sql);
	$rows=db_numrows($res_grp);

	if (!$res_grp || $rows < 1) {
		$return .= 'No Projects';
		$return .= db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			$return .= '<A href="/projects/'. 
			strtolower(db_result($res_grp,$i,'unix_group_name')) .'/">'. 
			db_result($res_grp,$i,'group_name') .'</A><BR>';
		}
	}
	return $return;
}

function foundry_top_downloads($foundry_id) {

	#get yesterdays day
	$yesterday = date("Ymd",time()-(3600*24));
	
	$res_topdown = db_query("SELECT groups.group_id,
		groups.group_name,
		groups.unix_group_name,
		frs_dlstats_group_agg.downloads 
		FROM frs_dlstats_group_agg,groups WHERE day='20001115' 
		AND frs_dlstats_group_agg.group_id=groups.group_id 
		AND EXISTS (SELECT project_id FROM foundry_projects 
			WHERE frs_dlstats_group_agg.group_id=foundry_projects.project_id 
			AND foundry_projects.foundry_id='$foundry_id') 
		AND groups.type=1 
		ORDER BY downloads DESC",10);

	if (!$res_topdown || db_numrows($res_topdown) < 1) {
		return db_error();
	} else {
		// print each one
		while ($row_topdown = db_fetch_array($res_topdown)) {
			if ($row_topdown['downloads'] > 0) 
				$return .= "<BR><A href=\"/projects/$row_topdown[unix_group_name]/\">"
				. "$row_topdown[group_name]</A> ($row_topdown[downloads])\n";
		}
	}
	
	return $return; 
}

function show_top_downloads() {
	$yesterday = date("Ymd",time()-(3600*24));

	$res_topdown = db_query("SELECT groups.group_id,"
		."groups.group_name,"
		."groups.unix_group_name,"
		."frs_dlstats_group_agg.downloads "
		."FROM frs_dlstats_group_agg,groups WHERE day=$yesterday "
		."AND frs_dlstats_group_agg.group_id=groups.group_id "
		."ORDER BY downloads DESC",10);
	// print each one
	while ($row_topdown = db_fetch_array($res_topdown)) {
		if ($row_topdown['downloads'] > 0) 
			$return .= "<BR>($row_topdown[downloads]) <A href=\"/projects/$row_topdown[unix_group_name]/\">"
			. "$row_topdown[group_name]</A>\n";
	}
	$return .= '<P align="center"><A href="/top/">[ More ]</A>';
	
	return $return;

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
	$return .= 'Hosted Projects: <B>'.number_format(stats_getprojects_active()).'</B>';
	$return .= '<BR>Registered Users: <B>'.number_format(stats_getusers()).'</B>';
//	$return .= '<BR>Files Downloaded: <B>'.number_format(stats_downloads_total()).'</B>';
//	$return .= '<BR>Pages Viewed: <B>'.number_format(stats_getpageviews_total()).'</B><BR>&nbsp;';
	return $return;
}

function show_newest_projects() {
	$sql =	"SELECT group_id,unix_group_name,group_name,register_time FROM groups " .
		"WHERE is_public=1 AND status='A' AND type=1 " .
		"AND register_time < " . strval(time()-(24*3600)) . " " . 
		"ORDER BY register_time DESC";
	$res_newproj = db_query($sql,10);

	if (!$res_newproj || db_numrows($res_newproj) < 1) {
		return db_error();
	} else {
		while ( $row_newproj = db_fetch_array($res_newproj) ) {
			if ( $row_newproj['register_time'] ) {
				$return .= "(" . date("m/d",$row_newproj['register_time'])  . ") "
				. "<A href=\"/projects/$row_newproj[unix_group_name]/\">"
				. "$row_newproj[group_name]</A><BR>";
			}
		}
	}
	return $return;
}

function show_highest_ranked_users() {

	//select out the users information to show the top users on the site
	$sql="SELECT users.user_name,users.realname,user_metric.metric ".
		"FROM user_metric,users ".
		"WHERE users.user_id=user_metric.user_id AND user_metric.ranking < 11";
	$res=db_query($sql);
	$rows=db_numrows($res);
	if (!$res || $rows<1) {
		return 'None Found. '.db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			$return .= ($i+1).' - ('. number_format(db_result($res,$i,'metric'),4) .') <A HREF="/users/'. db_result($res,$i,'user_name') .'">'. db_result($res,$i,'realname') .'</A><BR>'; 
		}
	}
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
		return db_error();
	} else {
		while ($row=db_fetch_array($result)) {
			$return .= '<B>( '.$row['percentile'].'% )</B>'
				.' <A HREF="/projects/'.$row['unix_group_name'].
			'/">'.$row['group_name'].'</A><BR>';
		}
		$return .= '<BR><CENTER><A href="/top/mostactive.php?type=week">[ More ]</A></CENTER>';
	}
	return $return;
}

function show_sfos() {
		$return = "Now SourceForge can support your company. Click to learn about <a href='http://www.valinux.com/services/sfos.html'>SourceForge OnSite</a><br>";

		return $return;
}

?>
