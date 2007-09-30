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


require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');
require_once('www/include/role_utils.php');
require_once('common/include/account.php');
require_once('common/include/GroupJoinRequest.class.php');

$group_id = getIntFromRequest('group_id');
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

if (getStringFromRequest('submit')) {
	if (getStringFromRequest('adduser')) {
		/*
			add user to this project
		*/
		$form_unix_name = getStringFromRequest('form_unix_name');
		$user_object = &user_get_object_by_name($form_unix_name);
		$user_id = $user_object->getID();
		$role_id = getIntFromRequest('role_id');
		if (!$group->addUser($form_unix_name,$role_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = _('User Added Successfully');

			//plugin webcal
			//change assistant for webcal
			$params[0] = getIntFromRequest('user_id');
			$params[1] = getIntFromRequest('group_id');
			plugin_hook('change_cal_permission',$params);
			$group_id = getIntFromRequest('group_id');

			//if the user have requested to join this group
			//we should remove him from the request list
			//since it has already been added
			$gjr=new GroupJoinRequest($group,$user_id);
			if ($gjr || is_object($gjr) || !$gjr->isError()) {
				$gjr->delete(true);
			}

		}
	} else if (getStringFromRequest('rmuser')) {
		/*
			remove a user from this group
		*/
		$user_id = getIntFromRequest('user_id');
		if (!$group->removeUser($user_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = _('User Removed Successfully');
			//plugin webcal
			//change assistant for webcal
			$params[0] = getIntFromRequest('user_id');
			$params[1] = getIntFromRequest('group_id');
			plugin_hook('change_cal_permission',$params);
			$group_id = getIntFromRequest('group_id');
		}
	} else if (getStringFromRequest('updateuser')) {
		/*
			Adjust User Role
		*/
		$user_id = getIntFromRequest('user_id');
		$role_id = getIntFromRequest('role_id');
		if (!$group->updateUser($user_id,$role_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$feedback = _('User updated successfully');
			//plugin webcal
			//change assistant for webcal
			$params[0] = getIntFromRequest('user_id');
			$params[1] = getIntFromRequest('group_id');
			plugin_hook('change_cal_permission',$params);
			$group_id = getIntFromRequest('group_id');
			
		}
	} elseif (getStringFromRequest('acceptpending')) {
		/*
			add user to this project
		*/
		$form_userid = getIntFromRequest('form_userid');
		$form_unix_name = getStringFromRequest('form_unix_name');
		$role_id = getIntFromRequest('role_id');
		if (!$group->addUser($form_unix_name,$role_id)) {
			$feedback .= $group->getErrorMessage();
		} else {
			$gjr=new GroupJoinRequest($group,$form_userid);
			if (!$gjr || !is_object($gjr) || $gjr->isError()) {
				$feedback .= 'Error Getting GroupJoinRequest';
			} else {
				$gjr->delete(true);
			}
			$feedback = _('User Added Successfully');
		}
	} elseif (getStringFromRequest('rejectpending')) {
		/*
			reject adding user to this project
		*/
		$form_userid = getIntFromRequest('form_userid');
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

$adminheadertitle=sprintf(_('Project Admin: %1$s'), $group->getPublicName() );
project_admin_header(array('title'=>$adminheadertitle, 'group'=>$group->getID()));

/*
	Show top box listing trove and other info
*/

?>

<table width="100%" cellpadding="2" cellspacing="2" border="0">
	<tr valign="top">
		<td width="50%">

<?php echo $HTML->boxTop(_('Misc. Project Information'));  ?>

&nbsp;
<br />
<?php echo _('Short Description:&nbsp;') ?><?php echo $group->getDescription(); ?>
<p><?php echo _('Homepage Link:&nbsp;') ?><strong><?php echo $group->getHomepage(); ?></strong></p>

<?php
	global $sys_use_shell;
	if ($sys_use_shell) {
?> 
<p><?php echo _('Group shell (SSH) server:&nbsp;') ?><strong><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?></strong></p>
<p><?php echo _('Group directory on shell server:&nbsp;') ?><br/><strong><?php echo account_group_homedir($group->getUnixName()); ?></strong></p>
<p><?php echo _('Project WWW directory on shell server:&nbsp;') ?><br /><strong><?php echo account_group_homedir($group->getUnixName()).'/htdocs'; ?></strong></p>
<?php
	} //end of use_shell condition
?> 

<?php	if($sys_use_scm) { ?>
	<p>[ <a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/tarballs.php?group_id=<?php echo $group_id; ?>"><?php echo _('Download Your Nightly SCM Tree Tarball') ?></a> ]</p>
<?php	} ?>

<hr />
<p>
<h4><?php echo _('Trove Categorization:&nbsp;') ?><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/project/admin/group_trove.php?group_id=<?php echo $group->getID(); ?>">[<?php echo _('Edit') ?>]</a></h4>
</p>
<?php
echo $HTML->boxMiddle(_('Tool Admin').'');

if($sys_use_tracker) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/tracker/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Tracker admin') ?></a><br />
<?php }
if($sys_use_docman) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/docman/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Doc manager admin') ?></a><br />
<?php }
if($sys_use_mail) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/mail/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Mail admin') ?></a><br />
<?php }
if($sys_use_news) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/news/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('News admin') ?></a><br />
<?php }
if($sys_use_pm) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/pm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Task manager admin') ?></a><br />
<?php }
if($sys_use_forum) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/forum/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('Forum admin') ?></a><br />
<?php }
if($sys_use_frs) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/frs/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('FRS admin') ?></a><br />
<?php }
if($sys_use_scm) { ?>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/scm/admin/?group_id=<?php echo $group->getID(); ?>"><?php echo _('SCM admin') ?></a><br />
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

		echo $HTML->boxTop(_('Group Members'));

		/*

			Show the members of this project

		*/

		$res_memb = db_query("SELECT users.realname,users.user_id,
			users.user_name,user_group.admin_flags,user_group.role_id
			FROM users,user_group 
			WHERE users.user_id=user_group.user_id 
			AND user_group.group_id='$group_id' ORDER BY user_id");

		echo '
		<table width="100%" border="0">
			<tr><td><strong>'._('Unix name').'</strong></td>
			<td><strong>'._('Role').'</strong></td>
			<td><strong>'._('Update').'</strong></td>
			<td><strong>'._('Remove').'</strong></td></tr>';

while ($row_memb=db_fetch_array($res_memb)) {

		echo '
			<form action="'.getStringFromServer('PHP_SELF').'" method="post">
			<input type="hidden" name="submit" value="y" />
			<input type="hidden" name="user_id" value="'.$row_memb['user_id'].'" />
			<input type="hidden" name="group_id" value="'. $group_id .'" />
			<td>'.$row_memb['realname'].' ('.$row_memb['user_name'].')</td>
			<td>'.role_box($group_id,'role_id',$row_memb['role_id']).'</td>
			<td><input type="submit" name="updateuser" value="'._('Update').'"></td>
			<td><input type="submit" name="rmuser" value="'._('Remove').'"></td>
			</tr></form>';
}
		echo '
			<tr><td>'._('Observer').'</td>
			<td></td>
			<form action="roleedit.php?group_id='. $group_id .'&amp;role_id=observer" method="POST">
			<td colspan="2"><input type="submit" name="edit" value="'._('Edit Observer').'"></td></form></tr>';

/*
	Add member form
*/

?>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
			<input type="hidden" name="submit" value="y" />
			<tr><td><input type="text" name="form_unix_name" size="10" value="" /></td>
			<td><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?></td>
			<td colspan="2"><input type="submit" name="adduser" value="<?php echo _('Add user') ?>" /></td>
			</tr></form>

			<tr><td colspan="4"><a href="massadd.php?group_id=<?php echo $group_id; ?>"><?php echo _('Add Users From List'); ?></a></td></tr>
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
echo $HTML->boxMiddle(_('Pending Requests'));
$reqs =& get_group_join_requests($group);
if (count($reqs) < 1) {
	echo _('No Pending Requests');
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
		<tr><td><input type="hidden" name="form_unix_name" value="<?php echo $user->getUnixName(); ?>" /><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/users/<?php echo $user->getUnixName(); ?>"><?php echo $user->getRealName(); ?></a></td>
		<td><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?>
			<input type="submit" name="acceptpending" value="<?php echo _('Accept') ?>" />
			<input type="submit" name="rejectpending" value="<?php echo _('Reject') ?>" /></td>
			</tr></form>
		
		<?php
	}
}


//
//	RBAC Editing Functions
//
echo $HTML->boxMiddle(_('Edit Roles'));
echo '<form action="roleedit.php?group_id='. $group_id .'" method="POST">';
echo role_box($group_id,'role_id','');
echo '<input type="submit" name="edit" value="'._('Edit Role').'"></form>';

echo '<p><a href="roleedit.php?group_id='.$group_id.'">'._('Add Role').'</a>';

//
//	Project hierarchy functions

plugin_hook('admin_project_link',$group_id) ;


echo $HTML->boxBottom();?>

		</td>
	</tr>

</table>

<?php

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
