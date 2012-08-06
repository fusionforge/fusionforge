<?php
/**
 * FusionForge Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC, Tim Perdue
 * Copyright 2010 (c) Franck Villaume
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
require_once(forge_get_config('jpgraph_path').'/jpgraph_pie.php');
require_once(forge_get_config('jpgraph_path').'/jpgraph_pie3d.php');
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/report_utils.php';

//
//	Create Report
//
$report=new Report();

//
//	Check for error, such as license key problem
//
if ($report->isError()) {
	exit_error($report->getErrorMessage(),'scm');
}

$group_id = getIntFromRequest('group_id');
$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
}

$days = getIntFromRequest('days');
if (!$days || $days < 1) {
	$days=30;
}

$start = getIntFromRequest('start');
if (!$start) {
	$start=time() - ($days * 60 * 60 * 24);
	$formattedmonth = date('Ym',$start);
}

$end = getIntFromRequest('end');
if (!$end) {
	$end=time();
} else {
	$end--;
}

$res = db_query_params ('SELECT u.realname,sum(commits) AS count
		FROM stats_cvs_user scu, users u
		WHERE u.user_id = scu.user_id
		AND scu.month >= $1
		AND group_id=$2
		GROUP BY realname ORDER BY count DESC',
			array ($formattedmonth,
			       $group_id));

if (db_error()) {
	exit_error(db_error(),'scm');
}

// Create the graph. These two calls are always required
$graph  = new PieGraph(640, 480,"auto");
//$graph->SetMargin(50,10,35,50);

$graph->title->Set(utf8_decode(_("Commits By User")." (".strftime('%x',$start) ." - ". strftime('%x',$end) .")"));
$graph->subtitle->Set(forge_get_config ('forge_name'));

// Create the tracker open plot
report_pie_arr(util_result_column_to_array($res,0), util_result_column_to_array($res,1), 0);

$p1  = new PiePlot3D($pie_vals);
$p1->SetLegends($pie_labels);
$graph->Add( $p1);

// Display the graph
$graph->Stroke();

?>
