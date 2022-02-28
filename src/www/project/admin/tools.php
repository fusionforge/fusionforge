<?php
/**
 * Project Admin page to edit tools information
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

global $HTML, $error_msg, $feedback;

$group_id = getIntFromRequest('group_id');
session_require_perm('project_admin', $group_id);
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error(_('Error creating group'), 'admin');
} elseif ($group->isError()) {
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
	$use_activity = getStringFromRequest('use_activity');
	$tags = getStringFromRequest('form_tags');
	$new_doc_address = getStringFromRequest('new_doc_address');
	$send_all_docs = getStringFromRequest('send_all_docs');
	$new_frs_address = getStringFromRequest('new_frs_address');
	$send_all_frs = getStringFromRequest('send_all_frs');

	$res = $group->update(
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
		$use_activity,
		$group->isPublic(),
		$new_frs_address,
		$send_all_frs
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

project_admin_header(array('title'=>sprintf(_('Tools for %s'), $group->getPublicName()),
						   'group'=>$group->getID()));

echo '<table class="fullwidth">';
echo '<tr class="top">';
echo '<td class="halfwidth">';

echo $HTML->boxTop(_('Active Tools'));
echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
?>

<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />
<input type="hidden" name="form_group_name" value="<?php echo $group->getPublicName(); ?>" />
<input type="hidden" name="form_shortdesc" value="<?php echo $group->getDescription(); ?>" />
<input type="hidden" name="form_tags" value="<?php echo $group->getTags(); ?>" />
<input type="hidden" name="form_homepage" value="<?php echo $group->getHomePage(); ?>" />

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
if(forge_get_config('use_activity')) {
?>
<tr>
<td>
<input type="checkbox" id="use_activity" name="use_activity" value="1" <?php echo c($group->usesActivity()); ?> />
</td>
<td>
<label for="use_activity">
<strong><?php echo _('Use Project Activity') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_forum')) {
?>
<tr>
<td>
<input type="checkbox" id="use_forum" name="use_forum" value="1" <?php echo c($group->usesForum()); ?> />
</td>
<td>
<label for="use_forum">
<strong><?php echo _('Use Forums') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_tracker')) {
?>
<tr>
<td>
<input type="checkbox" id="use_tracker" name="use_tracker" value="1" <?php echo c($group->usesTracker()); ?> />
</td>
<td>
<label for="use_tracker">
<strong><?php echo _('Use Trackers') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_mail')) {
?>
<tr>
<td>
<input type="checkbox" id="use_mail" name="use_mail" value="1" <?php echo c($group->usesMail()); ?> />
</td>
<td>
<label for="use_mail">
<strong><?php echo _('Use Mailing Lists') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_pm')) {
?>
<tr>
<td>
<input type="checkbox" id="use_pm" name="use_pm" value="1" <?php echo c($group->usesPM()); ?> />
</td>
<td>
<label for="use_pm">
<strong><?php echo _('Use Tasks') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_docman')) {
?>
<tr>
<td>
<input type="checkbox" id="use_docman" name="use_docman" value="1" <?php echo c($group->usesDocman()); ?> />
</td>
<td>
<label for="use_docman">
<strong><?php echo _('Use Documents') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_survey')) {
?>
<tr>
<td>
<input type="checkbox" id="use_survey" name="use_survey" value="1" <?php echo c($group->usesSurvey()); ?> />
</td>
<td>
<label for="use_survey">
<strong><?php echo _('Use Surveys') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_news')) {
?>
<tr>
<td>
<input type="checkbox" id="use_news" name="use_news" value="1" <?php echo c($group->usesNews()); ?> />
</td>
<td>
<label for="use_news">
<strong><?php echo _('Use News') ?> </strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_scm')) {
?>
<tr>
<td>
<input type="checkbox" id="use_scm" name="use_scm" value="1" <?php echo c($group->usesSCM()); ?> />
</td>
<td>
<label for="use_scm">
<strong><?php echo _('Use Source Code') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_frs')) {
?>
<tr>
<td>
<input type="checkbox" id="use_frs" name="use_frs" value="1" <?php echo c($group->usesFRS()); ?> />
</td>
<td>
<label for="use_frs">
<strong><?php echo _('Use File Release System') ?></strong>
</label>
</td>
</tr>
<?php
}

if(forge_get_config('use_ftp')) {
?>
<tr>
<td>
<input type="checkbox" id="use_ftp" name="use_ftp" value="1" <?php echo c($group->usesFTP()); ?> />
</td>
<td>
<label for="use_ftp">
<strong><?php echo _('Use FTP') ?></strong>
</label>
</td>
</tr>
<?php } ?>
<tr>
<td>
<input type="checkbox" id="use_stats" name="use_stats" value="1" <?php echo c($group->usesStats()); ?> />
</td>
<td>
<label for="use_stats">
<strong><?php echo _('Use Statistics') ?></strong>
</label>
</td>
</tr>

<?php
$hookParams['group']=$group_id;
plugin_hook("groupisactivecheckbox",$hookParams);
?>

</table>

<input type="hidden" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" />
<input type="hidden" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> />
<input type="hidden" name="new_frs_address" value="<?php echo $group->getFRSEmailAddress(); ?>" />
<input type="hidden" name="send_all_frs" value="1" <?php echo c($group->frsEmailAll()); ?> />

<input type="submit" name="submit" value="<?php echo _('Update') ?>" />
<?php echo $HTML->closeForm(); ?>

<br />

<?php
echo $HTML->boxBottom();
echo '</td>';

echo '<td>';
echo $HTML->boxTop(_('Tool Admin'));

if($group->usesForum()) {
	echo html_e('p', array(), util_make_link('/forum/admin/?group_id='.$group->getID(), _('Forums Administration')), false);
}
if($group->usesTracker()) {
	echo html_e('p', array(), util_make_link('/tracker/admin/?group_id='.$group->getID(), _('Trackers Administration')), false);
}
if($group->usesMail()) {
	echo html_e('p', array(), util_make_link('/mail/admin/?group_id='.$group->getID(), _('Mailing Lists Admin')), false);
}
if($group->usesPM()) {
	echo html_e('p', array(), util_make_link('/pm/admin/?group_id='.$group->getID(), _('Tasks Administration')), false);
}
if($group->usesDocman()) {
	echo html_e('p', array(), util_make_link('/docman/?group_id='.$group->getID().'&view=admin', _('Documents Admin')), false);
}
if($group->usesSurvey()) {
	echo html_e('p', array(), util_make_link('/survey/admin/?group_id='.$group->getID(), _('Survey Admin')), false);
}
if($group->usesNews()) {
	echo html_e('p', array(), util_make_link('/news/admin/?group_id='.$group->getID(), _('News Admin')), false);
}
if($group->usesSCM()) {
	echo html_e('p', array(), util_make_link('/scm/admin/?group_id='.$group->getID(), _('Source Code Admin')), false);
}
if($group->usesFRS()) {
	echo html_e('p', array(), util_make_link('/frs/?group_id='.$group->getID().'&view=admin', _('File Release System Admin')), false);
}

$hook_params = array();
$hook_params['group_id'] = $group_id;
plugin_hook("project_admin_plugins", $hook_params);

echo $HTML->boxBottom();

echo '</td>';
echo '</tr>';
echo '</table>';

project_admin_footer();
