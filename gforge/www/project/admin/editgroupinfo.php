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
		$use_pm_depend,
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

<P>
<FORM action="<?php echo $PHP_SELF; ?>" method="POST">
<INPUT type="hidden" name="group_id" value="<?php echo $group->getID(); ?>">

<P>Descriptive Group Name:
<BR><INPUT type="text" name="form_group_name" value="<?php echo $group->getPublicName(); ?>">

<P>Short Description (255 Character Max, HTML will be stripped from this description):
<BR><TEXTAREA cols=80 rows=3 wrap="virtual" name="form_shortdesc">
<?php echo $group->getDescription(); ?></TEXTAREA>

<P>Homepage Link:
<br><tt>http://</tt><INPUT type="text" name="form_homepage" size="40" value="<?php echo $group->getHomePage(); ?>">
<br>


<?php
/* NOT ACTIVE YET

<br>Logo Image:
<?php echo html_build_select_box($images_res, 'logo_image_id', $group->getLogoImageID(), true); ?>
 (first, upload via <a href="editimages.php?group_id='.$group_id.'">Multimedia Manager</a>, 
 dimensions 200x200 max)<br>
*/

?>

<HR>

<H3>Active Features:</H3>
<P>


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
 <INPUT TYPE="CHECKBOX" NAME="use_mail" VALUE="1" <?php echo c($group->usesMail()); ?> ><BR>
</td>
<td>
<B>Use Mailing Lists</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_survey" VALUE="1" <?php echo c($group->usesSurvey()); ?> ><BR>
</td>
<td>
<B>Use Surveys</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_forum" VALUE="1" <?php echo c($group->usesForum()); ?>  ><BR>
</td>
<td>
<B>Use Forums</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_pm" VALUE="1" <?php echo c($group->usesPM()); ?> ><BR>
</td>
<td>
<B>Use Project/Task Manager</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_pm_depend" VALUE="1" <?php echo c($group->usesPMDependencies()); ?> ><BR>
</td>
<td>
<B>Use Task Dependency List</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_cvs" VALUE="1" <?php echo c($group->usesCVS()); ?> ><BR>
</td>
<td>
<B>Use CVS</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="enable_pserver" VALUE="1" <?php echo c($group->enablePserver()); ?> ><BR>
</td>
<td>
<B>Enable pserver</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="enable_anoncvs" VALUE="1" <?php echo c($group->enableAnonCVS()); ?> ><BR>
</td>
<td>
<B>Enable anonymous CVS</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_news" VALUE="1" <?php echo c($group->usesNews()); ?> ><BR>
</td>
<td>
<B>Use News</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_docman" VALUE="1" <?php echo c($group->usesDocman()); ?> >
</td>
<td>
<B>Use Doc Mgr</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_ftp" VALUE="1" <?php echo c($group->usesFTP()); ?> >
</td>
<td>
<B>Use FTP</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_tracker" VALUE="1" <?php echo c($group->usesTracker()); ?> >
</td>
<td>
<B>Use Tracker</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_frs" VALUE="1" <?php echo c($group->usesFRS()); ?> >
</td>
<td>
<B>Use File Release System</B>
</td>
</tr>

<tr>
<td>
 <INPUT TYPE="CHECKBOX" NAME="use_stats" VALUE="1" <?php echo c($group->usesStats()); ?> >
</td>
<td>
<B>Use Statistics</B>
</td>
</tr>

</table>


<P>
If you wish, you can provide default email addresses to which new submissions will be sent.<BR>
<B>New Document Submissions:</B><BR><INPUT TYPE="TEXT" NAME="new_doc_address" VALUE="<?php echo $group->getDocEmailAddress(); ?>" SIZE="25" MAXLENGTH="250">
(send on all updates)
<INPUT TYPE="CHECKBOX" NAME="send_all_docs" VALUE="1" <?php echo c($group->docEmailAll()); ?> ><BR>

<HR>
<P><INPUT type="submit" name="submit" value="Update">
</FORM>

<?php

project_admin_footer(array());

?>
