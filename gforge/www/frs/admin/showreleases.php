<?php
/**
 *
 * Project Admin: Edit Releases of Packages
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) GForge, LLC
 *
 * @version   $Id$
 *
 */


/* Updated rewrite of the File Release System to clean up the UI 
 * a little and incorporate FRS.class.		-Darrell
 */

require_once('pre.php');	
require_once('www/frs/include/frs_utils.php');

if (!$group_id) {
	exit_no_group();
}

$project =& group_get_object($group_id);
if (!$project || $project->isError()) {
	exit_error('Error',$project->getErrorMessage());
}
$perm =& $project->getPermission(session_get_user());
if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

if (!$package_id) {
	header("Location /frs/admin/?group_id=$group_id?feedback=Choose+Package");
	exit;
}

/*
	Get the releases of this package
*/
$res=db_query("SELECT p.name AS package_name,r.* 
	FROM frs_release r, frs_package p 
	WHERE 
	p.package_id=r.package_id
	AND p.group_id='$group_id' 
	AND p.package_id='$package_id'");
if (!$res || db_numrows($res) < 1) {
	exit_error($Language->getText('general','error'),$Language->getText('project_admin_showreleases','no_release'));
}

/*
	Display a list of releases in this package
*/
frs_admin_header(array('title'=>$Language->getText('project_admin_showreleases','title'),'group'=>$group_id,'pagename'=>'project_admin_editreleases','sectionvals'=>array(group_getname($group_id))));

$title_arr=array();
$title_arr[]=$Language->getText('project_admin_showreleases','package_name');
$title_arr[]=$Language->getText('project_admin_showreleases','release_name');
$title_arr[]=$Language->getText('project_admin_showreleases','date');

echo $GLOBALS['HTML']->listTableTop ($title_arr);

for ($i=0; $i<db_numrows($res); $i++) {
	echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>
			<td>'.db_result($res,$i,'package_name').'</td>
			<td><a href="editrelease.php?group_id='.$group_id
				.'&amp;package_id='.$package_id
				.'&amp;release_id='.db_result($res,$i,'release_id').'">'. 
				db_result($res,$i,'name').' ['.$Language->getText('general','edit').']</a></td><td>'.
				date('Y-m-d H:i',db_result($res,$i,'release_date')).'</td></tr>';
}

echo $GLOBALS['HTML']->listTableBottom();

frs_admin_footer(array());

?>
