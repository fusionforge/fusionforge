<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once(forge_get_config('jpgraph_path').'/jpgraph.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_line.php');
require_once $gfcommon.'reporting/ReportTrackerAct.class.php';

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');
$SPAN = getIntFromRequest('SPAN', REPORT_TYPE_MONTHLY);
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

//
// Get Project Object
//
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
        exit_no_group();
}
if ($group->isError()) {
        if($group->isPermissionDeniedError()) {
                exit_permission_denied($group->getErrorMessage());
        } else {
                exit_error(_('Error'), $group->getErrorMessage());
        }
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

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,80);
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
$lineplot->SetLegend ("Avg Time Open (in days)");
$lineplot2 ->SetLegend("Total Opened");
$lineplot3 ->SetLegend("Total Still Open");

//echo "<pre>".print_r($report->getDates()).'<br />'.print_r($ydata).'<br />'.print_r($ydata2).'<br />'.print_r($ydata3);
//echo "<pre>".print_r($ydata2);
//exit;

//
//	Titles
//
$graph->title->Set("Tracker Activity For: ".$group->getPublicName(). 
	" (".date('Y-m-d',$report->getStartDate()) ." to ". date('Y-m-d',$report->getEndDate()) .")");
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
