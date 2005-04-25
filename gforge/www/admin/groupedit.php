<?php
/**
 * Site Admin group properties editing page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
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
require_once('common/include/license.php');
require_once('www/admin/admin_utils.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

// This function performs very update
function do_update(&$group, $is_public, $status, $license,
		   $group_type, $unix_box, $http_domain, $scm_box='') {
	global $feedback;
	global $Language;

	db_begin();

	if (!$group->setStatus(session_get_user(), $status)) {
		$feedback .= $group->getErrorMessage();
		db_rollback();
		return false;
	}

	if (!$group->updateAdmin(session_get_user(), $is_public, $license, $group_type, $unix_box, $http_domain)) {
		$feedback .= $group->getErrorMessage();
		db_rollback();
		return false;
	}

	if(!$group->setSCMBox($scm_box)) {
		$feedback .= $group->getErrorMessage();
		db_rollback();
		return false;
	}
	db_commit();

	$feedback .= $Language->getText('admin_groupedit','updated').'<br /> ';

	return true;
}


if ($submit) {

	do_update($group, $form_public, $form_status, $form_license,
		  1, $form_box, $form_domain, $form_scm_box);

} else if ($resend) {

	$group->sendApprovalEmail();
	$feedback .= $Language->getText('admin_groupedit','instruction_email_sent').'<br /> ';

}

site_admin_header(array('title'=>$Language->getText('admin_groupedit','title')));

echo '<h2>'.$group->getPublicName().'</h2>' ;?>

<p>
<?php print "<a href=\"/project/admin/?group_id=$group_id\"><h3>".$Language->getText('admin_groupedit','project_admin'). "</h3></a>"; ?></p>
<?php print "<a href=\"groupdelete.php?group_id=$group_id\"><h3>".$Language->getText('admin_groupdelete','title'). "</h3></a>"; ?></p>

<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">


<table>
<tr>
<td>
<?php echo $Language->getText('admin_groupedit','status') ?>:
</td>
<td>
<?php	// PLEASE DONT TRANSLATE THIS, THIS IS DATABASE INFO THAT CANT BE DIFFERENT AMONG LANGUAGES
$status = $group->getStatus();
if($status == 'P') {
	// we cannot update the status of a pending project
	echo '<input type="hidden" name="form_status" value="P" />';
	echo $Language->getText('admin_groupedit','pending').'&nbsp; &nbsp; ';
} else {
	echo html_build_select_box_from_arrays(
		array(
			'I',
			'A',
			'P',
			'H'
		),
		array(
			$Language->getText('admin_groupedit','incomplete'),
			$Language->getText('admin_groupedit','active'),
			$Language->getText('admin_groupedit','pending'),
			$Language->getText('admin_groupedit','holding')
		),
		'form_status', $status, false
	);
} ?>
</td>
</tr>
<tr>
<td>
<?php echo $Language->getText('admin_groupedit','public') ?>:
</td>
<td>
<?php 	// PLEASE DONT TRANSLATE THIS, THIS IS DATABASE INFO THAT CANT BE DIFFERENT AMONG LANGUAGES
	echo html_build_select_box_from_arrays(
	array(
		'0',
		'1'
	),
	array(
		$Language->getText('admin_groupedit','no'),
		$Language->getText('admin_groupedit','yes')
),
	'form_public', $group->isPublic(), false
); ?>

</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_groupedit','unix_group_name'); ?>
</td>
<td>
<?php echo $group->getUnixName(); ?>
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin','license'); ?>
</td>
<td>
<?php
	echo license_selectbox('form_license',$group->getLicense());
?>
</td>
</tr>
<?php
if ($group->getLicense() == GROUP_LICENSE_OTHER) {
?>
<tr>
<td><?php echo $Language->getText('admin','license_other'); ?>
</td>
<td>
<?php echo $group->getLicenseOther(); ?>
</td>
</tr>
<?php } ?> 
<tr>
<td>
<?php echo $Language->getText('admin','home_box'); ?>
</td>
<td>
<input type="text" name="form_box" value="<?php echo $group->getUnixBox(); ?>" />
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin','http_domain') ?>
</td>
<td>
<input size="40" type="text" name="form_domain" value="<?php echo $group->getDomain(); ?>" />
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_groupedit','registration_application'); ?>
</td>
<td>
<?php echo $group->getRegistrationPurpose(); ?>
</td>
</tr>
<?php
if ($group->usesSCM()) {
?>
<tr>
	<td><?php echo $Language->getText('admin','scm_box'); ?></td>
	<td><input size="40" type="text" name="form_scm_box" value="<?php echo $group->getSCMBox(); ?>"/></td>
</tr>
<?php
}
?>

</table>

<input type="hidden" name="group_id" value="<?php print $group_id; ?>" />

<br /><input type="submit" name="submit" value="<?php echo $Language->getText('admin_groupedit','update'); ?>" />
&nbsp;&nbsp;&nbsp; <input type="submit" name="resend" value="<?php echo $Language->getText('admin_groupedit','new_project_instruction_email'); ?>" />
</form></p>

<?php

echo show_grouphistory($group->getID());

site_admin_footer(array());

?>
