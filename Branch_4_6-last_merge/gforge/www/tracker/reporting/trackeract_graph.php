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
require_once($sys_path_to_jpgraph.'/jpgraph_line.php');
require_once('common/reporting/ReportTrackerAct.class');

$group_id = getIntFromRequest('group_id');
$atid = getStringFromRequest('atid');
$SPAN = getStringFromRequest('SPAN');
$start = getStringFromRequest('start');
$end = getStringFromRequest('end');

if (!$SPAN) {
	$SPAN=REPORT_TYPE_MONTHLY;
}

//
//	Create Report
//
$report=new ReportTrackerAct($SPAN,$group_id,$atid,$start,$end); 

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

//
// Get Group Object
//
$g =& group_get_object($group_id);
if (!$g || $g->isError()) {
	exit_error("Could Not Get Group");
}

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,50);
$graph->SetScale( "textlin");
//$graph->SetScale( "linlog");
//$graph ->SetYScale("log");

// Create the average time plot
$ydata  =& $report->getAverageTimeData();
$lineplot =new LinePlot($ydata);
$lineplot ->SetColor("black");
$graph->Add( $lineplot);

// Create the open count plot
$ydata2  =& $report->getOpenCountData();
$lineplot2 =new LinePlot($ydata2);
$lineplot2 ->SetColor("blue");
$graph->Add( $lineplot2 );

// Create the still open count plot
$ydata3  =& $report->getStillOpenCountData();
$lineplot3 =new LinePlot($ydata3);
$lineplot3 ->SetColor("red");
$graph->Add( $lineplot3 );

//	Legends
$lineplot->SetLegend ("Avg Time Open");
$lineplot2 ->SetLegend("Total Opened");
$lineplot3 ->SetLegend("Total Still Open");

//echo "<pre>".print_r($report->getDates()).'<br>'.print_r($ydata).'<br>'.print_r($ydata2).'<br>'.print_r($ydata3);
//echo "<pre>".print_r($ydata2);
//exit;

//
//	Titles
//
$graph->title->Set("Tracker Activity For: ".$g->getPublicName(). 
	" (".date('m/d/Y',$report->getStartDate()) ."-". date('m/d/Y',$report->getEndDate()) .")");
$graph->subtitle->Set($report_company_name);
//$graph->xaxis-> title->Set("Date" );
//$graph->yaxis-> title->Set("Number" ); 

$a=$report->getDates();
$graph->xaxis->SetTickLabels($a); 
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetTextLabelInterval($report->getGraphInterval());

// Display the graph
$graph->Stroke();

?>
