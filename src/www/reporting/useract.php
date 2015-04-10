<?php
/**
 * Reporting System
 *
 * Copyright 2003-2004 (c) GForge LLC
 * Copyright 2013, Franck Villaume - TrivialDev
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
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/ReportUserAct.class.php';

session_require_global_perm('forge_stats', 'read');

global $HTML;

$report = new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$sw = getStringFromRequest('sw');
$dev_id = getIntFromRequest('dev_id');
$area = getFilteredStringFromRequest('area', '/^[a-z]+$/');
$SPAN = getIntFromRequest('SPAN');
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

if ($start == $end) {
	$error_msg .= _('Start and end dates must be different');
}

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginhighlighter();
html_use_jqueryjqplotplugindateAxisRenderer();
html_use_jqueryjqplotpluginBar();

report_header(_('User Activity'));

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
echo html_e('p', array(), _('Choose the <strong>First Letter</strong> of the name of the person you wish to report on.'));

for ($i = 0; $i < count($abc_array); $i++) {
	if ($sw == $abc_array[$i]) {
		echo html_e('strong', array(), $abc_array[$i]);
	} else {
		echo util_make_link('/reporting/useract.php?sw='.$abc_array[$i], $abc_array[$i]);
	}
}

if ($sw) {
	echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'get'));
	echo html_e('input', array('type' => 'hidden', 'name' => 'sw', 'value' => $sw));
	echo $HTML->listTableTop();
	$cells = array();
	$cells[][] = html_e('strong', array(), _('User')._(':').html_e('br').report_useract_box('dev_id', $dev_id, $sw));
	$cells[][] = html_e('strong', array(), _('Area')._(':').html_e('br').report_area_box('area', $area));
	$cells[][] = html_e('strong', array(), _('Type')._(':').html_e('br').report_span_box('SPAN', $SPAN));
	$cells[][] = html_e('strong', array(), _('Start Date')._(':').html_e('br').report_months_box($report, 'start', $start));
	$cells[][] = html_e('strong', array(), _('End Date')._(':').html_e('br').report_months_box($report, 'end', $end));
	$cells[][] = html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Refresh')));
	echo $HTML->multiTableRow(array(), $cells);
	echo $HTML->listTableBottom();
	echo $HTML->closeForm();
	if ($dev_id && $start != $end) {
		report_actgraph('user', $SPAN, $start, $end, $dev_id, $area);
	}
}

report_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
