<?php
/**
  *
  * Project Statistics Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('project_stats_utils.php');

if ( !$group_id ) {
	exit_no_group();
}

site_project_header(array('title'=>"Project statistics ".$groupname,'group'=>$group_id,'toptab'=>'home'));

//
// BEGIN PAGE CONTENT CODE
//

if (!$report) {
	$report='last_7';
}

print '<div align="center">';
print '<span style="font-size:bigger"><strong>Usage Statistics </strong></span><br />';
print '<img src="stats_graph.png?group_id='.$group_id.'&amp;report='. $report .'" />';
print '</div>';

if ( $report == 'last_7' ) {

	print '<p>';
	stats_project_daily( $group_id, 7 );

} elseif ( $report == 'last_30' ) {

	print '<p>';
	stats_project_daily( $group_id, 30 );

} elseif ( $report == 'months' ) {

	print '<p>';
	stats_project_monthly( $group_id );

} else {

	   // default stats display, DAILY
	print '<p>';
	stats_project_daily( $group_id, 7 );

}

print '</p><br /><p>';
stats_project_all( $group_id );

$reports_ids=array();
$reports_ids[]='last_7';
$reports_ids[]='last_30';
$reports_ids[]='months';

$reports_names=array();
$reports_names[]='Last 7 Days';
$reports_names[]='Last 30 Days';
$reports_names[]='Monthly';

?>
</p>
<div align="center">
<form action="index.php" method="get">
View Reports:
<?php

	echo html_build_select_box_from_arrays($reports_ids, $reports_names, 'report', $report, false);

?>
&nbsp; 
<input type="submit" value="Change Stats View" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
</form>
</div>


<?php
if ($group_id && user_ismember($group_id)) {
	print "
	<p>
	Detailed statistics for:
	<ul>
	<li><a href=\"/tracker/?group_id=$group_id&period=$view&span=$span\">Tracker</a></li>
	<li><a href=\"/pm/reporting/?group_id=$group_id&period=$view&span=$span\">Tasks</a></li>
	</ul>
	</p>";
}

site_project_footer( array() );
?>
