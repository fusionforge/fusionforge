<?php
/**
  *
  * SourceForge Sitewide Statistics
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('../env.inc.php');
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

$sql = "SELECT month,day,new_users,new_projects FROM stats_site ORDER BY month ASC, day ASC";
$res = db_query( $sql );

$i = 0;
while ( $row = db_fetch_array($res) ) {
        $xdata[$i]          = $i;
	$xlabel[$i]         = (substr($row['month'],4) + 1 - 1) . "/" . $row['day'];
        $ydata1[$i]         = $row["new_users"];
        $ydata2[$i]         = $row["new_projects"];
        ++$i;
}
//
// Need at least 2 data points
//
if ($i == 0) {
    $xdata[0] = 0;
    $xlabel[0] = "";
    $ydata1[0] = 0;
    $ydata2[0] = 0;

    $xdata[1] = 1;
    $xlabel[1] = "";
    $ydata1[1] = 0;
    $ydata2[1] = 0;
}
if ($i == 1) {
    $xdata[1] = 1;
    $xlabel[1] = $xlabel[0];
    $ydata1[1] = $ydata1[0];
    $ydata2[1] = $ydata2[0];
}


$graph = new Graph( 750, 550 );

$data1 = $graph->AddData( $xdata, $ydata1, $xlabel );
$data2 = $graph->AddData( $xdata, $ydata2, $xlabel );

$graph->DrawGrid('gray');
$graph->LineGraph($data1,'red');
$graph->LineGraph($data2,'blue');
$graph->SetTitle($Language->getText('stats_user_graph','new_additions_by_day') );
$graph->SetSubTitle($Language->getText('stats_user_graph','new_user_projects'));
$graph->SetxTitle($Language->getText('stats_user_graph','date'));
$graph->SetyTitle($Language->getText('stats_user_graph','user_projects'));
$graph->DrawAxis();
//$graph->showDebug();
$graph->ShowGraph('png');

?>
