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
require_once('common/reporting/report_utils.php');
require_once('common/reporting/ReportProjectTime.class');

session_require( array('group'=>$sys_stats_group) );

global $Language;

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

if (!$start) {
	$z =& $report->getMonthStartArr();
	$start = $z[count($z)-1];
}

echo report_header($Language->getText('reporting_ttbp','title'));

$a[]=$Language->getText('reporting_ttbp','bytask');
$a[]=$Language->getText('reporting_ttbp','bycategory');
$a[]=$Language->getText('reporting_ttbp','bysubproject');
$a[]=$Language->getText('reporting_ttbp','byuser');

$a2[]='tasks';
$a2[]='category';
$a2[]='subproject';
$a2[]='user';

?>
<h3><?php echo $Language->getText('reporting_ttbp','title'); ?></h3>
<p>
<form action="<?php echo $PHP_SELF; ?>" method="get">
<input type="hidden" name="sw" value="<?php echo $sw; ?>">
<input type="hidden" name="typ" value="<?php echo $typ; ?>">
<table><tr>
<td><strong><?php echo $Language->getText('reporting','project'); ?>:</strong><br /><?php echo report_group_box('g_id',$g_id); ?></td>
<td><strong><?php echo $Language->getText('reporting','type'); ?>:</strong><br /><?php echo html_build_select_box_from_arrays($a2,$a,'type',$type,false); ?></td>
<td><strong><?php echo $Language->getText('reporting','start'); ?>:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo $Language->getText('reporting','end'); ?>:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="<?php echo $Language->getText('reporting','refresh'); ?>"></td>
</tr></table>
</form>
<p>
<?php 
	if ($g_id && $typ=='r') {
		$report=new ReportProjectTime($g_id,$type,$start,$end);

		$labels = $report->labels;
	    $data = $report->getData();

	    echo $HTML->listTableTop (array($Language->getText('reporting','type'),
	    		$Language->getText('reporting','time')));

	    for ($i=0; $i<count($labels); $i++) {

		echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>'.
			'<td>'. $labels[$i] .'</td><td>'. $data[$i] .'</td></tr>';
	    }

	    echo $HTML->listTableBottom ();

	} elseif ($g_id) { ?>
	<img src="projecttime_graph.php?<?php echo "start=$start&end=$end&g_id=$g_id&type=$type"; ?>" width="640" height="480">
	<p>
	<?php

}

echo report_footer();

?>
