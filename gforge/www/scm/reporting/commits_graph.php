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
require_once($sys_path_to_jpgraph.'/jpgraph_pie.php');
require_once($sys_path_to_jpgraph.'/jpgraph_pie3d.php');
require_once('common/reporting/Report.class');
require_once('common/reporting/report_utils.php');

//
//	Create Report
//
$report=new Report();

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	echo $report->getErrorMessage();
	exit;
}

$group_id = getIntFromRequest('group_id');
$days = getIntFromRequest('days');
if (!$days || $days < 1) {
	$days=30;
}

if (!isset($datatype)) {
	$datatype=1;
}

if (!$start) {
	$start=time() - ($days * 60 * 60 * 24);
	$formattedmonth = date('Ym',$start);
}

if (!$end) {
	$end=time();
} else {
	$end--;
}



	$sql="SELECT u.realname,sum(commits) AS count 
		FROM stats_cvs_user scu, users u
		WHERE u.user_id=scu.user_id 
		AND scu.month >= '$formattedmonth'
		AND group_id='$group_id'
		GROUP BY realname";

//echo $sql;
//exit;

$res=db_query($sql);

if (db_error()) {
	exit_error('Error',db_error());
}

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
//$graph->SetMargin(50,10,35,50);

$graph->title->Set("Commits By User (".date('m/d/Y',$start) ."-". date('m/d/Y',$end) .")");
$graph->subtitle->Set($sys_name);

// Create the tracker open plot
report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1));

$p1  = new PiePlot3D($pie_vals);
$p1->ExplodeSlice (0);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
