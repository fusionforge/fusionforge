<?php
/**
 * Site Admin group properties editing page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

session_require_global_perm ('forge_admin');

$group_id = getIntFromRequest('group_id');
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
}

// This function performs very update
function do_update(&$group, $is_public, $is_template, $status, $group_type, $unix_box, $http_domain, $scm_box='') {
	global $feedback;
    global $error_msg;

	db_begin();

	if (!$group->setStatus(session_get_user(), $status)) {
		$error_msg .= $group->getErrorMessage();
		db_rollback();
		return false;
	}

	if (!$group->updateAdmin(session_get_user(), $is_public, $group_type, $unix_box, $http_domain)) {
		$error_msg .= $group->getErrorMessage();
		db_rollback();
		return false;
	}

	if (!$group->setAsTemplate($is_template)) {
		$error_msg .= $group->getErrorMessage();
		db_rollback();
		return false;
	}

	if($group->usesSCM() && !$group->setSCMBox($scm_box)) {
		$error_msg .= $group->getErrorMessage();
		db_rollback();
		return false;
	}
	db_commit();

	$feedback .= _('Updated');

	return true;
}


if (getStringFromRequest('submit')) {
	$form_public = getStringFromRequest('form_public');
	$form_template = getStringFromRequest('form_template');
	$form_status = getStringFromRequest('form_status');
	$form_box = getStringFromRequest('form_box');
	$form_domain = getStringFromRequest('form_domain');
	$form_scm_box = getStringFromRequest('form_scm_box');

	do_update($group, $form_public, $form_template, $form_status, 1, $form_box, $form_domain, $form_scm_box);

} else if (getStringFromRequest('resend')) {

	$group->sendApprovalEmail();
	$feedback .= _('Instruction email sent');

}

$title = _('Site Admin: Project Info for ') . $group->getPublicName();
site_admin_header(array('title'=>$title));
?>

<h2><?php echo util_make_link("/project/admin/?group_id=$group_id", _('[Project Admin]')); ?></h2>
<h2><?php echo util_make_link("/admin/groupdelete.php?group_id=$group_id", _('Permanently Delete Project')); ?></h2>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<table>
<tr>
<td>
<?php echo _('Status') ?>
</td>
<td>
<?php	// PLEASE DONT TRANSLATE THIS, THIS IS DATABASE INFO THAT CANT BE DIFFERENT AMONG LANGUAGES
$status = $group->getStatus();
if($status == 'P') {
	// we cannot update the status of a pending project
	echo '<input type="hidden" name="form_status" value="P" />';
	echo _('Pending (P)').'&nbsp; &nbsp; ';
} else {
	echo html_build_select_box_from_arrays(
		array(
			'I',
			'A',
			'P',
			'H'
		),
		array(
			_('Incomplete (I)'),
			_('Active (A)'),
			_('Pending (P)'),
			_('Holding (H)')
		),
		'form_status', $status, false
	);
} ?>
</td>
</tr>
<tr>
<td>
<?php echo _('Public?') ?>:
</td>
<td>
<?php 	// PLEASE DONT TRANSLATE THIS, THIS IS DATABASE INFO THAT CANT BE DIFFERENT AMONG LANGUAGES
	echo html_build_select_box_from_arrays(
	array(
		'0',
		'1'
	),
	array(
		_('No'),
		_('Yes')
),
	'form_public', $group->isPublic(), false
); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('Template?') ?>:
</td>
<td>
<?php
	echo html_build_select_box_from_arrays(
	array(
		'0',
		'1'
	),
	array(
		_('No'),
		_('Yes')
),
	'form_template', $group->isTemplate(), false
); ?>
</td>
</tr>

<tr>
<td>
<?php echo _('Unix Project Name:'); ?>
</td>
<td>
<?php echo $group->getUnixName(); ?>
</td>
</tr>

<?php

	if (forge_get_config('use_shell')) {
?> 
<tr>
<td>
<?php echo _('Home Box:'); ?>
</td>
<td>
<input type="text" name="form_box" value="<?php echo $group->getUnixBox(); ?>" />
</td>
</tr>
<?php	} //end sus_use_shell condition ?>

<tr>
<td>
<?php echo _('HTTP Domain:') ?>
</td>
<td>
<input size="40" type="text" name="form_domain" value="<?php echo $group->getDomain(); ?>" />
</td>
</tr>

<tr>
<td>
<?php echo _('Registration Application:'); ?>
</td>
<td>
<?php echo $group->getRegistrationPurpose(); ?>
</td>
</tr>
<?php
if ($group->usesSCM()) {
?>
<tr>
	<td><?php echo _('SCM Box:'); ?></td>
	<td><input size="40" type="text" name="form_scm_box" value="<?php echo $group->getSCMBox(); ?>"/></td>
</tr>
<?php
}
?>

</table>

<input type="hidden" name="group_id" value="<?php print $group_id; ?>" />

<br /><input type="submit" name="submit" value="<?php echo _('Update'); ?>" />
&nbsp;&nbsp;&nbsp; <input type="submit" name="resend" value="<?php echo _('Resend New Project Instruction Email'); ?>" />
</form>

<?php

echo show_grouphistory($group->getID());

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
