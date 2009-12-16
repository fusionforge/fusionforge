<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   index.php,v 1.11 2004/08/05 20:48:59 tperdue Exp
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
require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';

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

/*
 * Set the start date to birth of the project.
 */
$res=db_query_params ('SELECT register_time FROM groups WHERE group_id=$1',
			array($group_id));
$report->site_start_date=db_result($res,0,'register_time');

if (!$start) {
	$z =& $report->getMonthStartArr();
	$start = $z[0];
}

if (!$end) {
	$z =& $report->getMonthStartArr();
	$end = $z[ count($z)-1];
}

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
        exit_no_group();
}
if ($group->isError()) {
        if($group->isPermissionDeniedError()) {
                exit_permission_denied($group->getErrorMessage());
        } else {
                exit_error(_('Error'), $group->getErrorMessage());
        }
}

if (!session_loggedin()) {
	exit_not_logged_in();
}

$perm =& $group->getPermission( session_get_user() );
if (!$perm || !is_object($perm)) {
	exit_error('Error','Error - Could Not Get Perm');
} elseif ($perm->isError()) {
	exit_error('Error',$perm->getErrorMessage());
}


//
//	Get list of trackers this person can see
//

$restracker = db_query_params ('SELECT DISTINCT agl.group_artifact_id,agl.name
	FROM artifact_group_list agl, role_setting rs, user_group ug
        WHERE agl.group_id=$1
        AND agl.group_id=ug.group_id
        AND ug.user_id=$2
        AND ug.role_id=rs.role_id
        AND (
                           (rs.section_name = $3 AND rs.value = $4)
                           OR (rs.section_name = $5 AND rs.value = $6)
                           OR (rs.section_name = $6 AND rs.value::integer >= 1 AND rs.ref_id = agl.group_artifact_id)
        )',
			array($group_id,
			      user_getid() ,
			      'projectadmin',
			      'A',
			      'trackeradmin',
			      2,
			      'tracker'));
echo db_error();

//
//	Build list of reports
//
$vals=array();
$labels=array();
$vals[]='activity'; $labels[]='Response Time';
$vals[]='assignee'; $labels[]='By Assignee';


//required params for site_project_header();
$params=array();
$params['group']=$group_id;
$params['toptab']='tracker';

echo site_project_header($params);

?>
<div align="center">
<h1>Project Activity</h1>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<table><tr>
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
</div>
<?php echo site_project_footer(array()); ?>
