<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) Tim Perdue - GForge LLC
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once(forge_get_config('jpgraph_path').'/jpgraph.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_line.php');
require_once $gfcommon.'reporting/ReportUserAdded.class.php';

//
//      Create Report
//
$report=new Report();

//
//      Check for error
//
if ($report->isError()) {
	exit_error($report->getErrorMessage(),'scm');
}

$group_id = getIntFromRequest('group_id');
$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
}

$res = db_query_params ('SELECT month,sum(commits) AS count
	FROM stats_cvs_group
	WHERE group_id=$1
	GROUP BY month ORDER BY month ASC',
			array ($group_id));
echo db_error();

$report->labels=util_result_column_to_array($res,0);
$report->setData($res,1);
$report->start_date=$report->labels[0];
$report->end_date=$report->labels[count($report->labels)-1];

// Some data

// Create the graph. These two calls are always required
$graph  = new Graph(640, 480,"auto");
$graph->SetMargin(50,10,35,50);
$graph->SetScale( "textlin");

// Create the linear plot
$lineplot =new LinePlot($report->getData());
$lineplot ->SetColor("black");
$lineplot->SetFillColor("orange");

// Add the plot to the graph
$graph->Add( $lineplot);

//$graph->SetMargin(10,10,25,10);
$graph->title->Set($g->getPublicName()." Commits Over Time: ".$report->start_date." - ".$report->end_date);
$graph->subtitle->Set(forge_get_config ('forge_name'));
//$graph->xaxis-> title->Set("Date" );
//$graph->yaxis-> title->Set("Number" );

$a=$report->getDates();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetTextLabelInterval(3);

// Display the graph
$graph->Stroke();

?>
