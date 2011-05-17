<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * http://fusionforge.org
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once(forge_get_config('jpgraph_path').'/jpgraph.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_pie.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_pie3d.php');
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';

session_require_global_perm ('forge_stats', 'read') ;

//
//	Create Report
//
$report=new Report();

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$area = getStringFromRequest('area');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

if (!isset($area)) {
	$area='category';
}

if (!$start) {
	$start=mktime(0,0,0,date('m'),1,date('Y'));;
}
if (!$end) {
	$end=time();
} else {
	$end--;
}


if ($area == 'category') {
	$res = db_query_params ('SELECT ac.category_name,count(*) 
	FROM artifact a, artifact_category ac
	WHERE a.group_artifact_id=$1
	AND a.category_id=ac.id
	AND a.open_date BETWEEN $2 AND $3
	GROUP BY category_name',
				array($atid,
				      $start,
				      $end));
} elseif ($area == 'group') {
	$res = db_query_params ('SELECT ag.group_name,count(*) 
	FROM artifact a, artifact_group ag
	WHERE a.group_artifact_id=$1
	AND a.artifact_group_id=ag.id
	AND a.open_date BETWEEN $2 AND $3
	GROUP BY group_name',
				array($atid,
				      $start,
				      $end));
} elseif ($area == 'resolution') {
	$res = db_query_params ('SELECT ar.resolution_name,count(*) 
	FROM artifact a, artifact_resolution ar
	WHERE a.group_artifact_id=$1
	AND a.resolution_id=ar.id
	AND a.open_date BETWEEN $2 AND $3
	GROUP BY resolution_name',
				array($atid,
				      $start,
				      $end));
} else {
	$area = 'assignee';
	$res = db_query_params ('SELECT u.realname,count(*) 
	FROM artifact a, users u
	WHERE a.group_artifact_id=$1
	AND a.assigned_to=u.user_id
	AND a.open_date BETWEEN $2 AND $3
	GROUP BY realname',
				array($atid,
				      $start,
				      $end));
}

if (db_error()) {
	exit_error(db_error());
}

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
//$graph->SetMargin(50,10,35,50);

$arr = array();
$arr['category']='By Category';
$arr['group']='By Group';
$arr['resolution']='By Resolution';
$arr['assignee']='By Assignee';
$graph->title->Set($arr[$area]." (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set(forge_get_config ('forge_name'));

// Create the tracker open plot
report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1));

$p1  = new PiePlot3D($pie_vals);
$p1->ExplodeSlice (0);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
