<?php
/**
  *
  * Project Admin Main Page
  *
  * This page contains administrative information for the project as well
  * as allows to manage it. This page should be accessible to all project
  * members, but only admins may perform most functions.
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
require_once('common/include/account.php');

session_require(array('group'=>$group_id));

// get current information
$group =& group_get_object($group_id);
exit_assert_object($group,'Group');

$perm =& $group->getPermission( session_get_user() );
exit_assert_object($perm,'Permission');

// only site admin get access inactive projects
if (!$group->isActive() && !$perm->isSuperUser()) {
	exit_error('Permission denied', 'Group is inactive.');
}

$is_admin = $perm->isAdmin();

// Only admin can make modifications via this page
if ($is_admin && $func) {
	/*
		updating the database
	*/
	if ($func=='adduser') {
		/*
			add user to this project
		*/

		if (!$group->addUser($form_unix_name)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = $Language->getText('project_admin','user_added');
		}

	} else if ($func=='rmuser') {
		/*
			remove a user from this group
		*/
		if (!$group->removeUser($rm_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = $Language->getText('project_admin','user_removed');
		}
	}

}

$group->clearError();

project_admin_header(array('title'=>$Language->getText('project_admin','title', array($group->getPublicName())),'group'=>$group->getID(),'pagename'=>'project_admin','sectionvals'=>array($group->getPublicName())));

/*
	Show top box listing trove and other info
*/

?>

<table width="100%" cellpadding="2" cellspacing="2" border="0">
<tr valign="top"><td width="50%">

<?php echo $HTML->boxTop($Language->getText('project_admin','project_information'));  ?>

&nbsp;
<br />
<?php echo $Language->getText('project_admin','short_description') ?>: <?php echo $group->getDescription(); ?>
<p>
<?php echo $Language->getText('project_admin','homepage_link') ?>: <strong><?php echo $group->getHomepage(); ?></strong>
</p><p>
<?php echo $Language->getText('project_admin','shell_server') ?>: <strong><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?></strong>
</p><p>
<?php echo $Language->getText('project_admin','shell_server_group_directory') ?>: <strong><?php echo account_group_homedir($group->getUnixName()); ?></strong>
</p><p>
<?php echo $Language->getText('project_admin','www_directory') ?>:
<strong><?php echo account_group_homedir($group->getUnixName()).'/htdocs'; ?>

<p align="center">
<a href="http://<?php echo $GLOBALS['sys_cvs_host']; ?>/cvstarballs/<?php echo $group->getUnixName(); ?>-cvsroot.tar.gz">[ <?php echo $Language->getText('project_admin','download_tarball') ?>]</a></p>
<p>&nbsp;</p>
<hr noshade="noshade" />
<p>&nbsp;</p>
<h4><?php echo $Language->getText('project_admin','trove_categorization') ?>:
<a href="/project/admin/group_trove.php?group_id=<?php echo $group->getID(); ?>">
[<?php echo $Language->getText('general','edit') ?>]</a></h4>
<p>
<?php
echo $HTML->boxBottom(); 

echo '
</td><td>&nbsp;</td><td width="50%">';


echo $HTML->boxTop($Language->getText('project_admin','group_members'));

/*

	Show the members of this project

*/

$res_memb = db_query("SELECT users.realname,users.user_id,users.user_name,user_group.admin_flags ".
		"FROM users,user_group ".
		"WHERE users.user_id=user_group.user_id ".
		"AND user_group.group_id='$group_id'");

print '<table width="100% border="0">';

while ($row_memb=db_fetch_array($res_memb)) {

	if (stristr($row_memb['admin_flags'], 'A')) {
		$img="trash-x.png";
	} else {
		$img="trash.png";
	}
	if ($is_admin) {
		$button='<input type="image" name="DELETE" src="'.$HTML->imgroot.'/ic/'.$img.'" height="16" width="16" border="0" />';
	} else {
		$button='&nbsp;';
	}
	print '
		<form action="rmuser.php" method="post"><input type="hidden" name="func" value="rmuser" />'.
		'<input type="hidden" name="return_to" value="'.$REQUEST_URI.'" />'.
		'<input type="hidden" name="rm_id" value="'.$row_memb['user_id'].'" />'.
		'<input type="hidden" name="group_id" value="'. $group_id .'" />'.
		'<tr><td align="center">'.$button.'</td></form>'.
		'<td><a href="/users/'.$row_memb['user_name'].'/">'.$row_memb['realname'].'</a></td></tr>';
}
print '</table>';

/*
	Add member form
*/

if ($is_admin) {

	// After adding user, we go to the permission page for one
?>
	<hr noshade="noshade size="1" />
	<form action="userpermedit.php?group_id=<?php echo $group->getID(); ?>" method="post">
	<input type="hidden" name="func" value="adduser" />
	<table width="100%" border="0">
	<tr><td><strong><?php echo $Language->getText('project_admin','unix_name') ?>:</strong></td><td><input type="text" name="form_unix_name" size="10" value="" /></td></tr>
	<tr><td colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $Language->getText('project_admin','add_user') ?>" /></td></tr></form>
	</table>

	<hr noshade="noshade size="1" />
	<div align="center">
	<a href="/project/admin/userperms.php?group_id=<?php echo $group->getID(); ?>">[<?php echo $Language->getText('project_admin','edit_member_permissions') ?>]</a>
	</div>
	</td></tr>

<?php
}
?>
 
<?php echo $HTML->boxBottom();?>


</td></tr>

<tr valign="top"><td width="50%">

<?php

/*
	Tool admin pages
*/

echo $HTML->boxTop($Language->getText('project_admin','tool_admin').'');

?>

<br />
<a href="/tracker/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','tracker_admin') ?></a><br />
<a href="/docman/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','docmanager_admin') ?></a><br />
<a href="/mail/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','mail_admin') ?></a><br />
<a href="/news/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','news_admin') ?></a><br />
<a href="/pm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','task_manager_admin') ?></a><br />
<a href="/forum/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','forum_admin') ?></a><br />

<?php echo $HTML->boxBottom(); ?>




</td>

<td>&nbsp;</td>

<td width="50%">

<?php echo $HTML->boxTop($Language->getText('project_admin','file_releases')); ?>
	&nbsp;<br />
	<div align="center">
	<a href="editpackages.php?group_id=<?php print $group_id; ?>"><strong>[<?php echo $Language->getText('project_admin','edit_file_releases') ?>]</strong></a>
	</div>

	<hr />
	<strong><?php echo $Language->getText('project_admin','packages') ?>:</strong>

	<p>

	<?php

	$res_module = db_query("SELECT * FROM frs_package WHERE group_id='$group_id'");
	while ($row_module = db_fetch_array($res_module)) {
		print "$row_module[name]<br />";
	}

	echo $HTML->boxBottom();
	?>
</p></td>
</tr>
</table>

<?php

project_admin_footer(array());

?>
