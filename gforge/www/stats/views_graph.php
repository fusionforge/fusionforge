<?php
/**
  *
  * SourceForge Sitewide Statistics - stats common module
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

   // require you to be a member of the sfstats group (group_id = 11084)
session_require( array('group'=>$sys_stats_group) );

$group_id = getIntFromRequest('group_id');
$year = getIntFromRequest('year');

if ( ! $group_id ) {
	$group_id = 0;
}

if ( ! $year ) {
	$year = gmstrftime("%Y", time() );
}

if ($monthly) {

	$sql = "SELECT month,site_page_views AS site_views,subdomain_views 
		FROM stats_site_months ORDER BY month ASC";
	$grouping='Months';

} else {

	$beg_year=date('Y',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));
	$beg_month=date('m',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));
	$beg_day=date('d',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));

	$sql = "SELECT month,day,site_page_views AS site_views,subdomain_views 
		FROM stats_site_vw 
		( month = '$beg_year$beg_month' AND day >= '$beg_day' ) OR ( month > '$beg_year$beg_month' )
		ORDER BY month ASC, day ASC";
	$grouping='Days';

}

$res = db_query($sql, -1, 0, SYS_DB_STATS);
//echo db_error();

$i = 0;
$xdata = array();
$ydata = array();
while ( $row = db_fetch_array($res) ) {
		$xdata[$i]		  = $i;
	$xlabel[$i]		 = $row['month'] . (($row['day']) ? "/" . $row['day'] : '');
		$ydata1[$i]		 = $row["site_views"] + $row["subdomain_views"];
		++$i;
}

$graph = new Graph( 750, 550 );
//
// Need at least 2 data points
//
if ($i == 0) {
	$xdata[0] = 0;
	$xlabel[0] = "";
	$ydata1[1] = 0;
	$xdata[1] = 1;
	$xlabel[1] = "";
	$ydata1[1] = 0;
}

if ($i == 1) {
	$xdata[1] = 1;
	$xlabel[1] = $xlabel[0];
	$ydata1[1] = $ydata1[0];
}
$graph->SetTitle( $Language->getText('stats_view_graph','page_views') );
$graph->SetSubTitle($Language->getText('stats_view_graph','total_views', array( $i)));

$data1 = $graph->AddData( $xdata, $ydata1, $xlabel );
$graph->LineGraph($data1,'red');

$graph->DrawGrid('gray');
$graph->SetxTitle($Language->getText('stats_view_graph','date'));
$graph->SetyTitle($Language->getText('stats_view_graph','views'));
$graph->DrawAxis();
//$graph->showDebug();
$graph->ShowGraph('png');

?>
