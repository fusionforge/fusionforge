<?php
/**
 * Project Statistics Page
 * Copyright 2003 GForge, LLC
 *
 */


require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';

$group_id = getIntFromRequest('group_id');
if ( !$group_id ) {
	exit_no_group();
}

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

$report=new Report();
if ($report->isError()) {
    exit_error($report->getErrorMessage());
}

$area = getStringFromRequest('area');
$SPAN = getIntFromRequest('SPAN', REPORT_TYPE_MONTHLY);
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

if (!$start) {
	$z =& $report->getMonthStartArr();
	$start = $z[0];
}
if (!$end || $end <= $start) {
	$z =& $report->getMonthStartArr();
	$end = $z[count($z)-1];
}

site_project_header(array('title'=>_('Project Activity').' '.$group->getPublicName(),'group'=>$group_id,'toptab'=>'home'));

$area = util_ensure_value_in_set ($area, array ('tracker','forum','docman','taskman','downloads')) ;
if ($SPAN && !is_numeric($SPAN)) { $SPAN = 1; }
if ($start && !is_numeric($start)) { $start = false; }
if ($end && !is_numeric($end)) { $end = false; }

//
// BEGIN PAGE CONTENT CODE
//
?>
<div align="center">
<h1><?php echo _('Project Activity'); ?></h1>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<table><tr>
<td><strong><?php echo _('Areas'); ?>:</strong><br /><?php echo report_area_box('area',$area,$group); ?></td>
<td><strong><?php echo _('Type'); ?>:</strong><br /><?php echo report_span_box('SPAN',$SPAN); ?></td>
<td><strong><?php echo _('Start'); ?>:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo _('End'); ?>:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" /></td>
</tr></table>
</form>
<p>
<img src="/reporting/projectact_graph.php?<?php echo "SPAN=$SPAN&amp;start=$start&amp;end=$end&amp;g_id=$group_id&amp;area=$area"; ?>" width="640" height="480" alt="" />
</p>
</div>
<?php

site_project_footer( array() );

?>
