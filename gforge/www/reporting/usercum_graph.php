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
require_once($sys_path_to_jpgraph.'jpgraph_line.php');
require_once('common/reporting/ReportUserCum.class');

session_require( array('group'=>$sys_stats_group) );

//
//	Create Report
//
$report=new ReportUserCum($SPAN,$start,$end); 

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

// Some data
$ydata  = $report->getData();

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,50);
$graph->SetScale( "textlin");

// Create the linear plot
$lineplot =new LinePlot($ydata);
$lineplot ->SetColor("black");
$lineplot->SetFillColor("orange");

// Add the plot to the graph
$graph->Add( $lineplot);

//$graph->SetMargin(10,10,25,10);
$graph->title->Set("Cumulative Users ".$report->getSpanName()
	." (".date('m/d/Y',$report->getStartDate()) ."-". date('m/d/Y',$report->getEndDate()) .")");
$graph->subtitle->Set($sys_name);
//$graph->xaxis-> title->Set("Date" );
//$graph->yaxis-> title->Set("Number" ); 

$a=$report->getDates();
$graph->xaxis->SetTickLabels($a); 
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetTextLabelInterval($report->getGraphInterval());

// Display the graph
$graph->Stroke();

?>
