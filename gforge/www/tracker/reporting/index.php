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
require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');
require_once('www/tracker/include/ArtifactTypeHtml.class');

$report=new Report();
if ($report->isError()) {
	exit_error($report->getErrorMessage());
}

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Error - Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
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
if ($perm->isArtifactAdmin()) {
	$alevel='';
} else {
	$alevel=' ap.user_id='. user_getid() ." AND ap.perm_level > 1 AND ";
}

$sql="SELECT DISTINCT agl.group_artifact_id,agl.name
	FROM artifact_group_list agl 
	LEFT JOIN artifact_perm ap
	ON (ap.group_artifact_id=agl.group_artifact_id)
	WHERE
	$alevel
	agl.group_id='$group_id'";
$restracker=db_query($sql);
echo db_error();

//
//	Build list of reports
//
$vals[]='activity'; $labels[]='Response Time';
$vals[]='category'; $labels[]='By Category';
$vals[]='group'; $labels[]='By Group';
$vals[]='resolution'; $labels[]='By Resolution';
$vals[]='technician'; $labels[]='By Tecnician';


//required params for site_project_header();
$params['group']=$group_id;
$params['toptab']='tracker';
$params['pagename']='tracker';
$params['sectionvals']=array($group->getPublicName());

echo site_project_header($params);

?>
<h3>Project Activity</h3>
<p>
<form action="<?php echo $PHP_SELF; ?>" method="get">
<input type="hidden" name="sw" value="<?php echo $sw; ?>">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<table><tr>
<td><strong>Tracker:</strong><br /><?php echo html_build_select_box($restracker,'atid',$atid,false); ?></td>
<td><strong>Area:</strong><br /><?php echo html_build_select_box_from_arrays($vals, $labels, 'area',$area,false); ?></td>
<td><strong>Type:</strong><br /><?php echo report_span_box('SPAN',$SPAN,true); ?></td>
<td><strong>Start:</strong><br /><?php echo report_months_box($report, 'start', $start); ?></td>
<td><strong>End:</strong><br /><?php echo report_months_box($report, 'end', $end); ?></td>
<td><input type="submit" name="submit" value="Refresh"></td>
</tr></table>
</form>
<p>
<?php if ($atid) {
		if (!$area || $area == 'activity') { 
	?>
	<img src="trackeract_graph.php?<?php echo "SPAN=$SPAN&start=$start&end=$end&group_id=$group_id&atid=$atid"; ?>" width="640" height="480">
	<p>
	<?php
		} else {
	?>
	<img src="trackerpie_graph.php?<?php echo "SPAN=$SPAN&start=$start&end=$end&group_id=$group_id&atid=$atid&area=$area"; ?>" width="640" height="480">
	<p>
	<?php

		}

}

echo site_project_footer(array());

?>
