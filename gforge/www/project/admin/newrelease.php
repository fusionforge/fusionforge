<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: newrelease.php,v 1.5 2000/12/13 18:59:06 dbrogdon Exp $

ob_start();

require ('pre.php');    
require ('frs.class');
require ($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');
session_require(array('group'=>$group_id));
$project=&group_get_object($group_id);
if (!$project->userIsReleaseTechnician()) exit_permission_denied();

project_admin_header(array('title'=>'Release New File Version','group'=>$group_id));

// Create a new FRS object
$frs = new FRS($group_id);

if( $submit ) {
	$release_id = $frs->frsAddRelease($release_name, $package_id);
	if( !$frs->isError() ) {
		header("Location: editreleases.php?package_id=$package_id&release_id=$release_id&group_id=$group_id");
		ob_end_flush();
?>

<h3>Release Added!</h3>
Click here to <a href="editreleases.php?package_id=<?php echo $package_id; ?>&release_id=<?php echo $release_id; ?>&group_id=<?php echo $group_id; ?>">edit this release</a>.

<?php
	}
} else {
?>

<h3>Create New Release:</h3>
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
