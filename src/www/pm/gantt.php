<?php
/**
 * Project Management Facility
 *
 * Copyright 2010, FusionForge Team
 * http://fusionforge.org
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

if (!file_exists(forge_get_config('jpgraph_path').'/jpgraph.php')) {
	exit_error(_('Package JPGraph not installed'),'pm');
}

require_once(forge_get_config('jpgraph_path').'/jpgraph.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_gantt.php');
require_once $gfcommon.'pm/ProjectTaskFactory.class.php';
require_once $gfwww.'include/unicode.php';

$ptf = new ProjectTaskFactory($pg);
if (!$ptf || !is_object($ptf)) {
	exit_error(_('Could Not Get ProjectTaskFactory'),'pm');
} elseif ($ptf->isError()) {
	exit_error(_('Error getting PTF: ').$ptf->getErrorMessage(),'pm');
}

$offset = getIntFromRequest('offset');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getIntFromRequest('_status');
$_order = getStringFromRequest('_order');
$_resolution = getStringFromRequest('_resolution');
$_category_id = getIntFromRequest('_category_id');
$_size = getIntFromRequest('_size');
$max_rows = getIntFromRequest('max_rows',50);

$ptf->setup($offset,$_order,$max_rows,'custom',$_assigned_to,$_status,$_category_id);
if ($ptf->isError()) {
	exit_error(_('Error in PTF: ').$ptf->getErrorMessage(),'pm');
}

$pt_arr =& $ptf->getTasks();
if ($ptf->isError()) {
	exit_error($ptf->getErrorMessage(),'pm');
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

//$graph->SetShadow();
$graph->SetMargin(10,10,25,10);

// Add title and subtitle
$graph->title->Set($pg->getName());

if (isset($gantt_title_font_family)) {
	$graph->title->SetFont( constant($gantt_title_font_family), 
		constant($gantt_title_font_style), $gantt_title_font_size);
}

//$graph->subtitle-> Set("(Draft version)");

// Show day, week and month scale
if ($_resolution == 'Days') {
	$graph->ShowHeaders( GANTT_HDAY |  GANTT_HWEEK |  GANTT_HMONTH);
} elseif ($_resolution == 'Weeks') {
	$graph->ShowHeaders( GANTT_HWEEK |  GANTT_HMONTH);
} elseif ($_resolution == 'Months') {
	$graph->ShowHeaders( GANTT_HMONTH | GANTT_HYEAR);
} else {
	$graph->ShowHeaders( GANTT_HYEAR);
}

// Instead of week number show the date for the first day in the week
// on the week scale
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Make the week scale font smaller than the default
if (isset($gantt_title_font_family)) {
	$graph->scale->week->SetFont( constant($gantt_title_font_family), FS_NORMAL, 9);
	$graph->scale->month->SetFont( constant($gantt_title_font_family), FS_NORMAL, 9);
}

// Use the short name of the month together with a 2 digit year
// on the month scale
$graph->scale->month->SetStyle( MONTHSTYLE_SHORTNAME);

$rows=count($pt_arr);

for ($i=0; $i<$rows; $i++) {
	// Format the bar for the first activity
	// ($row,$title,$startdate,$enddate)
	$activity[$i] = new GanttBar ($i, convert_unicode($pt_arr[$i]->getSummary()), date('Y-m-d',$pt_arr[$i]->getStartDate()), date('Y-m-d',$pt_arr[$i]->getEndDate()-86400));
	
	// Yellow diagonal line pattern on a red background
	$activity[$i]->SetPattern(BAND_RDIAG, "yellow");
	$activity[$i]->SetFillColor ("red");
	$activity[$i]->progress->Set( (( $pt_arr[$i]->getPercentComplete() ) ? ($pt_arr[$i]->getPercentComplete()/100) : 0));
	$activity[$i]->progress->SetPattern(BAND_RDIAG, "blue");

	if (isset($gantt_task_font_family)) {
		$activity[$i]->title->SetFont( constant($gantt_task_font_family), 
			constant($gantt_task_font_style), $gantt_task_font_size);
	}

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
