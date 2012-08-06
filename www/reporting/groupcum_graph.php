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
require_once(forge_get_config('jpgraph_path').'/jpgraph_line.php');
require_once $gfcommon.'reporting/ReportGroupCum.class.php';

session_require_global_perm ('forge_stats', 'read') ;

$SPAN = getIntFromRequest('SPAN');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

//
//	Create Report
//
$report=new ReportGroupCum($SPAN,$start,$end);

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

// Some data
$ydata  = $report->getData();

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,80);
$graph->SetScale( "textlin");

// Create the linear plot
$lineplot =new LinePlot($ydata);
$lineplot ->SetColor("black");
$lineplot->SetFillColor("orange");

// Add the plot to the graph
$graph->Add( $lineplot);

//$graph->SetMargin(10,10,25,10);
$graph->title->Set("Cumulative Projects ".$report->getSpanName()
	." (".date('m/d/Y',$report->getStartDate()) ."-". date('m/d/Y',$report->getEndDate()) .")");
$graph->subtitle->Set(forge_get_config ('forge_name'));
//$graph->xaxis-> title->Set("Date" );
//$graph->yaxis-> title->Set("Number" );

$a=$report->getDates();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetTextLabelInterval($report->getGraphInterval());

// Display the graph
$graph->Stroke();

?>
