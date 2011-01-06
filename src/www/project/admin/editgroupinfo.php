<?php
/**
 * FusionForge project admin page
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge, LLC
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

$group_id = getIntFromRequest('group_id');
session_require_perm ('project_admin', $group_id) ;

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} else if ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
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
	$is_public = getIntFromRequest('is_public');
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
		$is_public
	);
	
	//100 $logo_image_id

	if (!$res) {
		$error_msg = $group->getErrorMessage();
	} else {
		$feedback = _('Group information updated');
	}

	// This is done so plugins can enable/disable themselves from the project
	$hookParams['group']=$group_id;
	plugin_hook("groupisactivecheckboxpost",$hookParams);
}

project_admin_header(array('title'=>_('Edit Group Info').'','group'=>$group->getID()));

/* NOT ACTIVE YET

// Prepare images res to render select box in HTML template
$images_res = db_query_params ('
	SELECT id,description FROM db_images
	WHERE group_id=$1
	AND width<200
	AND height<200
',
			array($group_id));

*/

?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />

<p>
<?php echo _('Descriptive Group Name') ?>:<br />
<input type="text" name="form_group_name" value="<?php echo $group->getPublicName(); ?>" maxlength="40" />
</p>

<p>
<?php echo _('Short Description (255 Character Max, HTML will be stripped from this description)') ?>:<br />
<textarea cols="80" rows="3" name="form_shortdesc">
<?php echo $group->getDescription(); ?>
</textarea>
</p>

<?php if (forge_get_config('use_project_tags')) { ?>
<p>
<?php echo _('Tags') ?>:<br />
<input type="text" name="form_tags" size="100" value="<?php echo $group->getTags(); ?>" />
</p>
<?php } ?>

<p>
<?php echo _('Homepage Link') ?>:<br />
<tt>http://</tt><input type="text" name="form_homepage" size="40" value="<?php echo $group->getHomePage(); ?>" />
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
/* NOT ACTIVE YET
<p>
Logo Image:
<?php echo html_build_select_box($images_res, 'logo_image_id', $group->getLogoImageID(), true); ?>
 (first, upload via <a href="editimages.php?group_id='.$group_id.'">Multimedia Manager</a>, 
 dimensions 200x200 max)
</p>
*/
?>

<hr />

<h3><?php echo _('Active Features') ?>:</h3>

<?php

// This function is used to render checkboxes below
function c($v) {
	if ($v) {
		return 'checked';
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

if(forge_get_config('use_pm')) {
?>
<tr>
<td>
<input type="checkbox" name="use_pm" value="1" <?php echo c($group->usesPM()); ?> />
</td>
<td>
<strong><?php echo _('Use Project/Task Manager') ?></strong>
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
<strong><?php echo _('Use SCM') ?></strong>
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

if(forge_get_config('use_docman')) {
?>
<tr>
<td>
<input type="checkbox" name="use_docman" value="1" <?php echo c($group->usesDocman()); ?> />
</td>
<td>
<strong><?php echo _('Use Doc Mgr') ?></strong>
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
<?php
}

if(forge_get_config('use_tracker')) {
?>
<tr>
<td>
<input type="checkbox" name="use_tracker" value="1" <?php echo c($group->usesTracker()); ?> />
</td>
<td>
<strong><?php echo _('Use Tracker') ?></strong>
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
<?php
	if (forge_get_config('use_docman')) {
?>
<p>
<?php echo _('If you wish, you can provide default email addresses to which new submissions will be sent') ?>.<br />
<strong><?php echo _('New Document Submissions') ?>:</strong><br />
<input type="text" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" size="25" maxlength="250" />
<?php echo _('(send on all updates)') ?>
<input type="checkbox" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> />
</p>
<?php
	}
?>
<hr />

<p>
<input type="submit" name="submit" value="<?php echo _('Update') ?>" />
</p>
</form>

<?php

project_admin_footer(array());

?>
