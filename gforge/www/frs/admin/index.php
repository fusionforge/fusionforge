<?php
/**
 *
 * Project Admin: Edit Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
require_once $gfwww.'frs/include/frs_utils.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';

$group_id = getIntFromRequest('group_id');
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
if (getStringFromRequest('submit')) {
	$func = getStringFromRequest('func');
	$package_id = getIntFromRequest('package_id');
	$package_name = trim(getStringFromRequest('package_name'));
	$status_id = getIntFromRequest('status_id');
	$is_public = getStringFromRequest('is_public');

	/*

		make updates to the database

	*/
	if ($func=='add_package' && $package_name) {

		//create a new package
		$frsp = new FRSPackage($project);
		if (!$frsp || !is_object($frsp)) {
			exit_error('Error','Could Not Get FRS Package');
		} elseif ($frsp->isError()) {
			exit_error('Error',$frsp->getErrorMessage());
		}
		if (!$frsp->create($package_name,$is_public)) {
			exit_error('Error',$frsp->getErrorMessage());
		} else {
			$feedback .=_('Added Package');
		}

	} elseif ($func=='delete_package' && $package_id) {

		//delete a package
		$frsp = new FRSPackage($project,$package_id);
		if (!$frsp || !is_object($frsp)) {
			exit_error('Error','Could Not Get FRS Package');
		} elseif ($frsp->isError()) {
			exit_error('Error',$frsp->getErrorMessage());
		}
		
		$sure = getIntFromRequest("sure");
		$really_sure = getIntFromRequest("really_sure");
		if (!$frsp->delete($sure,$really_sure)) {
			exit_error('Error',$frsp->getErrorMessage());
		} else {
			$feedback .=_('Deleted');
		}

	} else if ($func=='update_package' && $package_id && $package_name && $status_id) {
		$frsp = new FRSPackage($project,$package_id);
		if (!$frsp || !is_object($frsp)) {
			exit_error('Error','Could Not Get FRS Package');
		} elseif ($frsp->isError()) {
			exit_error('Error',$frsp->getErrorMessage());
		}
		if (!$frsp->update($package_name,$status_id)) {
			exit_error('Error',$frsp->getErrorMessage());
		} else {
			$feedback .= _('Updated Package');
		}

	}

}


frs_admin_header(array('title'=>_('Release Edit/File Releases'),'group'=>$group_id));

$res=db_query_params ('SELECT status_id,package_id,name AS package_name 
	FROM frs_package WHERE group_id=$1',
			array($group_id));
$rows=db_numrows($res);
if ($res && $rows > 0) {
	echo '<h3>'._('QRS').'</h3>';
	printf(_('Click here to %1$s quick-release a file %2$s'), '<a href="qrs.php?group_id=' . $group_id . '">', '</a>').'<br />';
}
?>

<fieldset>
<h2><?php echo _("Packages") ?></h2>
<p><?php echo _("You can use packages to group different file releases together, or use them however you like.") ?></p>
<h3><?php echo _("An example of packages:") ?></h3>
<p><strong>Mysql-win</strong><br /><strong>Mysql-unix</strong><br /><strong>Mysql-odbc</strong></p>
<h3><?php echo _("Your Packages:") ?></h3>
<ol>
    <li><?php echo _("Define your packages") ?></li>
    <li><?php echo _("Create new releases of packages") ?></li>
</ol>
<h2><?php echo _("Releases of Packages") ?></h2>
<p><?php echo _("A release of a package can contain multiple files.") ?></p>
<h3><?php echo _("Examples of Releases") ?></h3>
<p><strong>3.22.1</strong><br /><strong>3.22.2</strong><br /><strong>3.22.3</strong></p>
<p><?php echo _("You can create new releases of packages by clicking on <strong>Add/Edit Releases</strong> next to your package name") ?>.</p>
</fieldset>

<?php
/*

	Show a list of existing packages
	for this project so they can
	be edited

*/

if (!$res || $rows < 1) {
	echo '<p><strong>'._('You Have No Packages Defined').'</strong></p>';
} else {
	$title_arr=array();
	$title_arr[]=_('Releases');
	$title_arr[]=_('Package name');
	$title_arr[]=_('Status');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	for ($i=0; $i<$rows; $i++) {
		echo '
		<form action="'. getStringFromServer('PHP_SELF') .'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="func" value="update_package" />
		<input type="hidden" name="package_id" value="'. db_result($res,$i,'package_id') .'" />
		<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td style="white-space: nowrap;" align="center">
					<a href="qrs.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>['._('Add Release').']</strong>
					</a>
				
					<a href="showreleases.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>['._('Edit Releases').']</strong>
					</a>

			</td>
			<td><input type="text" name="package_name" value="'.db_result($res,$i,'package_name') .'" size="20" maxlength="60" /></td>
			<td>'.frs_show_status_popup ('status_id', db_result($res,$i,'status_id')).'</span></td>
			<td><input type="submit" name="submit" value="'._('Update').'" />
				
					<a href="deletepackage.php?package_id='. 
						db_result($res,$i,'package_id') .'&amp;group_id='. $group_id .'"><strong>['._('Delete').']</strong>
					</a>
				
			</td>
			</tr></form>';
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

/*

	form to create a new package

*/

?>

<fieldset>
<legend><?php echo _('Create New Package') ?></legend>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="func" value="add_package" />
<p><strong><?php echo _('New Package Name') ?>:</strong>
<input type="text" name="package_name" value="" size="20" maxlength="30" /></p>
<p><strong><?php echo _('Publicly Viewable'); ?>:</strong>
<input type="radio" name="is_public" value="1" checked="checked" /> <?php echo _('Public'); ?>
<input type="radio" name="is_public" value="0" /> <?php echo _('Private'); ?></p>
<p><input type="submit" name="submit" value="<?php echo _('Create This Package') ?>" /></p>
</form>
</fieldset>

<?php

frs_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
