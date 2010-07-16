<?php
/**
  *
  * SourceForge Sitewide Statistics - stats common module
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/graph_lib.php';

session_require_global_perm ('forge_stats', 'read') ;

$group_id = getIntFromRequest('group_id');
$year = getIntFromRequest('year');
$monthly = getIntFromRequest('monthly');

if ( ! $group_id ) {
	$group_id = 0;
}

if ( ! $year ) {
	$year = gmstrftime("%Y", time() );
}

if ($monthly) {


$res = db_query_params ('SELECT month,site_page_views AS site_views,subdomain_views 
		FROM stats_site_months ORDER BY month ASC',
			array ());
	$grouping='Months';

} else {

	$beg_year=date('Y',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));
	$beg_month=date('m',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));
	$beg_day=date('d',mktime(0,0,0,(date('m')-1),date('d'),date('Y')));


$res = db_query_params ('SELECT month,day,site_page_views AS site_views,subdomain_views 
		FROM stats_site_vw 
		( month = $1 AND day >= $2 ) OR ( month > $3 )
		ORDER BY month ASC, day ASC',
			array ("$beg_year$beg_month",
				$beg_day,
				"$beg_year$beg_month"));
	$grouping='Days';

}


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
$graph->SetTitle( _('Forge Page Views') );
$graph->SetSubTitle(sprintf(_('Total Page Views (RED) (%1$s days)'),  $i));

$data1 = $graph->AddData( $xdata, $ydata1, $xlabel );
$graph->LineGraph($data1,'red');

$graph->DrawGrid('gray');
$graph->SetxTitle(_('Date'));
$graph->SetyTitle(_('Views (RED)'));
$graph->DrawAxis();
//$graph->showDebug();
$graph->ShowGraph('png');

?>
