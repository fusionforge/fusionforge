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

/*

	Relatively simple form to edit/add packages of releases

*/

// only admin can modify packages (vs modifying releases of packages)
if ($submit) {
	/*

		make updates to the database

	*/
	if ($func=='add_package' && $package_name) {

		//create a new package
		db_query("INSERT INTO frs_package (group_id,name,status_id) ".
			"VALUES ('$group_id','". htmlspecialchars($package_name)  ."','1')");
		$feedback .=$Language->getText('project_admin_editpackages','added_package');

	} else if ($func=='update_package' && $package_id && $package_name && $status_id) {
		if ($status_id != 1) {
			//if hiding a package, refuse if it has releases under it
			$res=db_query("SELECT * FROM frs_release WHERE package_id='$package_id' AND status_id=1");
			if (db_numrows($res) > 0) {
				$feedback .= ' Sorry - you cannot hide a package that contains active releases ';
				$status_id=1;
			}
		}
		//update an existing package
		db_query("UPDATE frs_package SET name='". htmlspecialchars($package_name)  ."', status_id='$status_id' ".
			"WHERE package_id='$package_id' AND group_id='$group_id'");
		$feedback .= $Language->getText('project_admin_editpackages','updated_package');

	}

}


frs_admin_header(array('title'=>$Language->getText('project_admin_editpackages','title'),'group'=>$group_id,'pagename'=>'project_admin_editpackages','sectionvals'=>array(group_getname($group_id))));

$res=db_query("SELECT status_id,package_id,name AS package_name FROM frs_package WHERE group_id='$group_id' AND status_id=1");
$rows=db_numrows($res);
if ($res && $rows > 0) {
	echo '<h3>'.$Language->getText('project_admin_editpackages','qrs').'</h3>';
	echo $Language->getText('project_admin_editpackages','qrs_a_file', array('<a href="qrs.php?package_id=' . $package_id . '&group_id=' . $group_id . '">','</a>') ).'<br />';
}
?>
<?php echo  $Language->getText('project_admin_editpackages','packages_info') ?>
<p>
<?php
/*

	Show a list of existing packages
	for this project so they can
	be edited

*/

$res=db_query("SELECT status_id,package_id,name AS package_name FROM frs_package WHERE group_id='$group_id'");
$rows=db_numrows($res);
if (!$res || $rows < 1) {
	echo '<h4>'.$Language->getText('project_admin_editpackages','no_packages_defined').'</h4>';
} else {
	$title_arr=array();
	$title_arr[]=$Language->getText('project_admin_editpackages','releases');
	$title_arr[]=$Language->getText('project_admin_editpackages','package_name');
	$title_arr[]=$Language->getText('project_admin_editpackages','no_packages_status');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	for ($i=0; $i<$rows; $i++) {
		echo '
		<form action="'. $PHP_SELF .'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="func" value="update_package" />
		<input type="hidden" name="package_id" value="'. db_result($res,$i,'package_id') .'" />
		<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td nowrap="nowrap" align="center">
				<span style="font-size:smaller">
					<a href="qrs.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>['.$Language->getText('project_admin_editpackages','add_release').']</strong>
					</a>
				</span>
				<span style="font-size:smaller">
					<a href="showreleases.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>['.$Language->getText('project_admin_editpackages','edit_releases').']</strong>
					</a>
				</span>

			</td>
			<td><span style="font-size:smaller"><input type="text" name="package_name" value="'.db_result($res,$i,'package_name') .'" size="20" maxlength="30" /></span></td>
			<td><span style="font-size:smaller">'.frs_show_status_popup ('status_id', db_result($res,$i,'status_id')).'</span></td>
			<td><input type="submit" name="submit" value="'.$Language->getText('general','update').'" /></td>
			</tr></form>';
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

/*

	form to create a new package

*/

?>
</p>
<h3><?php echo $Language->getText('project_admin_editpackages','new_package_name') ?>:</h3>
<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="func" value="add_package" />
<input type="text" name="package_name" value="" size="20" maxlength="30" />
<p><input type="submit" name="submit" value="<?php echo $Language->getText('project_admin_editpackages','create_package') ?>" /></p>
</form></p>

<?php

frs_admin_footer();

?>
