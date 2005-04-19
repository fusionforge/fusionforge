<?php
/**
 * Project Admin page to edit project information (like description,
 * active facilities, etc.)
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error($Language->getText('general','error'),
		$Language->getText('error','error_creating_group'));
} else if ($group->isError()) {
	exit_error($Language->getText('general','error'),
		$group->getErrorMessage());
}

// If this was a submission, make updates
if ($submit) {

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
		'',
		'',
		$use_ftp,
		$use_tracker,
		$use_frs,
		$use_stats
	);
	
	//100 $logo_image_id

	if (!$res) {
		$feedback .= $group->getErrorMessage();
	} else {
		$feedback .= $Language->getText('project_admin_editgroupinfo','group_updated');
	}
}

project_admin_header(array('title'=>$Language->getText('project_admin_editgroupinfo','title').'','group'=>$group->getID(),'pagename'=>'project_admin_editgroupinfo','sectionvals'=>array(group_getname($group_id))));

if ($submit) {
	$hookParams['group']=$group_id;
	plugin_hook("groupisactivecheckboxpost",$hookParams);
}

/* NOT ACTIVE YET

// Prepare images res to render select box in HTML template
$images_res = db_query("
	SELECT id,description FROM db_images
	WHERE group_id='$group_id'
	AND width<200
	AND height<200
");

*/

?>

<form action="<?php echo $PHP_SELF; ?>" method="post">

<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />

<p>
<?php echo $Language->getText('project_admin_editgroupinfo','group_name') ?>:<br />
<input type="text" name="form_group_name" value="<?php echo $group->getPublicName(); ?>" maxlength="40" />
</p>

<p>
<?php echo $Language->getText('project_admin_editgroupinfo','short_description') ?>:<br />
<textarea cols="80" rows="3" wrap="virtual" name="form_shortdesc">
<?php echo $group->getDescription(); ?>
</textarea>
</p>

<p>
<?php echo $Language->getText('project_admin_editgroupinfo','homepage_link') ?>:<br />
<tt>http://</tt><input type="text" name="form_homepage" size="40" value="<?php echo $group->getHomePage(); ?>" />
</p>

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

<h3><?php echo $Language->getText('project_admin_editgroupinfo','active_features') ?>:</h3>

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
if($sys_use_mail) {
?>
<tr>
<td>
<input type="checkbox" name="use_mail" value="1" <?php echo c($group->usesMail()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_mailing_lists') ?></strong>
</td>
</tr>
<?php
} 

if($sys_use_survey) {
?>
<tr>
<td>
<input type="checkbox" name="use_survey" value="1" <?php echo c($group->usesSurvey()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_surveys') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_forum) {
?>
<tr>
<td>
<input type="checkbox" name="use_forum" value="1" <?php echo c($group->usesForum()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_forums') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_pm) {
?>
<tr>
<td>
<input type="checkbox" name="use_pm" value="1" <?php echo c($group->usesPM()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_pm') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_scm) {
?>
<tr>
<td>
input type="checkbox" name="use_scm" value="1" <?php echo c($group->usesSCM()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_scm') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_news) {
?>
<tr>
<td>
<input type="checkbox" name="use_news" value="1" <?php echo c($group->usesNews()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_news') ?> </strong>
</td>
</tr>
<?php
}

if($sys_use_docman) {
?>
<tr>
<td>
<input type="checkbox" name="use_docman" value="1" <?php echo c($group->usesDocman()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_docman') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_ftp) {
?>
<tr>
<td>
<input type="checkbox" name="use_ftp" value="1" <?php echo c($group->usesFTP()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_ftp') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_tracker) {
?>
<tr>
<td>
<input type="checkbox" name="use_tracker" value="1" <?php echo c($group->usesTracker()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_tracker') ?></strong>
</td>
</tr>
<?php
}

if($sys_use_frs) {
?>
<tr>
<td>
<input type="checkbox" name="use_frs" value="1" <?php echo c($group->usesFRS()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_frs') ?></strong>
</td>
</tr>
<?php } ?>
<tr>
<td>
<input type="checkbox" name="use_stats" value="1" <?php echo c($group->usesStats()); ?> />
</td>
<td>
<strong><?php echo $Language->getText('project_admin_editgroupinfo','use_stats') ?></strong>
</td>
</tr>

<?php 
$hookParams['group']=$group_id;
plugin_hook("groupisactivecheckbox",$hookParams);
?>

</table>

<p>
<?php echo $Language->getText('project_admin_editgroupinfo','new_doc_info') ?>.<br />
<strong><?php echo $Language->getText('project_admin_editgroupinfo','new_doc') ?>:</strong><br />
<input type="text" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" size="25" maxlength="250" />
<?php echo $Language->getText('project_admin_editgroupinfo','send_on_all_updates') ?>
<input type="checkbox" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> />
</p>

<hr />

<p>
<input type="submit" name="submit" value="<?php echo $Language->getText('general','update') ?>" />
</p>
</form>

<?php

project_admin_footer(array());

?>
