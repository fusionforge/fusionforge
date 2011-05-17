<?php
/**
 * Sitewide Statistics
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
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
require_once $gfwww.'include/graph_lib.php';

session_require_global_perm ('forge_stats', 'read') ;

$group_id = getIntFromRequest('group_id');
$year = getIntFromRequest('year');

if ( ! $group_id ) {
	$group_id = 0;
}

if ( ! $year ) {
	$year = gmstrftime("%Y", time() );
}

$res = db_query_params ('SELECT month,day,new_users,new_projects FROM stats_site ORDER BY month ASC, day ASC',
			array ());

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
$graph->SetTitle(_('New Additions, by Day') );
$graph->SetSubTitle(_('New Users (RED), New Projects (BLUE)'));
$graph->SetxTitle(_('Date'));
$graph->SetyTitle(_('Users (RED) / Projects (BLUE)'));
$graph->DrawAxis();
//$graph->showDebug();
$graph->ShowGraph('png');

?>
