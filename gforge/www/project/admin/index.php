<?php
/**
 * Project Admin Main Page
 *
 * This page contains administrative information for the project as well
 * as allows to manage it. This page should be accessible to all project
 * members, but only admins may perform most functions.
 *
 * Copyright 2004 GForge, LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
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
require_once('www/include/role_utils.php');
require_once('common/include/account.php');
require_once('common/include/GroupJoinRequest.class');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// get current information
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

$perm =& $group->getPermission( session_get_user() );
if (!$perm || !is_object($perm)) {
	exit_error('Error','Could Not Get Permission');
} elseif ($perm->isError()) {
	exit_error('Error',$perm->getErrorMessage());
}

if (!$perm->isAdmin()) {
	exit_permission_denied();
}

if ($submit) {
	if ($adduser) {
		/*
			add user to this project
		*/
		if (!$group->addUser($form_unix_name,$role_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = $Language->getText('project_admin','user_added');
		}
	} else if ($rmuser) {
		/*
			remove a user from this group
		*/
		if (!$group->removeUser($user_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = $Language->getText('project_admin','user_removed');
		}
	} else if ($updateuser) {
		/*
			Adjust User Role
		*/
		if (!$group->updateUser($user_id,$role_id)) {
			$feedback .= 'Foo'.$group->getErrorMessage();
		} else {
			$feedback = $Language->getText('project_admin','user_updated');
		}
	} elseif ($acceptpending) {
		/*
			add user to this project
		*/
		if (!$group->addUser($form_unix_name,$role_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$gjr=new GroupJoinRequest($group,$form_userid);
			if (!$gjr || !is_object($gjr) || $gjr->isError()) {
				$feedback .= 'Error Getting GroupJoinRequest';
			} else {
				$gjr->delete(true);
			}
			$feedback = $Language->getText('project_admin','user_added');
		}
	} elseif ($rejectpending) {
		/*
			reject adding user to this project
		*/
		$gjr=new GroupJoinRequest($group,$form_userid);
		if (!$gjr || !is_object($gjr) || $gjr->isError()) {
			$feedback .= 'Error Getting GroupJoinRequest';
		} else {
			if (!$gjr->reject()) {
				exit_error('Error',$gjr->getErrorMessage());
			} else {
				$feedback .= 'Rejected';
			}
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
	<tr valign="top">
		<td width="50%">

<?php echo $HTML->boxTop($Language->getText('project_admin','project_information'));  ?>

&nbsp;
<br />
<?php echo $Language->getText('project_admin','short_description') ?><?php echo $group->getDescription(); ?>
<p><?php echo $Language->getText('project_admin','homepage_link') ?><strong><?php echo $group->getHomepage(); ?></strong></p>
<p><?php echo $Language->getText('project_admin','shell_server') ?><strong><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?></strong></p>
<p><?php echo $Language->getText('project_admin','shell_server_group_directory') ?><br/><strong><?php echo account_group_homedir($group->getUnixName()); ?></strong></p>
<p><?php echo $Language->getText('project_admin','www_directory') ?><br /><strong><?php echo account_group_homedir($group->getUnixName()).'/htdocs'; ?></p>

<?php if($sys_use_scm) { ?>
	<p>[ <a href="/tarballs.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('project_admin', 'download_tarball') ?></a> ]</p>
<?php } ?>

<hr />
<p>
<h4><?php echo $Language->getText('project_admin','trove_categorization') ?><a href="/project/admin/group_trove.php?group_id=<?php echo $group->getID(); ?>">[<?php echo $Language->getText('general','edit') ?>]</a></h4>
</p>
<?php
echo $HTML->boxMiddle($Language->getText('project_admin','tool_admin').'');

if($sys_use_tracker) { ?>
	<a href="/tracker/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','tracker_admin') ?></a><br />
<?php }
if($sys_use_docman) { ?>
	<a href="/docman/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','docmanager_admin') ?></a><br />
<?php }
if($sys_use_mail) { ?>
	<a href="/mail/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','mail_admin') ?></a><br />
<?php }
if($sys_use_news) { ?>
	<a href="/news/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','news_admin') ?></a><br />
<?php }
if($sys_use_pm) { ?>
	<a href="/pm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','task_manager_admin') ?></a><br />
<?php }
if($sys_use_forum) { ?>
	<a href="/forum/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','forum_admin') ?></a><br />
<?php }
if($sys_use_frs) { ?>
	<a href="/frs/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','frs_admin') ?></a><br />
<?php }
if($sys_use_scm) { ?>
	<a href="/scm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo $Language->getText('project_admin','scm_admin') ?></a><br />
<?php }

$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("project_admin_plugins", $hook_params) ;


echo $HTML->boxBottom(); 

?>
		</td>
		<td>&nbsp;</td>
		<td width="50%">

<?php

		echo $HTML->boxTop($Language->getText('project_admin','group_members'));

		/*

			Show the members of this project

		*/

		$res_memb = db_query("SELECT users.realname,users.user_id,
			users.user_name,user_group.admin_flags,user_group.role_id
			FROM users,user_group 
			WHERE users.user_id=user_group.user_id 
			AND user_group.group_id='$group_id' ORDER BY user_id");

		echo '
		<table width="100% border="2">
			<tr><td><strong>'.$Language->getText('project_admin','unix_name').'</strong></td>
			<td><strong>'.$Language->getText('rbac_edit','role').'</strong></td>
			<td><strong>'.$Language->getText('rbac_edit','update').'</strong></td>
			<td><strong>'.$Language->getText('rbac_edit','remove').'</strong></td></tr>';

while ($row_memb=db_fetch_array($res_memb)) {

		echo '
			<form action="'.$PHP_SELF.'" method="post">
			<input type="hidden" name="submit" value="y" />
			<input type="hidden" name="user_id" value="'.$row_memb['user_id'].'" />
			<input type="hidden" name="group_id" value="'. $group_id .'" />
			<td>'.$row_memb['realname'].' ('.$row_memb['user_name'].')</td>
			<td>'.role_box($group_id,'role_id',$row_memb['role_id']).'</td>
			<td><input type="submit" name="updateuser" value="'.$Language->getText('rbac_edit','update').'"></td>
			<td><input type="submit" name="rmuser" value="'.$Language->getText('rbac_edit','remove').'"></td>
			</tr></form>';
}
		echo '
			<tr><td>'.$Language->getText('rbac_edit','observerusername').'</td>
			<td></td>
			<form action="roleedit.php?group_id='. $group_id .'&amp;role_id=observer" method="POST">
			<td colspan="2"><input type="submit" name="edit" value="'.$Language->getText('rbac_edit','observer').'"></td></form></tr>';

/*
	Add member form
*/

?>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
			<input type="hidden" name="submit" value="y" />
			<tr><td><input type="text" name="form_unix_name" size="10" value="" /></td>
			<td><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?></td>
			<td colspan="2"><input type="submit" name="adduser" value="<?php echo $Language->getText('project_admin','add_user') ?>" /></td>
			</tr></form>

			<tr><td colspan="4"><a href="massadd.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('project_admin','addfromlist'); ?></a></td></tr>
		</table>
<!--	</td></tr>
</td>
<td width="50%">
&nbsp;
</td>-->
<?php 
//
//	Pending requests
//
echo $HTML->boxMiddle($Language->getText('project_joinrequest','pending'));
$reqs =& get_group_join_requests($group);
if (count($reqs) < 1) {
	echo $Language->getText('project_joinrequest','nonepending');
} else {
	for ($i=0; $i<count($reqs); $i++) {
		$user =& user_get_object($reqs[$i]->getUserId());
		if (!$user || !is_object($user)) {
			echo "Invalid User";
		}
		?>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
		<input type="hidden" name="submit" value="y" />
		<input type="hidden" name="form_userid" value="<?php echo $user->getId(); ?>" />
		<tr><td><input type="hidden" name="form_unix_name" value="<?php echo $user->getUnixName(); ?>" /><a href="/users/<?php echo $user->getUnixName(); ?>"><?php echo $user->getRealName(); ?></a></td>
		<td><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?>
			<input type="submit" name="acceptpending" value="<?php echo $Language->getText('project_admin','acceptpending') ?>" />
			<input type="submit" name="rejectpending" value="<?php echo $Language->getText('project_admin','rejectpending') ?>" /></td>
			</tr></form>
		
		<?php
	}
}


//
//	RBAC Editing Functions
//
echo $HTML->boxMiddle($Language->getText('rbac_edit','editroles'));
echo '<form action="roleedit.php?group_id='. $group_id .'" method="POST">';
echo role_box($group_id,'role_id','');
echo '<input type="submit" name="edit" value="'.$Language->getText('rbac_edit','editrole').'"></form>';

echo '<p><a href="roleedit.php?group_id='.$group_id.'">'.$Language->getText('rbac_edit','addrole').'</a>';

echo $HTML->boxBottom();?>

		</td>
	</tr>

</table>

<?php

project_admin_footer(array());

?>
