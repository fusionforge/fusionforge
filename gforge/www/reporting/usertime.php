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

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/ReportUserTime.class');

session_require( array('group'=>$sys_stats_group) );

global $Language;

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$start = getStringFromRequest('start');
$end = getStringFromRequest('end');
$sw = getStringFromRequest('sw');
$typ = getStringFromRequest('typ');
$dev_id = getStringFromRequest('dev_id');
$type = getStringFromRequest('type');

if (!$start) {
	$z =& $report->getMonthStartArr();
	$start = $z[count($z)-1];
}


echo report_header($Language->getText('reporting_ut','title'));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
echo $Language->getText('reporting_ut','description');

for ($i=0; $i<count($abc_array); $i++) {
	if ($sw == $abc_array[$i]) {
		echo '<strong>'.$abc_array[$i].'</strong>&nbsp;';
	} else { 
		echo '<a href="usertime.php?sw='.$abc_array[$i].'&typ='.$typ.'">'.$abc_array[$i].'</A>&nbsp;';
	}
}

if ($sw) {

	$a[]=$Language->getText('reporting_ttbp','bytask');
	$a[]=$Language->getText('reporting_ttbp','bycategory');
	$a[]=$Language->getText('reporting_ttbp','bysubproject');

	$a2[]='tasks';
	$a2[]='category';
	$a2[]='subproject';

	?>
	<h3><?php echo $Language->getText('reporting_ut','title'); ?></h3>
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
	<input type="hidden" name="sw" value="<?php echo $sw; ?>">
	<input type="hidden" name="typ" value="<?php echo $typ; ?>">
	<table><tr>
	<td><strong><?php echo $Language->getText('reporting','user'); ?>:</strong><br /><?php echo report_usertime_box('dev_id',$dev_id,$sw); ?></td>
	<td><strong><?php echo $Language->getText('reporting','type'); ?>:</strong><br /><?php echo html_build_select_box_from_arrays($a2,$a,'type',$type,false); ?></td>
	<td><strong><?php echo $Language->getText('reporting','start'); ?>:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
	<td><strong><?php echo $Language->getText('reporting','end'); ?>:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
	<td><input type="submit" name="submit" value="<?php echo $Language->getText('reporting','refresh'); ?>"></td>
	</tr></table>
	</form>
	<p>
	<?php
		if ($dev_id && $typ=='r') {
			$report=new ReportUserTime($dev_id,$type,$start,$end);
			$labels = $report->labels;
			$data = $report->getData();

			echo $HTML->listTableTop (array('Type','Time'));

			for ($i=0; $i<count($labels); $i++) {

				echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>'.
					'<td>'. $labels[$i] .'</td><td>'. $data[$i] .'</td></tr>';

			}

			echo $HTML->listTableBottom ();

		} elseif ($dev_id) { ?>
		<img src="usertime_graph.php?<?php echo "start=$start&end=$end&dev_id=$dev_id&type=$type"; ?>" width="640" height="480">
		<p>
		<?php

	}

}

echo report_footer();

?>
