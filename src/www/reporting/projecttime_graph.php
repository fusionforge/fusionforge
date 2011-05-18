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
require_once $gfcommon.'reporting/ReportProjectTime.class.php';
require_once $gfcommon.'reporting/report_utils.php';

session_require_global_perm ('forge_stats', 'read') ;

$g_id = getIntFromRequest('g_id');
$type = getStringFromRequest('type');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

//
//	Create Report
//
$report=new ReportProjectTime($g_id,$type,$start,$end);

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

// XXX ogi: Isn't it $type?
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

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
$graph->SetMargin(50,10,35,80);

$arr['tasks']='By Task';
$arr['category']='By Category';
$arr['subproject']='By Subproject';
$arr['user']='By User';

$graph->title->Set("Time Report ".$arr[$type]." (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set(forge_get_config ('forge_name'));

report_pie_arr($report->labels,$report->getData());

$p1  = new PiePlot3D( $pie_vals );
$p1->ExplodeSlice (0);
$p1->SetLegends( $pie_labels );
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
