<?php
/**
  *
  * Project Admin page to view permissions of the project members
  *
  * This pages shows permissions of all project members as static table,
  * with links to userpermedit.php for editing individual members.
  *
  * Known bugs:
  * 1. This page doesn't show permissions for specific trackers.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// Maps role id to string
function role_id2str($role_id) {
	global $member_roles_assoc;
	if (!$member_roles_assoc) {
		$sql = "SELECT category_id,name FROM people_job_category";
		$member_roles = db_query($sql);
		$member_roles_assoc = util_result_columns_to_assoc($member_roles);
	}

	if ($member_roles_assoc[$role_id]) {
		$str = $member_roles_assoc[$role_id];
		if (trim($str)) {
			return $str;
		} 
	}

	return '???';
}

// Render table row of developer's permissions
function show_permissions_row($i, $row_dev) {
	global $group_id;

	// Show admins in bold
	if (stristr($row_dev['admin_flags'],'A')) {
		$name = '<b>'.$row_dev['user_name'].' ('.$row_dev['realname'].')</b>';
	} else {
		$name = $row_dev['user_name'].' ('.$row_dev['realname'].')';
	}

	print '
	<tr align="center" BGCOLOR="'. html_get_alt_row_color($i) .'">'
	.'<td align="left">'
	.'<a href="userpermedit.php?group_id='.$group_id
	.'&user_id='.$row_dev['user_id'].'">'.$name.'</a><br>'
	.role_id2str($row_dev['member_role'])
	.(($row_dev['release_flags']==1)?', Rel.Tech.':'')
	.'</td>';

/*
	// cvs permissions
	$cvs2perm = array(0=>'Read', 1=>'Write', 2=>'Admin');
	print '<TD>'.$cvs2perm[$row_dev['cvs_flags']].'</td>'."\n";
*/

	// artifact manager permissions
	$art2perm = array(0=>'-',2=>'A');
	print '<TD>'.$art2perm[$row_dev['artifact_flags']].'</td>';

	// project/task manager permissions
	$flag2perm = array(0=>'-',1=>'T',2=>'A&T',3=>'A');
	print '<TD>'.$flag2perm[$row_dev['project_flags']].'</td>';

	// forum permissions
	$forum2perm = array(0=>'-',2=>'Moderator');
	print '<TD>'.$forum2perm[$row_dev['forum_flags']].'</td>';

	// documenation manager permissions
	$forum2perm = array(0=>'-',1=>'Editor');

	print '<TD>'.$forum2perm[$row_dev['doc_flags']].'</td>';

	print '</TR>';
}


/*
 *	Main Code
 */

$group =& group_get_object($group_id);
exit_assert_object($group, 'Group');

project_admin_header(array('title'=>'Project Developer Permissions','group'=>$group_id,'pagename'=>'project_admin_userperms','sectionvals'=>array(group_getname($group_id))));

?>

<P>
<p>Click developer's name below to edit permissions.</p>
<P>

<TABLE width="100%" cellspacing=0 cellpadding=2 border=0>

<TR align=center>
<TD><font size="-1"><B>General</B></font></TD>
<?php /*
<TD><font size="-1"><B>CVS</B></font></TD>
*/ ?>
<TD><font size="-1"><B>Tracker<br>Manager</B></font></TD>
<TD><font size="-1"><B>Task<br>Manager</B></font></TD>
<TD><font size="-1"><B>Forums</B></font></TD>
<TD><font size="-1"><B>Doc.<br>Manager</B></font></TD>
</TR>
<?php

	$res_dev = db_query("
		SELECT users.user_name AS user_name,
		users.realname, 
		users.user_id AS user_id, 
		user_group.admin_flags, 
		user_group.forum_flags, 
		user_group.project_flags, 
		user_group.doc_flags, 
		user_group.cvs_flags, 
		user_group.release_flags, 
		user_group.artifact_flags, 
		user_group.member_role 
		FROM users,user_group 
		WHERE 
		users.user_id=user_group.user_id AND user_group.group_id='$group_id'
		ORDER BY users.user_name
	");

	if (!$res_dev || db_numrows($res_dev) < 1) {
		echo '<H2>No Developers Found</H2>';
	} else {
		$i = 0;

		while ($row_dev = db_fetch_array($res_dev)) {

			show_permissions_row($i++, $row_dev);

		}
	}

?>

</TABLE>

<?php
project_admin_footer(array());
?>
