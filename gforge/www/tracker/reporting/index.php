<?php
/**
 *	GForge Reporting System
 *  Copyright 2003 GForge LLC
 *
 *  THIS SOFTWARE IS PROPRIETARY
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
	$alevel=' >= 0';
} else {
	$alevel=' > 1';
}

$sql="SELECT agl.group_artifact_id,agl.name
	FROM artifact_group_list agl,artifact_perm ap
	WHERE agl.group_artifact_id=ap.group_artifact_id
	AND ap.user_id='". user_getid() ."'
	AND ap.perm_level $alevel
	AND agl.group_id='$group_id'";
$restracker=db_query($sql);

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
