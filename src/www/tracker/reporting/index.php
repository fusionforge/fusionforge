<?php
/**
 * Reporting System
 *
 * Copyright 2003, Tim Perdue, tim@gforge.org
 * Copyright 2004 (c) GForge LLC
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/ReportTrackerAct.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeFactoryHtml.class.php';

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');
$area = getFilteredStringFromRequest('area', '/^[a-z]+$/');
$SPAN = getIntFromRequest('SPAN', REPORT_TYPE_MONTHLY);
$start = getIntFromRequest('start');
$end = getIntFromRequest('end');

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
if ($group->isError()) {
	if ($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage());
	} else {
		exit_error($group->getErrorMessage(), 'tracker');
	}
}

/*
 * Set the start date to birth of the project.
 */
$res = db_query_params('SELECT register_time FROM groups WHERE group_id=$1',
			array($group_id));
$report->site_start_date = db_result($res,0,'register_time');

if (!$start || !$end) $z =& $report->getMonthStartArr();

if (!$start) {
	$start = $z[0];
}
if (!$end) {
	$end = $z[count($z)-1];
}
if ($end < $start) list($start, $end) = array($end, $start);

//
//	Get list of trackers this person can see
//

$atf = new ArtifactTypeFactory($group);
$tids = array();
foreach ($atf->getArtifactTypes() as $at) {
	if (forge_check_perm ('tracker', $at->getID(), 'read')) {
		$tids[] = $at->getID();
	}
}

$restracker = db_query_params('SELECT group_artifact_id, name
			FROM artifact_group_list
			WHERE group_artifact_id = ANY ($1)',
				array(db_int_array_to_any_clause($tids)));
echo db_error();

//
//	Build list of reports
//
$vals=array();
$labels=array();
$vals[]='activity'; $labels[]=_('Response Time');
$vals[]='assignee'; $labels[]=_('By Assignee');

if ($atid) {
	$h = new ArtifactTypeHtml($group, $atid);
} else {
	$h = new ArtifactTypeFactoryHtml($group);
}

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginPie();
html_use_jqueryjqplotpluginhighlighter();
html_use_jqueryjqplotplugindateAxisRenderer();
html_use_jqueryjqplotpluginBar();

$h->header(array('title' => _('Tracker Activity Reporting')));

?>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<table class="centered">
<tr>
<td>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<strong><?php echo _('Tracker')._(': '); ?></strong><br />
<?php echo html_build_select_box($restracker,'atid',$atid,false); ?></td>
<td><strong><?php echo _('Area')._(': '); ?></strong><br />
<?php echo html_build_select_box_from_arrays($vals, $labels, 'area',$area,false); ?></td>
<td><strong><?php echo _('Type')._(': '); ?></strong>
<br /><?php echo report_span_box('SPAN',$SPAN,true); ?></td>
<td><strong><?php echo _('Start Date')._(': '); ?></strong><br />
<?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong><?php echo _('End Date')._(': '); ?></strong><br />
<?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="<?php echo _("Refresh") ?>" /></td>
</tr>
</table>
</form>
<?php
if ($start == $end) {
	echo '<p class="error">'._('Start and end dates must be different').'</p>';
} else {
	if ($atid) {
		if (!$area || $area == 'activity') {
			if (!trackeract_graph($group_id, 'activity', $SPAN, $start, $end, $atid)) {
				echo '<p class="error">'._('Error during graphic computation.').'</p>';
			}
		} else {
			if (!trackerpie_graph($group_id, $area, $SPAN, $start, $end, $atid)) {
				echo '<p class="error">'._('Error during graphic computation.').'</p>';
			}
		}
	}
}
$h->footer(array());
