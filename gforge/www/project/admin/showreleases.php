<?php
/**
  *
  * Project Admin: Edit Releases of Packages
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/* Updated rewrite of the File Release System to clean up the UI 
 * a little and incorporate FRS.class.		-Darrell
 */

require_once('pre.php');	
require_once('www/project/admin/project_admin_utils.php');

if (!$group_id) {
	exit_no_group();
}
if (!$package_id) {
	header("Location editpackages.php?group_id=$group_id?feedback=Choose+Package");
	exit;
}

/*
	Set up and verify permissions
*/
session_require(array('group'=>$group_id));

$project =& group_get_object($group_id);

exit_assert_object($project,'Project');

$perm =& $project->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
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
	exit_error('Error','No Releases Of This Package Are Available');
}

/*
	Display a list of releases in this package
*/
project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_editreleases','sectionvals'=>array(group_getname($group_id))));

$title_arr=array();
$title_arr[]='Package Name';
$title_arr[]='Release Name';
$title_arr[]='Date';

echo $GLOBALS['HTML']->listTableTop ($title_arr);

for ($i=0; $i<db_numrows($res); $i++) {
	echo '<TR "'. $HTML->boxGetAltRowStyle($i) .'">
			<TD>'.db_result($res,$i,'package_name').'</TD>
			<TD><A HREF="editrelease.php?group_id='.$group_id
				.'&package_id='.$package_id
				.'&release_id='.db_result($res,$i,'release_id').'">'. 
				db_result($res,$i,'name').' [edit]</A></TD><TD>'.
				date('Y-m-d',db_result($res,$i,'release_date')).'<TD></TR>';
}

echo $GLOBALS['HTML']->listTableBottom();

project_admin_footer(array());

?>
