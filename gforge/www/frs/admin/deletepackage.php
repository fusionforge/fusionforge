<?php
/**
 *
 * Project Admin: Edit Packages
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) GForge, LLC
 *
 * @version   $Id$
 *
 */

require_once('pre.php');	
require_once('www/frs/include/frs_utils.php');
require_once('common/frs/FRSPackage.class');

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

$frsp = new FRSPackage($project,$package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error('Error','Could Not Get FRS Package');
} elseif ($frsp->isError()) {
	exit_error('Error',$frsp->getErrorMessage());
}

/*

	Relatively simple form to delete packages of releases

*/

frs_admin_header(array('title'=>$Language->getText('project_admin_editpackages','title'),'group'=>$group_id));

	echo '<strong>'.$frsp->getName().'</strong><p>';
	echo '
	<form action="/frs/admin/?group_id='.$group_id.'" method="post">
	<input type="hidden" name="func" value="delete_package" />
	<input type="hidden" name="package_id" value="'. $package_id .'" />
	'.$Language->getText('frs_admin','delete_package_warning').'
	<p>
	<input type="checkbox" name="sure" value="1">'.$Language->getText('frs_admin','sure').'<br />
	<input type="checkbox" name="really_sure" value="1">'.$Language->getText('frs_admin','really_sure').'<br />
	<input type="submit" name="submit" value="'.$Language->getText('frs_admin','delete').'" />
	</form>';

frs_admin_footer();

?>
