<?php
/**
 * Project Admin Main Page
 *
 * This page contains administrative information for the project as well
 * as allows to manage it. This page should be accessible to all project
 * members, but only admins may perform most functions.
 *
 * Copyright 2004 GForge, LLC - Tim Perdue
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/role_utils.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$group_id = getIntFromRequest('group_id');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));

session_require_perm ('project_admin', $group_id) ;

// get current information
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
}

$group->clearError();

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
	$addTags = getArrayFromRequest('addTags');
	$is_public = getIntFromRequest('is_public');
	$new_doc_address = getStringFromRequest('new_doc_address');
	$send_all_docs = getStringFromRequest('send_all_docs');
	
	if (trim($tags) != "") {
		$tags .= ",";
	}
	$tags .= implode(",", $addTags);

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
		$is_public
	);
	
	//100 $logo_image_id

	if (!$res) {
		$error_msg .= $group->getErrorMessage();
	} else {
		$feedback .= _('Project information updated');
	}
}

$adminheadertitle=sprintf(_('Project Admin: %1$s'), $group->getPublicName() );
project_admin_header(array('title'=>$adminheadertitle, 'group'=>$group->getID()));

?>

<table class="my-layout-table">
	<tr>
		<td>

<?php echo $HTML->boxTop(_('Misc. Project Information'));

if (forge_get_config('use_shell')) {
?> 
<p><?php echo _('Group shell (SSH) server:&nbsp;') ?><strong><?php echo $group->getUnixName().'.'.forge_get_config('web_host'); ?></strong></p>
<p><?php echo _('Group directory on shell server:&nbsp;') ?><br/><strong><?php echo account_group_homedir($group->getUnixName()); ?></strong></p>
<p><?php echo _('Project WWW directory on shell server:&nbsp;') ?><br /><strong><?php echo account_group_homedir($group->getUnixName()).'/htdocs'; ?></strong></p>
<?php
	} //end of use_shell condition
?> 

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />

<h2><?php echo _('Descriptive Project Name'); ?></h2>
<p>
<input type="text" name="form_group_name" value="<?php echo $group->getPublicName(); ?>" size="40" maxlength="40" />
</p>

<h2><?php echo _('Short Description'); ?></h2>
<p>
<?php echo _('Maximum 255 characters, HTML will be stripped from this description'); ?>
</p>
<p>
<textarea cols="80" rows="3" name="form_shortdesc">
<?php echo $group->getDescription(); ?>
</textarea>
</p>

<?php if (forge_get_config('use_project_tags')) { ?>
<p>
<h2><?php echo _('Project tags'); ?></h2>
<?php echo _('Add tags (use comma as separator): ') ?><br />
<input type="text" name="form_tags" size="100" value="<?php echo $group->getTags(); ?>" />
</p><br />
<?php echo _('Or pick a tag from those used by other projects: ') ?><br />
<?php 
	 
	 echo '<table width="100%"><thead><tr>';
echo '<th>'._('Tags').'</th>';
echo '<th>'._('Projects').'</th>';
echo '</tr></thead><tbody>';

$infos = getAllProjectTags();

$unix_name = $group->getUnixName();
foreach ($infos as $tag => $plist) {
	$disabled = '';
	$links = array();
	foreach($plist as $project) {
		$links[] = util_make_link('/projects/'.$project['unix_group_name'].'/',$project['unix_group_name']);
		if ($project['group_id'] == $group_id) {
			$disabled = ' disabled="disabled"';
		}
	}
	
	echo '<tr>';
	echo '<td><input type="checkbox" name="addTags[]" value="'.$tag.'"'.$disabled.' /> ';
	if ($disabled) {
		echo '<s>'.$tag.'</s>';
	} else {
		echo $tag;
	}
	echo '</td>';
	echo '<td>'.implode(' ', $links).'</td>' ;
	echo '</tr>' ;
}
echo '</table>' ;



} ?>

<h2><?php echo _('Trove Categorization'); ?></h2>
<p>
<a href="/project/admin/group_trove.php?group_id=<?php echo $group->getID(); ?>">[<?php echo _('Edit Trove'); ?>]</a>
</p>

<h2><?php echo _('Homepage Link') ?></h2>
<p>
<input type="text" name="form_homepage" size="100" value="<?php echo $group->getHomePage(); ?>" />
</p>

<?php
	if ($sys_use_private_project) {
		echo '<p>' ;
		echo _('Visibility: ');
		echo html_build_select_box_from_arrays(
               array('0','1'),
               array(  _('Private'), _('Public') ),
               'is_public', $group->isPublic(), false);
	} else {
		echo "<input type=hidden name=\"is_public\" value=\"1\">";
	}
?>

<?php
// This function is used to render checkboxes below
function c($v) {
	if ($v) {
		return 'checked="checked"';
	} else {
		return '';
	}
}
?>

<?php
if(forge_get_config('use_mail')) {
?>
<input type="hidden" name="use_mail" value="<?php echo ($group->usesMail() ? '1' : '0'); ?>" />
<?php
} 

if(forge_get_config('use_survey')) {
?>
<input type="hidden" name="use_survey" value="<?php echo ($group->usesSurvey() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_forum')) {
?>
<input type="hidden" name="use_forum" value="<?php echo ($group->usesForum() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_pm')) {
?>
<input type="hidden" name="use_pm" value="<?php echo ($group->usesPM() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_scm')) {
?>
<input type="hidden" name="use_scm" value="<?php echo ($group->usesSCM() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_news')) {
?>
<input type="hidden" name="use_news" value="<?php echo ($group->usesNews() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_docman')) {
?>
<input type="hidden" name="use_docman" value="<?php echo ($group->usesDocman() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_ftp')) {
?>
<input type="hidden" name="use_ftp" value="<?php echo ($group->usesFTP() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_tracker')) {
?>
<input type="hidden" name="use_tracker" value="<?php echo ($group->usesTracker() ? '1' : '0'); ?>" />
<?php
}

if(forge_get_config('use_frs')) {
?>
<input type="hidden" name="use_frs" value="<?php echo ($group->usesFRS() ? '1' : '0'); ?>" />
<?php } ?>

<input type="hidden" name="use_stats" value="<?php echo ($group->usesStats() ? '1' : '0'); ?>" />

<p>
<?php echo _('If you wish, you can provide default email addresses to which new submissions will be sent') ?>.<br />
<strong><?php echo _('New Document Submissions') ?>:</strong><br />
<input type="text" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" size="40" maxlength="250" />
<?php echo _('(send on all updates)') ?>
<input type="checkbox" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> />
</p>

<p>
<input type="submit" name="submit" value="<?php echo _('Update') ?>" />
</p>

</form>

<?php
plugin_hook('admin_project_link', array($group_id, 'project'));

echo $HTML->boxBottom();?>

		</td>
	</tr>
</table>

<?php

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
