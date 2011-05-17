<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/ReportSiteTime.class.php';

session_require_global_perm ('forge_stats', 'read') ;

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$typ = getStringFromRequest('typ');
$type = getStringFromRequest('type');
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

if (!$start || !$end) $z =& $report->getMonthStartArr();

if (!$start) {
	$start = $z[0];
}
if (!$end) {
	$end = $z[count($z)-1];
}
if ($end < $start) list($start, $end) = array($end, $start);

if ($typ != 'r' && $start == $end) {
	$error_msg .= _('Start and end dates must be different');
}

report_header(_('Site-Wide Time Tracking'));

$a[]=_('By Task');
$a[]=_('By Category');
$a[]=_('By Subproject');
$a[]=_('By User');

$a2[]='tasks';
$a2[]='category';
$a2[]='subproject';
$a2[]='user';

?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<table><tr>
<td><strong><?php echo _('Type'); ?>:</strong><br /><?php echo html_build_select_box_from_arrays($a2,$a,'type',$type,false); ?></td>
<td><strong><?php echo _('Start'); ?>:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo _('End'); ?>:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td>
	<input type="hidden" name="typ" value="<?php echo $typ; ?>" />
	<input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" />
</td>
</tr></table>
</form>

<?php 
if ($typ =='r') {
	$report=new ReportSiteTime($type,$start,$end);
	$labels = $report->labels;
	$data = $report->getData();

	echo $HTML->listTableTop (array(_('Type'), _('Time')));

	for ($i=0; $i<count($labels); $i++) {
		echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>'.
		'<td>'. $labels[$i] .'</td><td>'. $data[$i] .'</td></tr>';
	}

	echo $HTML->listTableBottom ();
} elseif ($start != $end) { ?>
<p>
<img src="sitetime_graph.php?<?php echo "start=$start&amp;end=$end&amp;type=$type"; ?>" width="640" height="480" alt="" />
</p>
<?php
}
report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
