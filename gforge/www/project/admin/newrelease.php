<?php
/**
  *
  * Project Admin: Create a New Release
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: newrelease.php,v 1.10 2001/05/22 19:48:40 pfalcon Exp $
  *
  */


require_once('pre.php');    
require_once('frs.class');
require_once('www/project/admin/project_admin_utils.php');

$project=&group_get_object($group_id);

exit_assert_object($project,'Project');

$perm =& $project->getPermission(session_get_user());

if (!$perm->isReleaseTechnician()) {
    exit_permission_denied();
}

// Create a new FRS object
$frs = new FRS($group_id);

if( $submit ) {

	$release_id = $frs->frsAddRelease($release_name, $package_id);
	if( !$frs->isError() ) {
		header("Location: editreleases.php?package_id=$package_id&release_id=$release_id&group_id=$group_id");
	}

} else {

project_admin_header(array('title'=>'Release New File Version','group'=>$group_id,'pagename'=>'project_admin_newrelease','sectionvals'=>array(group_getname($group_id))));

?>

<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<table border="0" cellpadding="2" cellspacing="2">
<tr>
	<td>New release name:</td>
	<td><input type="text" name="release_name" value="" size="20" maxlength="25"></td>
</tr>
<tr>
	<td>Of which package:</b></td>
	<td><?php echo frs_show_package_popup($group_id,'package_id',$package_id); ?></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" name="submit" value="Create This Release"></td>
</tr>
</table>
</form>

<?php

}

project_admin_footer(array());

?>
