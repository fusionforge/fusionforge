<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once('pre.php');
require_once($sys_path_to_jpgraph.'/jpgraph.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie3d.php');
require_once('common/reporting/Report.class');
require_once('common/reporting/report_utils.php');

session_require( array('group'=>$sys_stats_group) );

//
//	Create Report
//
$report=new Report();

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

$datatype = getIntFromRequest('datatype');
$start = getStringFromRequest('start');
$end = getStringFromRequest('end');

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

	$sql="SELECT g.group_name,count(*) AS count
	FROM groups g, artifact_group_list agl, artifact a
	WHERE g.group_id=agl.group_id 
	AND agl.group_artifact_id=a.group_artifact_id 
	AND a.open_date BETWEEN '$start' AND '$end'
	AND agl.datatype='$datatype'
	GROUP BY group_name
	ORDER BY count DESC";

} elseif ($datatype == 5) {

	$sql="SELECT g.group_name,count(*) AS count
	FROM groups g, forum_group_list fgl, forum f
	WHERE g.group_id=fgl.group_id
	AND fgl.group_forum_id=f.group_forum_id
	AND f.post_date BETWEEN '$start' AND '$end'
	GROUP BY group_name
	ORDER BY count DESC";

} elseif ($datatype == 6) {

	$sql="SELECT g.group_name,count(*) AS count
	FROM groups g, project_group_list pgl, project_task pt
	WHERE g.group_id=pgl.group_id
	AND pgl.group_project_id=pt.group_project_id
	AND pt.start_date BETWEEN '$start' AND '$end'
	GROUP BY group_name
	ORDER BY count DESC";

} else {

	$sql="SELECT g.group_name,count(*) AS count
	FROM groups g, frs_package fp, frs_release fr, frs_file ff, frs_dlstats_file fdf
	WHERE g.group_id=fp.group_id
	AND fp.package_id=fr.package_id
	AND fr.release_id=ff.release_id
	AND ff.file_id=fdf.file_id
	AND (((fdf.month > '". date('Ym',$start) ."') OR (fdf.month = '". date('Ym',$start) ."' AND fdf.day >= '". date('d',$start) ."'))
	AND ((fdf.month < '". date('Ym',$end) ."') OR (fdf.month = '". date('Ym',$end) ."' AND fdf.day < '". date('d',$end) ."')))
	GROUP BY group_name
	ORDER BY count DESC";

}

//echo $sql;
//exit;

$res=db_query($sql);

if (db_error()) {
	exit_error('Error',db_error());
}

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
//$graph->SetMargin(50,10,35,50);

$arr[1]='Bugs';
$arr[2]='Support Requests';
$arr[3]='Patches';
$arr[4]='Feature Requests';
$arr[0]='Other Trackers';
$arr[5]='Forum Messages';
$arr[6]='Tasks';
$arr[7]='Downloads';
$graph->title->Set($arr[$datatype]." By Project (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set($sys_name);

// Create the tracker open plot
report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1));

$p1  = new PiePlot3D($pie_vals);
$p1->ExplodeSlice (0);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
