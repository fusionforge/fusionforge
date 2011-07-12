<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once(forge_get_config('jpgraph_path').'/jpgraph.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_bar.php');
require_once $gfcommon.'reporting/Report.class.php';

session_require_global_perm ('forge_stats', 'read') ;

//
//	Create Report
//
$report=new Report();

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

if (!$start) {
	$start=mktime(0,0,0,date('m'),1,date('Y'));;
}
if (!$end) {
	$end=time();
} else {
	$end--;
}

$res=db_query_params ('SELECT week,sum(hours)
	FROM rep_time_tracking
	WHERE week
	BETWEEN $1 AND $2 GROUP BY week',
			array($start,
				$end));

$report->setDates($res,0);
$report->setData($res,1);

//////
// Create the graph. These two calls are always required
$graph = new Graph(640,480,"auto");
$graph->SetScale("textlin");
$graph->yaxis->scale->SetGrace(20);

// Add a drop shadow
//$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(40,20,35,40);

// Create a bar pot
$bplot = new BarPlot($report->getData());

// Adjust fill color
$bplot->SetFillColor('orange');
$bplot->SetShadow();
$bplot->value->Show();
//$bplot->value->SetAngle(90);
//$bplot->value->SetFormat('%0.1f');
$graph->Add($bplot);

// Setup the titles
$graph->title->Set("Hours Recorded (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set(forge_get_config ('forge_name'));
$graph->xaxis->title->Set("Date");
$graph->yaxis->title->Set("Hours");

// Setup X-axis
$graph->xaxis->SetTickLabels($report->getDates());

// Display the graph
$graph->Stroke();

?>
