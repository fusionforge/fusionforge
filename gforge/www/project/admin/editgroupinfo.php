<?php
/**
  *
  * Project Admin page to edit project information (like description,
  * active facilities, etc.)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/vars.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group =& group_get_object($group_id);
exit_assert_object($group, 'Group');

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
		$use_cvs,
		$use_news,
		$use_docman,
		$new_doc_address,
		$send_all_docs,
		100,
		$enable_pserver,
		$enable_anoncvs,
		$use_ftp,
		$use_tracker,
		$use_frs,
		$use_stats
	);
	//100 $logo_image_id

	if (!res) {
		$feedback .= $group->getErrorMessage();
	} else {
		$feedback .= 'Group information updated';
	}
}

project_admin_header(array('title'=>'Edit Group Info','group'=>$group->getID(),'pagename'=>'project_admin_editgroupinfo','sectionvals'=>array(group_getname($group_id))));

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

<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group->getID(); ?>" />

<p>Descriptive Group Name:
<br /><input type="text" name="form_group_name" value="<?php echo $group->getPublicName(); ?>"></p>

<p>Short Description (255 Character Max, HTML will be stripped from this description):
<br /><textarea cols="80" rows="3" wrap="virtual" name="form_shortdesc">
<?php echo $group->getDescription(); ?></textarea></p>

<p>Homepage Link:
<br /><tt>http://</tt><input type="text" name="form_homepage" size="40" value="<?php echo $group->getHomePage(); ?>" />
<br />


<?php
/* NOT ACTIVE YET

<br />Logo Image:
<?php echo html_build_select_box($images_res, 'logo_image_id', $group->getLogoImageID(), true); ?>
 (first, upload via <a href="editimages.php?group_id='.$group_id.'">Multimedia Manager</a>, 
 dimensions 200x200 max)<br />
*/

?>

<hr />

<h3>Active Features:</h3>
<p>


<?php

// This function is used to render checkboxes below
function c($v) {
	if ($v) {
		return 'CHECKED';
	} else {
		return '';
	}
}

/*
	Show the options that this project is using
*/

?>

<table>
<tr>
<td>
 <input type="CHECKBOX" name="use_mail" value="1" <?php echo c($group->usesMail()); ?> ><br />
</td>
<td>
<strong>Use Mailing Lists</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_survey" value="1" <?php echo c($group->usesSurvey()); ?> ><br />
</td>
<td>
<strong>Use Surveys</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_forum" value="1" <?php echo c($group->usesForum()); ?>  ><br />
</td>
<td>
<strong>Use Forums</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_pm" value="1" <?php echo c($group->usesPM()); ?> ><br />
</td>
<td>
<strong>Use Project/Task Manager</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_cvs" value="1" <?php echo c($group->usesCVS()); ?> ><br />
</td>
<td>
<strong>Use CVS</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="enable_pserver" value="1" <?php echo c($group->enablePserver()); ?> ><br />
</td>
<td>
<strong>Enable pserver</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="enable_anoncvs" value="1" <?php echo c($group->enableAnonCVS()); ?> ><br />
</td>
<td>
<strong>Enable anonymous CVS</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_news" value="1" <?php echo c($group->usesNews()); ?> ><br />
</td>
<td>
<strong>Use News</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_docman" value="1" <?php echo c($group->usesDocman()); ?> >
</td>
<td>
<strong>Use Doc Mgr</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_ftp" value="1" <?php echo c($group->usesFTP()); ?> >
</td>
<td>
<strong>Use FTP</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_tracker" value="1" <?php echo c($group->usesTracker()); ?> >
</td>
<td>
<strong>Use Tracker</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_frs" value="1" <?php echo c($group->usesFRS()); ?> >
</td>
<td>
<strong>Use File Release System</strong>
</td>
</tr>

<tr>
<td>
 <input type="CHECKBOX" name="use_stats" value="1" <?php echo c($group->usesStats()); ?> >
</td>
<td>
<strong>Use Statistics</strong>
</td>
</tr>

</table>


<p>
If you wish, you can provide default email addresses to which new submissions will be sent.<br />
<strong>New Document Submissions:</strong><br /><input type="TEXT" name="new_doc_address" value="<?php echo $group->getDocEmailAddress(); ?>" SIZE="25" MAXLENGTH="250">
(send on all updates)
<input type="CHECKBOX" name="send_all_docs" value="1" <?php echo c($group->docEmailAll()); ?> ><br />

<hr />
<p><input type="submit" name="submit" value="Update">
</form>

<?php

project_admin_footer(array());

?>
