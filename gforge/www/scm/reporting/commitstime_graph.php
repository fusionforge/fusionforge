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
require_once('common/reporting/ReportUserAdded.class');

//
//      Create Report
//
$report=new Report();

//
//      Check for error
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

$group_id = getIntFromRequest('group_id');
$g =& group_get_object($group_id);
if (!$g || !is_object($g)) {
	echo 'Permission Denied';
	exit;
}

$sql="SELECT month,sum(commits) AS count
	FROM stats_cvs_group
	WHERE group_id='$group_id'
	GROUP BY month ORDER BY month ASC";
$res=db_query($sql);
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
$graph->subtitle->Set($sys_name);
//$graph->xaxis-> title->Set("Date" );
//$graph->yaxis-> title->Set("Number" ); 

$a=$report->getDates();
$graph->xaxis->SetTickLabels($a); 
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetTextLabelInterval(3);

// Display the graph
$graph->Stroke();

?>
