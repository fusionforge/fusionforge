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
require_once $gfcommon.'reporting/ReportProjectAct.class.php';
require_once $gfwww.'include/unicode.php';

$area = getStringFromRequest('area');
$SPAN = getIntFromRequest('SPAN', 1);
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');
$g_id = getIntFromRequest('g_id');

$area = util_ensure_value_in_set ($area, array ('tracker','forum','docman','taskman','downloads')) ;

//
//	Create Report
//
$report=new ReportProjectAct($SPAN,$g_id,$start,$end); 

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

//
// Get Project Object
//
$g = group_get_object($g_id);
if (!$g || !is_object($g)) {
	exit_no_group();
} elseif ($g->isError()) {
	exit_error($g->getErrorMessage(),'');
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
	$lineplot4 ->SetLegend(convert_unicode(_('Docs')));

} elseif ($area=='downloads') {

	// Create the Docman plot
	$ydata4  =& $report->getDownloads();
	$lineplot4 =new LinePlot($ydata4);
	$lineplot4 ->SetColor("red");
	$graph->Add( $lineplot4 );

	//	Legends
	$lineplot4 ->SetLegend(convert_unicode(_('Downloads')));

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
	$lineplot5 ->SetLegend(convert_unicode(_('Task Open')));
	$lineplot6 ->SetLegend(convert_unicode(_('Task Close')));

} elseif ($area=='pageviews') {

	// Create the PageViews plot
	$ydata4  =& $report->getPageViews();
	$lineplot4 =new LinePlot($ydata4);
	$lineplot4 ->SetColor("blue");
	$graph->Add( $lineplot4 );

	//	Legends
	$lineplot4 ->SetLegend(convert_unicode(_('Page Views')));

} else {
	/*	
 	* The goal of this code is to get values from the activity hook to compute stats without the
 	* need of another specific hook or another dedicated tables.
 	* 
 	* So, values are requested to the hook and stored in $results array.
 	* After, the sum is made according to the chosen interval
 	* And then, the sum is stored in the ydata array.
	*/	
	
	$results = array();
	$ids = array();
	$texts = array();
	
	$hookParams['group'] = $g_id ;
	$hookParams['results'] = &$results;
	$hookParams['show'] = array();
	$hookParams['begin'] = $start;
	$hookParams['end'] = $end;
	$hookParams['ids'] = &$ids;
	$hookParams['texts'] = &$texts;
	plugin_hook ("activity", $hookParams) ;
	
	if ($SPAN == REPORT_TYPE_DAILY) {
		$interval = REPORT_DAY_SPAN;
	} elseif ($SPAN == REPORT_TYPE_WEEKLY) {
		$interval = REPORT_WEEK_SPAN;
	} elseif ($SPAN == REPORT_TYPE_MONTHLY) {
		$interval = REPORT_MONTH_SPAN;
	}
	
	print "start: $start ".date('r',$start)."<br>";
	print "  end: $end ".date('r', $end)."<br>";
	
	$sum = array();
	$starting_date = $start;
	foreach ($results as $arr) {
		$d = $arr['activity_date'];
		$col = intval(($d - $starting_date)/$interval);
		$col_date = $starting_date+$col*$interval;
		$sum[$col_date]++;
	}
	
	// Now, stores the values in the ydata array for the graph.
	$ydata = array();
	$i = 0;
	foreach ($report->getDates() as $d) {
		$ydata[$i++] = isset($sum[strtotime($d)]) ? $sum[strtotime($d)] : 0; 
	}
	
	$lineplot =new LinePlot($ydata);
	$lineplot->SetColor("violet");
	$graph->Add( $lineplot );

	//	Legends
	$lineplot->SetLegend($area);
	
//	var_dump($report->getDates());
//	var_dump($ydata);
// 	exit;
}


//
//	Titles
//
$graph->title->Set("Project Activity For: ".util_unconvert_htmlspecialchars($g->getPublicName()). 
	" (".date('Y-m-d',$report->getStartDate()) ." to ". date('Y-m-d',$report->getEndDate()) .")");
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
