<?php
/**
  *
  * Project Statistics Graph
  *
  * This script produces PNG image which shows graph of downloads/pageviews
  * across the time.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('graph_lib.php');
require_once('project_stats_utils.php');

if (!$group_id) {
	exit_no_group();
}

if (!$report) {
	$report = 'last_7';
}   

$unit = 'days';
//
//	Data will be fetched in DESC order, then flipped into ASC order
//
if ( $report == 'last_7' ) {

	$res=db_query("
		SELECT month,day,downloads, subdomain_views as views,site_views as views2
		FROM stats_project_vw
		WHERE group_id='$group_id' ORDER BY month ASC, day ASC
	", 7, 23, SYS_DB_STATS);
    
} elseif ( $report == 'last_30' ) {

	$res=db_query("
		SELECT month,day,downloads, subdomain_views as views,site_views as views2 
		FROM stats_project_vw
		WHERE group_id='$group_id' ORDER BY month ASC, day ASC
	", -1, 0, SYS_DB_STATS);
    
} elseif ( $report == 'months' ) {

	$res = db_query("
		SELECT month,'15',downloads,subdomain_views as views,site_views as views2 
		FROM stats_project_months 
		WHERE group_id='$group_id'
		ORDER BY group_id ASC, month ASC
	", -1, 0, SYS_DB_STATS);
	$unit = 'months';
    
} else {

	$res=db_query("
		SELECT month,day,downloads, subdomain_views as views,site_views as views2
		FROM stats_project_vw
		WHERE group_id='$group_id' ORDER BY month ASC, day ASC
	", 7, 23, SYS_DB_STATS);
    
}

$j = 0;
$rows=db_numrows($res);
$db_error=db_error();

while (	$row = db_fetch_array($res) ) {
	$xdata[$j]	  = $j;
	$xlabel[$j]	 = substr($row['month'],4) . "-" . $row['day'];
	$ydata1[$j]	 = $row["views"] + $row["views2"];
	$ydata2[$j]	 = $row["downloads"];
	$j++;
}


//
//	Add dummy data to keep graph from breaking
//
if ($rows < 1) {
	$xdata[$j]=0;
	$xlabel[$j]	 = "0-0";
	$ydata1[$j]	 = 0;
	$ydata2[$j]	 = 0;

	$j++;
	$xdata[$j]=1;
	$xlabel[$j]	 = "0-1";
	$ydata1[$j]	 = 0;
	$ydata2[$j]	 = 0;
}

$graph = new Graph(600, 350);

$graph->addDebug( "We appended $j/$rows rows of data to the graphing set." );
$graph->addDebug( $db_error );
//$graph->addDebug( "$begin_time" );
//$graph->addDebug( "$sql" );

@$data1 = $graph->AddData($xdata,$ydata1,$xlabel);
@$data2 = $graph->AddData($xdata,$ydata2,$xlabel);

$graph->DrawGrid('gray');
$graph->LineGraph( $data1, 'red' );
$graph->LineGraph( $data2, 'blue' );
$graph->SetTitle( "Sourceforge Statistics: " . group_getname($group_id) );
$graph->SetSubTitle("Page Views (red) and Downloads (blue) for the past $j $unit");
$graph->SetxTitle('Date');
$graph->SetyTitle('Views (red) / Downloads (blue)');
$graph->DrawAxis();
//$graph->showDebug();
$graph->ShowGraph('png');

?>
