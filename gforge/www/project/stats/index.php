<?php
/**
 * Project Statistics Page
 * Copyright 2003 GForge, LLC
 *
 * @version   $Id$
 */


require_once('../../env.inc.php');
require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');

$group_id = getIntFromRequest('group_id');
if ( !$group_id ) {
	exit_no_group();
}

$report=new Report();
if ($report->isError()) {
    exit_error($report->getErrorMessage());
}

$area = getStringFromRequest('area');
$SPAN = getStringFromRequest('SPAN');
$start = getStringFromRequest('start');
$end = getStringFromRequest('end');

if (!$start) {
	$z =& $report->getMonthStartArr();
	$start = $z[count($z)-1];
}

site_project_header(array('title'=>$Language->getText('reporting','project_activity_title').' '.$groupname,'group'=>$group_id,'toptab'=>'home'));

if ($area && !is_numeric($area)) { $area = 1; }
if ($SPAN && !is_numeric($SPAN)) { $SPAN = 1; }
if ($start && !is_numeric($start)) { $start = false; }
if ($end && !is_numeric($end)) { $end = false; }

//
// BEGIN PAGE CONTENT CODE
//
?>
<h3><?php echo $Language->getText('reporting','project_activity_title'); ?></h3>
<p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<table><tr>
<td><strong><?php echo $Language->getText('reporting','area'); ?>:</strong><br /><?php echo report_area_box('area',$area); ?></td>
<td><strong><?php echo $Language->getText('reporting','type'); ?>:</strong><br /><?php echo report_span_box('SPAN',$SPAN); ?></td>
<td><strong><?php echo $Language->getText('reporting','start'); ?>:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo $Language->getText('reporting','end'); ?>:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="<?php echo $Language->getText('reporting','refresh'); ?>"></td>
</tr></table>
</form>
<p>
<img src="/reporting/projectact_graph.php?<?php echo "SPAN=$SPAN&start=$start&end=$end&g_id=$group_id&area=$area"; ?>" width="640" height="480">
<?php

site_project_footer( array() );

?>
