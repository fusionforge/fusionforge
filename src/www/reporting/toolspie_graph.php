<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
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

$datatype = getIntFromRequest('datatype');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

if (!isset($datatype)) {
	$datatype=1;
}

if (!$start) {
	$start=mktime(0,0,0,date('m'),1,date('Y'));;
}
if (!$end) {
	$end=time();
} else {
	$end--;
}


if ($datatype < 5) {
	$res = db_query_params ('SELECT g.group_name,count(*) AS count
	FROM groups g, artifact_group_list agl, artifact a
	WHERE g.group_id=agl.group_id
	AND agl.group_artifact_id=a.group_artifact_id
	AND a.open_date BETWEEN $1 AND $2
	AND agl.datatype=$3
	GROUP BY group_name
	ORDER BY count DESC',
				array ($start,
				       $end,
				       $datatype));
} elseif ($datatype == 5) {
	$res = db_query_params ('SELECT g.group_name,count(*) AS count
	FROM groups g, forum_group_list fgl, forum f
	WHERE g.group_id=fgl.group_id
	AND fgl.group_forum_id=f.group_forum_id
	AND f.post_date BETWEEN $1 AND $2
	GROUP BY group_name
	ORDER BY count DESC',
				array ($start,
				       $end));
} elseif ($datatype == 6) {
	$res = db_query_params ('SELECT g.group_name,count(*) AS count
	FROM groups g, project_group_list pgl, project_task pt
	WHERE g.group_id=pgl.group_id
	AND pgl.group_project_id=pt.group_project_id
	AND pt.start_date BETWEEN $1 AND $2
	GROUP BY group_name
	ORDER BY count DESC',
				array ($start,
				       $end));
} else {
	$res = db_query_params ('SELECT g.group_name,count(*) AS count
	FROM groups g, frs_package fp, frs_release fr, frs_file ff, frs_dlstats_file fdf
	WHERE g.group_id=fp.group_id
	AND fp.package_id=fr.package_id
	AND fr.release_id=ff.release_id
	AND ff.file_id=fdf.file_id
	AND (((fdf.month > $1) OR (fdf.month = $1 AND fdf.day >= $2))
	AND ((fdf.month < $3) OR (fdf.month = $3 AND fdf.day < $4)))
	GROUP BY group_name
	ORDER BY count DESC',
				array (date('Ym',$start),
				       date('d',$start),
				       date('Ym',$end),
				       date('d',$end)));
}

//echo $sql;
//exit;


if (db_error()) {
	exit_error(db_error());
}

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
$graph->SetMargin(50,10,35,80);

$arr[1]='Bugs';
$arr[2]='Support Requests';
$arr[3]='Patches';
$arr[4]='Feature Requests';
$arr[0]='Other Trackers';
$arr[5]='Forum Messages';
$arr[6]='Tasks';
$arr[7]='Downloads';
$graph->title->Set($arr[$datatype]." By Project (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set(forge_get_config ('forge_name'));

// Create the tracker open plot
report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1));

$p1  = new PiePlot3D($pie_vals);
$p1->ExplodeSlice (0);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
