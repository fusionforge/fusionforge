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
require_once($sys_path_to_jpgraph.'jpgraph.php');
require_once($sys_path_to_jpgraph.'jpgraph_pie.php');
require_once($sys_path_to_jpgraph.'jpgraph_pie3d.php');
require_once('common/reporting/ReportUserTime.class');
require_once('common/reporting/report_utils.php');

session_require( array('group'=>$sys_stats_group) );

//
//	Create Report
//
$report=new ReportUserTime($dev_id,$type,$start,$end);

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

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
//$graph->SetMargin(50,10,35,50);

$arr['tasks']='By Task';
$arr['category']='By Category';
$arr['subproject']='By Subproject';

$graph->title->Set("Time Report ".$arr[$type]." (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set($report_company_name);

// Create the tracker open plot
//$data  =& $report->getData();
//$labels =& $report->labels;
report_pie_arr($report->labels,$report->getData());

$p1  = new PiePlot3D( $pie_vals );
$p1->ExplodeSlice (0);
$p1->SetLegends( $pie_labels );
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
