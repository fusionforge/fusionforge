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
	global $Language;
	// Show admins in bold
	if (stristr($row_dev['admin_flags'],'A')) {
		$name = '<strong>'.$row_dev['user_name'].' ('.$row_dev['realname'].')</strong>';
	} else {
		$name = $row_dev['user_name'].' ('.$row_dev['realname'].')';
	}

	print '
	<tr align="center" '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'
	.'<td align="left">'
	.'<a href="userpermedit.php?group_id='.$group_id
	.'&amp;user_id='.$row_dev['user_id'].'">'.$name.'</a><br />'
	.role_id2str($row_dev['member_role'])
	.(($row_dev['release_flags']==1)?', '.$Language->getText('project_admin_userperms','rel_tech'):'')
	.'</td>';

	// cvs permissions
	$cvs2perm = array(0=>'-', 1=>'Commit', 2=>'Admin');
	print '<td>'.$cvs2perm[$row_dev['cvs_flags']].'</td>'."\n";

	// artifact manager permissions
	$art2perm = array(0=>'-',2=>'A');
	print '<td>'.$art2perm[$row_dev['artifact_flags']].'</td>';

	// project/task manager permissions
	$flag2perm = array(0=>'-',1=>'T',2=>'A&T',3=>'A');
	print '<td>'.$flag2perm[$row_dev['project_flags']].'</td>';

	// forum permissions
	$forum2perm = array(0=>'-',2=>$Language->getText('project_admin_userperms','moderator'));
	print '<td>'.$forum2perm[$row_dev['forum_flags']].'</td>';

	// documenation manager permissions
	$forum2perm = array(0=>'-',1=>$Language->getText('project_admin_userperms','editor'));

	print '<td>'.$forum2perm[$row_dev['doc_flags']].'</td>';

	print '</tr>';
}


/*
 *	Main Code
 */

$group =& group_get_object($group_id);
exit_assert_object($group, 'Group');

project_admin_header(array('title'=>$Language->getText('project_admin_userperms','title'),'group'=>$group_id,'pagename'=>'project_admin_userperms','sectionvals'=>array(group_getname($group_id))));

?>

<p>&nbsp;</p>
<p><?php echo $Language->getText('project_admin_userperms','info') ?>.</p>
<p>&nbsp;</p>

<table width="100%" cellspacing="0" cellpadding="2" border="0">

<tr align="center">
<td><span style="font-size:smaller"><strong><?php echo $Language->getText('project_admin_userperms','general') ?></strong></span></td>
<td><font size="-1"><strong>CVS</strong></font></td>
<td><span style="font-size:smaller"><strong><?php echo $Language->getText('project_admin_userperms','tracker_manager') ?></strong></span></td>
<td><span style="font-size:smaller"><strong><?php echo $Language->getText('project_admin_userperms','task_manager') ?></strong></span></td>
<td><span style="font-size:smaller"><strong><?php echo $Language->getText('project_admin_userperms','forums') ?></strong></span></td>
<td><span style="font-size:smaller"><strong><?php echo $Language->getText('project_admin_userperms','docman') ?></strong></span></td>
</tr>
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
		echo '<h2>'.$Language->getText('project_admin_userperms','no_developers').'</h2>';
	} else {
		$i = 0;

		while ($row_dev = db_fetch_array($res_dev)) {

			show_permissions_row($i++, $row_dev);

		}
	}

?>

</table>

<?php
project_admin_footer(array());
?>
