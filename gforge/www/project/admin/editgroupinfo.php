<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');
require ('vars.php');
require ($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// If this was a submission, make updates

if ($Update) {

	group_add_history ('Changed Public Info','',$group_id);

	// in the database, these all default to '1', 
	// so we have to explicity set 0
	if (!$use_bugs) {
		$use_bugs=0;
	}
	if (!$use_bug_depend) {
		$use_bug_depend=0;
	}
	if (!$use_mail) {
		$use_mail=0;
	}
	if (!$use_survey) {
		$use_survey=0;
	}
	if (!$use_patch) {
		$use_patch=0;
	}
	if (!$use_forum) {
		$use_forum=0;
	}
	if (!$use_pm) {
		$use_pm=0;
	}
	if (!$use_pm_depend) {
		$use_pm_depend=0;
	}
	if (!$use_cvs) {
		$use_cvs=0;
	}
	if (!$use_news) {
		$use_news=0;
	}
	if (!$use_support) {
		$use_support=0;
	}
	if (!$use_docman) {
		$use_docman=0;
	}
	if (!$send_all_bugs) {
		$send_all_bugs=0;
	}
	if (!$send_all_patches) {
		$send_all_patches=0;
	}
	if (!$send_all_support) {
		$send_all_support=0;
	}
	if (!$send_all_tasks) {
		$send_all_tasks=0;
	}
 
	//blank out any invalid email addresses
	if ($new_bug_address && !validate_email($new_bug_address)) {
		$new_bug_address='';
		$feedback .= ' Bug Address Appeared Invalid ';
	}
	if ($new_patch_address && !validate_email($new_patch_address)) {
		$new_patch_address='';
		$feedback .= ' Patch Address Appeared Invalid ';
	}
	if ($new_support_address && !validate_email($new_support_address)) {
		$new_support_address='';
		$feedback .= ' Support Address Appeared Invalid ';
	}
	if ($new_task_address && !validate_email($new_task_address)) {
		$new_task_address='';
		$feedback .= ' Task Address Appeared Invalid ';
	}
	if (!$form_group_name) {
		$form_group_name='Invalid Group Name';
	}
	if (!$form_homepage) {
		$form_homepage='http://sourceforge.net';
	}
	$result=db_query('UPDATE groups SET '
		."group_name='$form_group_name',"
		."homepage='$form_homepage',"
		."short_description='$form_shortdesc',"
		."use_bugs='$use_bugs',"
		."use_bug_depend_box='$use_bug_depend',"
		."use_mail='$use_mail',"
		."use_survey='$use_survey',"
		."use_patch='$use_patch',"
		."use_forum='$use_forum',"
		."use_pm='$use_pm',"
		."use_pm_depend_box='$use_pm_depend',"
		."use_cvs='$use_cvs',"
		."use_news='$use_news',"
		."use_support='$use_support',"
		."use_docman='$use_docman',"
		."new_bug_address='$new_bug_address',"
		."new_patch_address='$new_patch_address',"
		."new_support_address='$new_support_address',"
		."new_task_address='$new_task_address',"
		."send_all_bugs='$send_all_bugs', "
		."send_all_patches='$send_all_patches', "
		."send_all_support='$send_all_support', "
		."send_all_tasks='$send_all_tasks' "
		."WHERE group_id=$group_id");

	if (!$result || db_affected_rows($result) < 1) {
		$feedback .= ' UPDATE FAILED OR NO DATA CHANGED! '.db_error();
	} else {
		$feedback .= ' UPDATE SUCCESSFUL ';
	}
}

// update info for page
$res_grp = db_query("SELECT * FROM groups WHERE group_id=$group_id");
if (db_numrows($res_grp) < 1) {
	exit_no_group();
}
$row_grp = db_fetch_array($res_grp);

project_admin_header(array('title'=>'Editing Group Info','group'=>$group_id));

print '<P>Editing group info for: <B>'.$row_grp['group_name'].'</B>';

print '
<P>
<FORM action="'.$PHP_SELF.'" method="post">
<INPUT type="hidden" name="group_id" value="'.$group_id.'">

<P>Descriptive Group Name:
<BR><INPUT type="text" name="form_group_name" value="'.$row_grp['group_name'].'">

<P>Short Description (255 Character Max, HTML will be stripped from this description):
<BR><TEXTAREA cols=80 rows=3 wrap="virtual" name="form_shortdesc">
'.$row_grp['short_description'].'</TEXTAREA>

<P>Homepage Link:
<BR>http://<INPUT type="text" name="form_homepage" value="'.$row_grp['homepage'].'">

<HR>

<H3>Active Features:</H3>
<P>
';
/*
	Show the options that this project is using
*/

echo '
	<B>Use Bug Tracker:</B> <INPUT TYPE="CHECKBOX" NAME="use_bugs" VALUE="1"'.( ($row_grp['use_bugs']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Bug Dependency List:</B> <INPUT TYPE="CHECKBOX" NAME="use_bug_depend" VALUE="1"'.( ($row_grp['use_bug_depend_box']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Mailing Lists:</B> <INPUT TYPE="CHECKBOX" NAME="use_mail" VALUE="1"'.( ($row_grp['use_mail']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Surveys:</B> <INPUT TYPE="CHECKBOX" NAME="use_survey" VALUE="1"'.( ($row_grp['use_survey']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Patch Manager:</B> <INPUT TYPE="CHECKBOX" NAME="use_patch" VALUE="1"'.( ($row_grp['use_patch']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Forums:</B> <INPUT TYPE="CHECKBOX" NAME="use_forum" VALUE="1"'.( ($row_grp['use_forum']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Project/Task Manager:</B> <INPUT TYPE="CHECKBOX" NAME="use_pm" VALUE="1"'.( ($row_grp['use_pm']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Task Dependency List:</B> <INPUT TYPE="CHECKBOX" NAME="use_pm_depend" VALUE="1"'.( ($row_grp['use_pm_depend_box']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use CVS:</B> <INPUT TYPE="CHECKBOX" NAME="use_cvs" VALUE="1"'.( ($row_grp['use_cvs']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use News:</B> <INPUT TYPE="CHECKBOX" NAME="use_news" VALUE="1"'.( ($row_grp['use_news']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Doc Mgr:</B> <INPUT TYPE="CHECKBOX" NAME="use_docman" VALUE="1"'.( ($row_grp['use_docman']==1) ? ' CHECKED' : '' ).'><BR>
	<B>Use Support:</B> <INPUT TYPE="CHECKBOX" NAME="use_support" VALUE="1"'.( ($row_grp['use_support']==1) ? ' CHECKED' : '' ).'>';
echo '
	<P><B>If you wish, you can provide default email addresses to which new submissions will be sent.</B><BR>
	<B>New Bugs:</B><BR><INPUT TYPE="TEXT" NAME="new_bug_address" VALUE="'.$row_grp['new_bug_address'].'" SIZE="25" MAXLENGTH="250"> 
	(send on all updates) <INPUT TYPE="CHECKBOX" NAME="send_all_bugs" VALUE="1" '. (($row_grp['send_all_bugs'])?'CHECKED':'') .'><BR>
	<B>New Patches:</B><BR><INPUT TYPE="TEXT" NAME="new_patch_address" VALUE="'.$row_grp['new_patch_address'].'" SIZE="25" MAXLENGTH="250">
	(send on all updates) <INPUT TYPE="CHECKBOX" NAME="send_all_patches" VALUE="1" '. (($row_grp['send_all_patches'])?'CHECKED':'') .'><BR>
	<B>New Support Requests:</B><BR><INPUT TYPE="TEXT" NAME="new_support_address" VALUE="'.$row_grp['new_support_address'].'" SIZE="25" MAXLENGTH="250">
	(send on all updates) <INPUT TYPE="CHECKBOX" NAME="send_all_support" VALUE="1" '. (($row_grp['send_all_support'])?'CHECKED':'') .'><BR>
	<B>New Task Assignments:</B><BR><INPUT TYPE="TEXT" NAME="new_task_address" VALUE="'.$row_grp['new_task_address'].'" SIZE="25" MAXLENGTH="250">
	(send on all updates) <INPUT TYPE="CHECKBOX" NAME="send_all_tasks" VALUE="1" '. (($row_grp['send_all_tasks'])?'CHECKED':'') .'><BR>';

echo '
<HR>
<P><INPUT type="submit" name="Update" value="Update">
</FORM>
';

project_admin_footer(array());

?>
