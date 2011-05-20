<?php
/**
 * Project Admin page to edit tools information
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org/
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

$group_id = getIntFromRequest('group_id');
session_require_perm('project_admin', $group_id);
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error(_('Error creating group object'), 'admin');
} else if ($group->isError()) {
	exit_error($group->getErrorMessage(), 'admin');
}

// If this was a submission, make updates
if (getStringFromRequest('submit')) {
	$form_group_name = getStringFromRequest('form_group_name');
	$form_shortdesc = getStringFromRequest('form_shortdesc');
	$form_homepage = getStringFromRequest('form_homepage');
	$logo_image_id = getIntFromRequest('logo_image_id');
	$use_mail = getStringFromRequest('use_mail');
	$use_survey = getStringFromRequest('use_survey');
	$use_forum = getStringFromRequest('use_forum');
	$use_pm = getStringFromRequest('use_pm');
	$use_scm = getStringFromRequest('use_scm');
	$use_news = getStringFromRequest('use_news');
	$use_docman = getStringFromRequest('use_docman');
	$use_ftp = getStringFromRequest('use_ftp');
	$use_tracker = getStringFromRequest('use_tracker');
	$use_frs = getStringFromRequest('use_frs');
	$use_stats = getStringFromRequest('use_stats');
	$tags = getStringFromRequest('form_tags');
	$new_doc_address = getStringFromRequest('new_doc_address');
	$send_all_docs = getStringFromRequest('send_all_docs');

	$res = $group->update(
		session_get_user(),
		$form_group_name,
		$form_homepage,
		$form_shortdesc,
		$use_mail,
		$use_survey,
		$use_forum,
		$use_pm,
		1,
		$use_scm,
		$use_news,
		$use_docman,
		$new_doc_address,
		$send_all_docs,
		100,
		$use_ftp,
		$use_tracker,
		$use_frs,
		$use_stats,
		$tags,
		$group->isPublic()
	);

	if (!$res) {
		$error_msg = $group->getErrorMessage();
		$group->clearError();
	} else {
		// This is done so plugins can enable/disable themselves from the project
		$hookParams['group'] = $group_id;
		if (!plugin_hook("groupisactivecheckboxpost", $hookParams)) {
			if ($group->isError()) {
				$error_msg = $group->getErrorMessage();
				$group->clearError();
			} else {
				$error_msg = _('At least one plugin does not initialize correctly');
			}
		}
	}

	if (empty($error_msg)) {
		$feedback = _('Project information updated');
	}

}

project_admin_header(array('title'=>_('Tools'),'group'=>$group->getID()));

echo '<table width="100%">';
echo '<tr valign="top">';
echo '<td width="50%">';

echo $HTML->boxTop(_('Active Tools').'');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />
<input type="hidden" name="form_group_name" value="<?php echo $group->getPublicName(); ?>" />
<input type="hidden" name="form_shortdesc" value="<?php echo $group->getDescription(); ?>" />
<input type="hidden" name="form_tags" size="100" value="<?php echo $group->getTags(); ?>" />
<input type="hidden" name="form_homepage" size="100" value="<?php echo $group->getHomePage(); ?>" />

<?php

// This function is used to render checkboxes below
function c($v) {
	if ($v) {
		return 'checked="checked"';
	} else {
		return '';
	}
}

/*
	Show the options that this project is using
*/

?>

<table>
<?php
if(forge_get_config('use_forum')) {
?>
<tr>
<td>
<input type="checkbox" name="use_forum" value="1" <?php echo c($group->usesForum()); ?> />
</td>
<td>
<strong><?php echo _('Use Forums') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_tracker')) {
?>
<tr>
<td>
<input type="checkbox" name="use_tracker" value="1" <?php echo c($group->usesTracker()); ?> />
</td>
<td>
<strong><?php echo _('Use Trackers') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_mail')) {
?>
<tr>
<td>
<input type="checkbox" name="use_mail" value="1" <?php echo c($group->usesMail()); ?> />
</td>
<td>
<strong><?php echo _('Use Mailing Lists') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_pm')) {
?>
<tr>
<td>
<input type="checkbox" name="use_pm" value="1" <?php echo c($group->usesPM()); ?> />
</td>
<td>
<strong><?php echo _('Use Tasks') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_docman')) {
?>
<tr>
<td>
<input type="checkbox" name="use_docman" value="1" <?php echo c($group->usesDocman()); ?> />
</td>
<td>
<strong><?php echo _('Use Documents') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_survey')) {
?>
<tr>
<td>
<input type="checkbox" name="use_survey" value="1" <?php echo c($group->usesSurvey()); ?> />
</td>
<td>
<strong><?php echo _('Use Surveys') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_news')) {
?>
<tr>
<td>
<input type="checkbox" name="use_news" value="1" <?php echo c($group->usesNews()); ?> />
</td>
<td>
<strong><?php echo _('Use News') ?> </strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_scm')) {
?>
<tr>
<td>
<input type="checkbox" name="use_scm" value="1" <?php echo c($group->usesSCM()); ?> />
</td>
<td>
<strong><?php echo _('Use Source Code') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_frs')) {
?>
<tr>
<td>
<input type="checkbox" name="use_frs" value="1" <?php echo c($group->usesFRS()); ?> />
</td>
<td>
<strong><?php echo _('Use File Release System') ?></strong>
</td>
</tr>
<?php
}

if(forge_get_config('use_ftp')) {
?>
<tr>
<td>
<input type="checkbox" name="use_ftp" value="1" <?php echo c($group->usesFTP()); ?> />
</td>
<td>
<strong><?php echo _('Use FTP') ?></strong>
</td>
</tr>
<?php } ?>
<tr>
<td>
<input type="checkbox" name="use_stats" value="1" <?php echo c($group->usesStats()); ?> />
</td>
<td>
<strong><?php echo _('Use Statistics') ?></strong>
</td>
</tr>

<?php
$hookParams['group']=$group_id;
plugin_hook("groupisactivecheckbox",$hookParams);
?>

</table>

<input type="hidden" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" />
<input type="hidden" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> />

<input type="submit" name="submit" value="<?php echo _('Update') ?>" />
</form>

<br />

<?php
echo $HTML->boxBottom();
echo '</td>';

echo '<td>';
echo $HTML->boxTop(_('Tool Admin').'');

if($group->usesForum()) { ?>
	<p><a href="/forum/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Forums Admin') ?></a></p>
<?php }
if($group->usesTracker()) { ?>
	<p><a href="/tracker/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Trackers Admin') ?></a></p>
<?php }
if($group->usesMail()) { ?>
	<p><a href="/mail/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Mailing Lists Admin') ?></a></p>
<?php }
if($group->usesPM()) { ?>
	<p><a href="/pm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Tasks Admin') ?></a></p>
<?php }
if($group->usesDocman()) { ?>
	<p><a href="/docman/?group_id=<?php echo $group->getID(); ?>&amp;view=admin"><?php echo _('Documents Admin') ?></a></p>
<?php }
if($group->usesSurvey()) { ?>
	<p><a href="/survey/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Survey Admin') ?></a></p>
<?php }
if($group->usesNews()) { ?>
	<p><a href="/news/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('News Admin') ?></a></p>
<?php }
if($group->usesSCM()) { ?>
	<p><a href="/scm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Source Code Admin') ?></a></p>
<?php }
if($group->usesFRS()) { ?>
	<p><a href="/frs/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('File Release System Admin') ?></a></p>
<?php }

$hook_params = array();
$hook_params['group_id'] = $group_id;
plugin_hook("project_admin_plugins", $hook_params);

echo $HTML->boxBottom();

echo '</td>';
echo '</tr>';
echo '</table>';

project_admin_footer(array());

?>
