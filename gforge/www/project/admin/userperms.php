<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
require ($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

function member_role_box($name,$checked) {
	global $member_roles;
	if (!$member_roles) {
		$sql="SELECT * FROM people_job_category";
		$member_roles=db_query($sql);
	}
	return html_build_select_box ($member_roles,$name,$checked,true,'Undefined');
}


$group=group_get_object($group_id);

$res_grp = $group->getData();

//no results found
if (db_numrows($res_grp) < 1) {
	exit_error("Invalid Group","That group does not exist.");
}
$row_grp = db_fetch_array($res_grp);

// ########################### form submission, make updates
if ($submit) {
	group_add_history ('Changed Permissions','',$group_id);

	$res_dev = db_query("SELECT user_id FROM user_group WHERE group_id=$group_id");
	while ($row_dev = db_fetch_array($res_dev)) {
		$admin_flags="admin_user_$row_dev[user_id]";
		$bug_flags="bugs_user_$row_dev[user_id]";
		$forum_flags="forums_user_$row_dev[user_id]";
		$project_flags="projects_user_$row_dev[user_id]";
		$patch_flags="patch_user_$row_dev[user_id]";
		$support_flags="support_user_$row_dev[user_id]";
		$doc_flags="doc_user_$row_dev[user_id]";
		$cvs_flags="cvs_user_$row_dev[user_id]";
		$release_flags="release_user_$row_dev[user_id]";
		$member_role="role_user_$row_dev[user_id]";

		//call to control function in the $Group object
		if (!$group->updateUser($row_dev['user_id'],$$admin_flags,$$bug_flags,$$forum_flags,
			$$project_flags,$$patch_flags,$$support_flags,
			$$doc_flags,$$cvs_flags,$$release_flags,
			$$member_role)) {
			$feedback .= $group->getErrorMessage();
			break;
		}
	}

	$feedback .= ' Permissions Updated ';
}

$res_dev = db_query("SELECT users.user_name AS user_name,"
	. "users.user_id AS user_id, "
	. "user_group.admin_flags, "
	. "user_group.bug_flags, "
	. "user_group.forum_flags, "
	. "user_group.project_flags, "
	. "user_group.patch_flags, "
	. "user_group.doc_flags, "
	. "user_group.support_flags, "
	. "user_group.cvs_flags, "
	. "user_group.release_flags, "
	. "user_group.member_role "
	. "FROM users,user_group WHERE "
	. "users.user_id=user_group.user_id AND user_group.group_id=$group_id "
	. "ORDER BY users.user_name");

project_admin_header(array('title'=>'Project Developer Permissions','group'=>$group_id));
?>

<P><B>Developer Permissions for Project: <?php html_a_group($group_id); ?></B>
<P>
<B>NOTE:</B>

<dl>
<dt><B>Project Admins</B></dt>
<dd>can access this page and other project administration pages</dd>

<dt><B>Release Technicians</B></dt>
<dd>can make the file releases (any project admin also a release technician)</dd>

<?php /*
<dt><B>CVS Admins</B></dt>
<dd><!-- can --> <i>will</i> be able to access repository files directly (in addition to standard write access)</dd>
*/ ?>

<dt><B>Tool Technicians (T)</B></dt>
<dd>can be assigned Bugs/Tasks/Patches</dd>

<dt><B>Tool Admins (A)</B></dt>
<dd>can make changes to Bugs/Tasks/Patches as well as use the /toolname/admin/ pages</dd>

<dt><B>Moderators</B> (forums)</dt>
<dd>can delete messages from the project forums</dd>

<dt><B>Editors</B> (doc. manager)</dt>
<dd>can update/edit/remove documentation from the project.</dd>
</dl>

<P>
<FORM action="userperms.php" method="post">
<INPUT type="hidden" name="group_id" value="<?php print $group_id; ?>">
<TABLE width="100%" cellspacing=0 cellpadding=2 border=0>
<TR align=center>
<TD><font size="-1"><B>General</B></font></TD>
<?php /*
<TD><font size="-1"><B>CVS</B></font></TD>
*/ ?>
<TD><font size="-1"><B>Bug<br>Tracking</B></font></TD>
<TD><font size="-1"><B>Task<br>Manager</B></font></TD>
<TD><font size="-1"><B>Patch<br>Manager</B></font></TD>
<TD><font size="-1"><B>Support<br>Manager</B></font></TD>
<TD><font size="-1"><B>Forums</B></font></TD>
<TD><font size="-1"><B>Doc.<br>Manager</B></font></TD>
</TR>

<?php

if (!$res_dev || db_numrows($res_dev) < 1) {
	echo '<H2>No Developers Found</H2>';
} else {

	while ($row_dev = db_fetch_array($res_dev)) {
		$i++;
		$cur_color=html_get_alt_row_color($i);
	print '
	<TR valign="bottom" BGCOLOR="'. $cur_color .'">
		<TD colspan="7"><b>'.$row_dev['user_name'].'</b></td>
	</tr>

	<tr bgcolor="'. $cur_color .'"><td>
		<font size="-1">';

	echo member_role_box('role_user_'.$row_dev['user_id'],$row_dev['member_role']).'<br>';

	print '<INPUT TYPE="CHECKBOX" NAME="admin_user_'.$row_dev['user_id'].'" VALUE="A" ';
	print ((stristr($row_dev['admin_flags'],'A'))?'CHECKED':'').'> Admin';

	print '
		<INPUT TYPE="CHECKBOX" NAME="release_user_'.$row_dev['user_id'].'" VALUE="1" '.
		(($row_dev['release_flags']==1)?'CHECKED':'').'> Rel.Tech.</font></td>

';

/*
	// cvs selects
	print '<TD><FONT size="-1"><SELECT name="cvs_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['cvs_flags']==0)?" selected":"").'>Read';
	print '<OPTION value="1"'.(($row_dev['cvs_flags']==1)?" selected":"").'>Write';
	print '<OPTION value="2"'.(($row_dev['cvs_flags']==2)?" selected":"").'>Admin';
	print '</SELECT></FONT></TD>
';
*/
	// bug selects
	print '<TD><FONT size="-1"><SELECT name="bugs_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['bug_flags']==0)?" selected":"").'>-';
	print '<OPTION value="1"'.(($row_dev['bug_flags']==1)?" selected":"").'>T';
	print '<OPTION value="2"'.(($row_dev['bug_flags']==2)?" selected":"").'>A,T';
	print '<OPTION value="3"'.(($row_dev['bug_flags']==3)?" selected":"").'>A';
	print '</SELECT></FONT></TD>
';
	// project selects
	print '<TD><FONT size="-1"><SELECT name="projects_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['project_flags']==0)?" selected":"").'>-';
	print '<OPTION value="1"'.(($row_dev['project_flags']==1)?" selected":"").'>T';
	print '<OPTION value="2"'.(($row_dev['project_flags']==2)?" selected":"").'>A,T';
	print '<OPTION value="3"'.(($row_dev['project_flags']==3)?" selected":"").'>A';
	print '</SELECT></FONT></TD>
';
	// patch selects
	print '<TD><FONT size="-1"><SELECT name="patch_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['patch_flags']==0)?" selected":"").'>-';
	print '<OPTION value="1"'.(($row_dev['patch_flags']==1)?" selected":"").'>T';
	print '<OPTION value="2"'.(($row_dev['patch_flags']==2)?" selected":"").'>A,T';
	print '<OPTION value="3"'.(($row_dev['patch_flags']==3)?" selected":"").'>A';
	print '</SELECT></FONT></TD>
';

	// patch selects
	print '<TD><FONT size="-1"><SELECT name="support_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['support_flags']==0)?" selected":"").'>-';
	print '<OPTION value="1"'.(($row_dev['support_flags']==1)?" selected":"").'>T';
	print '<OPTION value="2"'.(($row_dev['support_flags']==2)?" selected":"").'>A,T';
	print '<OPTION value="3"'.(($row_dev['support_flags']==3)?" selected":"").'>A';
	print '</SELECT></FONT></TD>
';

	// forums
	print '<TD><FONT size="-1"><SELECT name="forums_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['forum_flags']==0)?" selected":"").'>-';
	print '<OPTION value="2"'.(($row_dev['forum_flags']==2)?" selected":"").'>Moderator';
	print '</SELECT></FONT></TD>
';

	//documenation states - nothing or editor	
	print '<TD><FONT size="-1"><SELECT name="doc_user_'.$row_dev['user_id'].'">';
	print '<OPTION value="0"'.(($row_dev['doc_flags']==0)?" selected":"").'>-';
	print '<OPTION value="1"'.(($row_dev['doc_flags']==1)?" selected":"").'>Editor';
	print '</SELECT></FONT></TD>
';

	print '</TR>
';
}

}
?>

</TABLE>
<P align="center"><INPUT type="submit" name="submit" value="Update Developer Permissions">
</FORM>

<?php
project_admin_footer(array());
?>
