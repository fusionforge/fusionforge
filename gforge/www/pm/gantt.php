<?php

require_once($sys_path_to_jpgraph.'jpgraph.php');
require_once($sys_path_to_jpgraph.'jpgraph_gantt.php');
require_once('common/pm/ProjectTaskFactory.class');

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error('Error','Could Not Get ProjectTaskFactory');
} elseif ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$ptf->setup($offset,$_order,$max_rows,$set,$_assigned_to,$_status,$_category_id);
if ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

$pt_arr =& $ptf->getTasks();
if ($ptf->isError()) {
	exit_error('Error',$ptf->getErrorMessage());
}

if ($_size==640) {
	$graph  = new GanttGraph (640,480, "auto");
} elseif ($_size==1024) {
	$graph  = new GanttGraph (1024,768, "auto");
} elseif ($_size==1600) {
	$graph  = new GanttGraph (1600,1200, "auto");
} else {
	$graph  = new GanttGraph (800,600, "auto");
}
$graph->SetShadow();

// Add title and subtitle
$graph->title-> Set($pg->getName());
//$graph->title-> SetFont( FF_ARIAL, FS_BOLD, 12);
$graph->subtitle-> Set("(Draft version)");

// Show day, week and month scale
if ($_resolution == 'Days') {
	$graph->ShowHeaders( GANTT_HDAY |  GANTT_HWEEK |  GANTT_HMONTH);
} elseif ($_resolution == 'Weeks') {
	$graph->ShowHeaders( GANTT_HWEEK |  GANTT_HMONTH);
} else {
	$graph->ShowHeaders( GANTT_HMONTH);
}

// Instead of week number show the date for the first day in the week
// on the week scale
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Make the week scale font smaller than the default
$graph->scale->week->SetFont(FF_FONT0 );

// Use the short name of the month together with a 2 digit year
// on the month scale
$graph->scale->month->SetStyle( MONTHSTYLE_SHORTNAMEYEAR2);

$rows=count($pt_arr);

for ($i=0; $i<$rows; $i++) {
	// Format the bar for the first activity
	// ($row,$title,$startdate,$enddate)
	$activity[$i] = new GanttBar ($i, $pt_arr[$i]->getSummary(), date('Y-m-d',$pt_arr[$i]->getStartDate()), date('Y-m-d',$pt_arr[$i]->getEndDate()-86400));

	// Yellow diagonal line pattern on a red background
	$activity[$i]->SetPattern(BAND_RDIAG, "yellow");
	$activity[$i]->SetFillColor ("red");
	$activity[$i]->progress->Set( (( $pt_arr[$i]->getPercentComplete() ) ? ($pt_arr[$i]->getPercentComplete()/100) : 0));
	$activity[$i]->progress->SetPattern(BAND_RDIAG, "blue");
	// Finally add the bar to the graph
	$graph->Add( $activity[$i] );
}

//echo $rows;
$todayline = new GanttVLine(date('Y-m-d',time()),"Today");
$todayline ->SetDayOffset (0.5);
$graph->Add( $todayline);


// Display the Gantt chart
$graph->Stroke();

?>
