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

require_once('pre.php');
require_once($sys_path_to_jpgraph.'/jpgraph.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie3d.php');
require_once('common/reporting/Report.class');
require_once('common/reporting/report_utils.php');
require_once('www/tracker/include/ArtifactTypeHtml.class');


$group_id = getIntFromRequest('group_id');
$atid = getStringFromRequest('atid');
$area = getStringFromRequest('area');
$SPAN = getStringFromRequest('SPAN');
$start = getStringFromRequest('start');
$end = getStringFromRequest('end');
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

	$sql="SELECT ac.category_name,count(*) 
	FROM artifact a, artifact_category ac
	WHERE a.group_artifact_id='$atid'
	AND a.category_id=ac.id
	AND a.open_date BETWEEN '$start' AND '$end'
	GROUP BY category_name";

} elseif ($area == 'group') {

	$sql="SELECT ag.group_name,count(*) 
	FROM artifact a, artifact_group ag
	WHERE a.group_artifact_id='$atid'
	AND a.artifact_group_id=ag.id
	AND a.open_date BETWEEN '$start' AND '$end'
	GROUP BY group_name";

} elseif ($area == 'resolution') {

	$sql="SELECT ar.resolution_name,count(*) 
	FROM artifact a, artifact_resolution ar
	WHERE a.group_artifact_id='$atid'
	AND a.resolution_id=ar.id
	AND a.open_date BETWEEN '$start' AND '$end'
	GROUP BY resolution_name";

} else {
	
	$area = 'assignee';

	$sql="SELECT u.realname,count(*) 
	FROM artifact a, users u
	WHERE a.group_artifact_id='$atid'
	AND a.assigned_to=u.user_id
	AND a.open_date BETWEEN '$start' AND '$end'
	GROUP BY realname";

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

$arr=array();
$arr['category']='By Category';
$arr['group']='By Group';
$arr['resolution']='By Resolution';
$arr['assignee']='By Assignee';
$graph->title->Set($arr[$area]." (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set($report_company_name);

// Create the tracker open plot
report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1));

$p1  = new PiePlot3D($pie_vals);
$p1->ExplodeSlice (0);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
