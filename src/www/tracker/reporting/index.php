<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 *
 * @version   index.php,v 1.11 2004/08/05 20:48:59 tperdue Exp
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeFactoryHtml.class.php';

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
        if($group->isPermissionDeniedError()) {
                exit_permission_denied($group->getErrorMessage());
        } else {
                exit_error($group->getErrorMessage(), 'tracker');
        }
}

/*
 * Set the start date to birth of the project.
 */
$res=db_query_params ('SELECT register_time FROM groups WHERE group_id=$1',
			array($group_id));
$report->site_start_date=db_result($res,0,'register_time');

if (!$start || !$end) $z =& $report->getMonthStartArr();

if (!$start) {
	$start = $z[0];
}
if (!$end) {
	$end = $z[count($z)-1];
}
if ($end < $start) list($start, $end) = array($end, $start);

if (!session_loggedin()) {
	exit_not_logged_in();
}

//
//	Get list of trackers this person can see
//

$atf = new ArtifactTypeFactory ($group) ;
$tids = array () ;
foreach ($atf->getArtifactTypes() as $at) {
	if (forge_check_perm ('tracker', $at->getID(), 'read')) {
		$tids[] = $at->getID() ;
	}
}

$restracker = db_query_params ('SELECT group_artifact_id, name
			FROM artifact_group_list
			WHERE group_artifact_id = ANY ($1)',
			       array (db_int_array_to_any_clause ($tids))) ;
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

$h->header(array('title' => _('Project Activity')));

?>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<table style="margin-left: auto; margin-right: auto;">
<tr>
<td>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<strong>Tracker:</strong><br /><?php echo html_build_select_box($restracker,'atid',$atid,false); ?></td>
<td><strong>Area:</strong><br /><?php echo html_build_select_box_from_arrays($vals, $labels, 'area',$area,false); ?></td>
<td><strong>Type:</strong><br /><?php echo report_span_box('SPAN',$SPAN,true); ?></td>
<td><strong>Start:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong>End:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="Refresh" /></td>
</tr></table>
</form>
<p>
<?php if ($atid) {
		if (!$area || $area == 'activity') {
	?>
	<img src="trackeract_graph.php?<?php echo "SPAN=$SPAN&amp;start=$start&amp;end=$end&amp;group_id=$group_id&amp;atid=$atid"; ?>" width="640" height="480" alt="" />
	<?php
		} else {
	?>
	<img src="trackerpie_graph.php?<?php echo "SPAN=$SPAN&amp;start=$start&amp;end=$end&amp;group_id=$group_id&amp;atid=$atid&amp;area=$area"; ?>" width="640" height="480" alt="" />
	<?php

		}

}
?>
</p>
<?php $h->footer(array()); ?>
