<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/ReportUserTime.class.php';

session_require_global_perm ('forge_stats', 'read') ;

global $HTML, $error_msg;

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$start = getIntFromRequest('start');
$end = getIntFromRequest('end');
$sw = getStringFromRequest('sw');
$typ = getStringFromRequest('typ');
$dev_id = getIntFromRequest('dev_id');
$type = getStringFromRequest('type');

if (!$start || !$end) {
	$z =& $report->getMonthStartArr();
}

if (!$start) {
	$start = $z[0];
}
if (!$end) {
	$end = $z[count($z)-1];
}
if ($end < $start) {
	list($start, $end) = array($end, $start);
}

if ($typ != 'r' && $start == $end) {
	$error_msg .= _('Start and end dates must be different');
}

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginPie();
html_use_jqueryjqplotpluginhighlighter();
html_use_jqueryjqplotplugindateAxisRenderer();

report_header(_('User Time Reporting'));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
echo '<p>';
echo _('Choose the <strong>First Letter</strong> of the name of the person you wish to report on.');
echo '</p>';

echo html_ao('div', array('class' => 'abc'));
for ($i=0; $i<count($abc_array); $i++) {
	if ($sw == $abc_array[$i]) {
	    echo html_e('strong', array(), $abc_array[$i]);
	} else {
		echo util_make_link('/reporting/usertime.php?sw='.$abc_array[$i].'&amp;typ='.$typ, $abc_array[$i]);
	}
}
echo html_ac(html_ap() -1); // </div> .abc


if ($sw) {

	$a[]=_('By Task');
	$a[]=_('By Category');
	$a[]=_('By Subproject');

	$a2[]='tasks';
	$a2[]='category';
	$a2[]='subproject';

	echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'get'));
	?>
	<input type="hidden" name="sw" value="<?php echo $sw; ?>" />
	<input type="hidden" name="typ" value="<?php echo $typ; ?>" />
	<table><tr>
	<td><strong><?php echo _('User')._(':'); ?></strong><br /><?php echo report_usertime_box('dev_id',$dev_id,$sw); ?></td>
	<td><strong><?php echo _('Type')._(':'); ?></strong><br /><?php echo html_build_select_box_from_arrays($a2,$a,'type',$type,false); ?></td>
	<td><strong><?php echo _('Start Date')._(':'); ?></strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
	<td><strong><?php echo _('End Date')._(':'); ?></strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
	<td><br><input type="submit" name="submit" value="<?php echo _('Refresh'); ?>" /></td>
	</tr></table>
	<?php
	echo $HTML->closeForm();

	if ($dev_id && $typ=='r') {
		$report=new ReportUserTime($dev_id,$type,$start,$end);
		$labels = $report->labels;
		$data = $report->getData();

		echo $HTML->listTableTop(array(_('Type'), _('Time')));

		for ($i=0; $i<count($labels); $i++) {
			echo '<tr>'.'<td>'. $labels[$i] .'</td><td>'. $data[$i] .'</td></tr>';
		}

		echo $HTML->listTableBottom();

	} elseif ($dev_id && $start != $end) {
		report_timegraph('user', $type, $start, $end, $dev_id);
	}
}

report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
