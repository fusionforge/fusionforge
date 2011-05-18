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
require_once $gfcommon.'reporting/ReportUserAct.class.php';
require_once $gfwww.'include/unicode.php';

session_require_global_perm ('forge_stats', 'read') ;

$dev_id = getIntFromRequest('dev_id');
$SPAN = getIntFromRequest('SPAN');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');
$area = getFilteredStringFromRequest('area', '/^[a-z]+$/');

//
//	Create Report
//
$report=new ReportUserAct($SPAN,$dev_id,$start,$end); 

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

//
// Get User Object
//
$u =& user_get_object($dev_id);
if (!$u || $u->isError()) {
	exit_error(_("Could Not Get User"));
}

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,80);
$graph->SetScale( "textlin");
//$graph->SetScale( "linlog");
//$graph ->SetYScale("log");

if ($area=='tracker') {

	// Create the tracker open plot
	$ydata  =& $report->getTrackerOpened();
	$lineplot =new LinePlot($ydata);
	$lineplot ->SetColor("black");
	$graph->Add( $lineplot);

	// Create the tracker close plot
	$ydata2  =& $report->getTrackerClosed();
	$lineplot2 =new LinePlot($ydata2);
	$lineplot2 ->SetColor("blue");
	$graph->Add( $lineplot2 );

	//	Legends
	$lineplot->SetLegend (convert_unicode(_('Tracker Items Opened')));
	$lineplot2 ->SetLegend(convert_unicode(_('Tracker Items Closed')));

} elseif ($area=='forum') {

	// Create the forum plot
	$ydata3  =& $report->getForum();
	$lineplot3 =new LinePlot($ydata3);
	$lineplot3 ->SetColor("orange");
	$graph->Add( $lineplot3 );

	//	Legends
	$lineplot3 ->SetLegend("Forum");

} elseif ($area=='docman') {

	// Create the Docman plot
	$ydata4  =& $report->getDocs();
	$lineplot4 =new LinePlot($ydata4);
	$lineplot4 ->SetColor("red");
	$graph->Add( $lineplot4 );

	//	Legends
	$lineplot4 ->SetLegend("Docs");

} elseif ($area=='taskman') {

	// Create the Tasks Opened plot
	$ydata5  =& $report->getTaskOpened();
	$lineplot5 =new LinePlot($ydata5);
	$lineplot5 ->SetColor("purple");
	$graph->Add( $lineplot5 );

	// Create the Tasks Closed plot
	$ydata6  =& $report->getTaskClosed();
	$lineplot6 =new LinePlot($ydata6);
	$lineplot6 ->SetColor("yellow");
	$graph->Add( $lineplot6 );

	//	Legends
	$lineplot5 ->SetLegend("Task Open");
	$lineplot6 ->SetLegend("Task Close");
}


//
//	Titles
//
$graph->title->Set("User Activity For: ".$u->getRealName() 
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
