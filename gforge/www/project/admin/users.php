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
 * @version   $Id: index.php 5829 2006-10-19 20:02:18Z federicot $
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
require_once $gfwww.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfwww.'include/role_utils.php';
require_once $gfcommon.'include/account.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$group_id = getIntFromRequest('group_id');
$feedback = getStringFromRequest('feedback');
session_require_perm ('project_admin', $group_id) ;

// get current information
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

// Add hook to replace users managements by a plugin.
$html_code = array();
if (plugin_hook_listeners("project_admin_users") > 0) {
	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("project_admin_users", $hook_params);
}

if (getStringFromRequest('submit')) {
	if (getStringFromRequest('adduser')) {
		/*
			add user to this project
			*/
		$form_unix_name = getStringFromRequest('form_unix_name');
		$user_object = &user_get_object_by_name($form_unix_name);
		if ($user_object === false) {
			$feedback .= _("<p>No Matching Users Found</p>");
		} else {
			$role_id = getIntFromRequest('role_id');
			if (!$role_id) {
				$feedback .= _("Role not selected");
			} else {
				$user_id = $user_object->getID();
				if (!$group->addUser($form_unix_name,$role_id)) {
					$error_msg = $group->getErrorMessage();
				} else {
					$feedback = _("User Added Successfully");
					//if the user have requested to join this group
					//we should remove him from the request list
					//since it has already been added
					$gjr=new GroupJoinRequest($group,$user_id);
					if (($gjr || is_object($gjr)) && (!$gjr->isError())) {
						$gjr->delete(true);
					}
				}
			}
		}
	} else if (getStringFromRequest('rmuser')) {
		/*
			remove a user from this group
			*/
		$user_id = getIntFromRequest('user_id');
		if (!$group->removeUser($user_id)) {
			$error_msg = $group->getErrorMessage();
		} else {
			$feedback = _("User Removed Successfully");
		}
	} else if (getStringFromRequest('updateuser')) {
		/*
			Adjust User Role
			*/
		$user_id = getIntFromRequest('user_id');
		$role_id = getIntFromRequest('role_id');
		if (! $role_id) {
			$error_msg = _("Role not selected");
		}
		else {
			if (!$group->updateUser($user_id,$role_id)) {
				$error_msg = $group->getErrorMessage();
			} else {
				$feedback = _("User Updated Successfully");
			}
		}
	} elseif (getStringFromRequest('acceptpending')) {
		/*
			add user to this project
			*/
		$role_id = getIntFromRequest('role_id');
		if (!$role_id) {
			$feedback .= _("Role not selected");
		} else {
			$form_userid = getIntFromRequest('form_userid');
			$form_unix_name = getStringFromRequest('form_unix_name');
			if (!$group->addUser($form_unix_name,$role_id)) {
				$error_msg = $group->getErrorMessage();
			} else {
				$gjr=new GroupJoinRequest($group,$form_userid);
				if (!$gjr || !is_object($gjr) || $gjr->isError()) {
					$error_msg = 'Error Getting GroupJoinRequest';
				} else {
					$gjr->delete(true);
				}
				$feedback = _("User Added Successfully");
			}
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

project_admin_header(array('title'=>sprintf(_('Project Admin: %s'), $group->getPublicName()),'group'=>$group->getID()));

?>

<table width="100%" cellpadding="2" cellspacing="2">
	<tr valign="top">
		<td width="50%"><?php 
		//
		//	Pending requests
		//
		$reqs =& get_group_join_requests($group);
		if (count($reqs) > 0) {
			echo $HTML->boxTop(_("Pending Requests"));
			for ($i=0; $i<count($reqs); $i++) {
				$user =& user_get_object($reqs[$i]->getUserId());
				if (!$user || !is_object($user)) {
					echo "Invalid User";
				}
				?>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>"
			method="post">
		<table width="100%">
			<tr>
				<td style="white-space: nowrap;"><input type="hidden" name="submit"
					value="y" /> <input type="hidden" name="form_userid"
					value="<?php echo $user->getId(); ?>" /> <input type="hidden"
					name="form_unix_name" value="<?php echo $user->getUnixName(); ?>" /><a
					href="/users/<?php echo $user->getUnixName(); ?>"><?php echo $user->getRealName(); ?></a>
				</td>
				<td style="white-space: nowrap; text-align: right;"><?php echo role_box($group_id,'role_id',$row_memb['role_id']); ?>
				<input type="submit" name="acceptpending"
					value="<?php echo _("Accept") ?>" />
				<input type="submit" name="rejectpending"
					value="<?php echo _("Reject") ?>" />
				</td>
			</tr>
		</table>
		</form>

		<?php
			}
			echo $HTML->boxBottom();
		}

		echo $HTML->boxTop(_("Add User"));

		if (isset($html_code['add_user'])) {
			echo $html_code['add_user'];
		} else {

			/*
			 Add member form
			 */
			?>
		<form
			action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>"
			method="post">
		<p><input type="hidden" name="submit" value="y" /> <input type="text"
			name="form_unix_name" size="10" value="" /> <?php echo role_box($group_id,'role_id'); ?>
		<input type="submit" name="adduser"
			value="<?php echo _("Add User") ?>" />
		</p>
		</form>
		<p><a
                        href="massadd.php?group_id=<?php echo $group_id; ?>"><?php echo _("Add Users From List"); ?></a></p>

			<?php
		}
		//
		//      RBAC Editing Functions
		//
		echo $HTML->boxMiddle(_("Edit Roles"));

		echo '
        <form action="roleedit.php?group_id='. $group_id .'&amp;role_id=observer" method="post">
        <p><input type="submit" name="edit" value="'._("Edit Observer").'" /></p>
        </form>';

		echo '<form action="roleedit.php?group_id='. $group_id .'" method="post"><p>';
		echo role_box($group_id,'role_id','');
		echo '&nbsp;<input type="submit" name="edit" value="'._("Edit Role").'" /></p></form>';

		echo '<p><a href="roleedit.php?group_id='.$group_id.'">'._("Add Role").'</a>';
		echo '</p>';

		echo $HTML->boxBottom();

		?></td>
		<td><?php

		echo $HTML->boxTop(_("Project Members"));

		/*

		Show the members of this project

		*/

		$res_memb = db_query_params('SELECT users.realname,users.user_id,
			users.user_name,user_group.admin_flags,user_group.role_id
			FROM users,user_group 
			WHERE users.user_id=user_group.user_id 
			AND user_group.group_id=$1 ORDER BY users.realname',
			array($group_id));

echo '<table width="100%"><thead><tr>';
echo '<th>'._('User name').'</th>';
echo '<th>'._('Role').'</th>';
echo '<th>'._('Update').'</th>';
echo '<th>'._('Remove').'</th>';
echo '</tr></thead><tbody>';
		while ($row_memb=db_fetch_array($res_memb)) {

			echo '
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
                        <tr>
                        <td style="white-space: nowrap;">
			  <input type="hidden" name="submit" value="y" />
			  <input type="hidden" name="user_id" value="'.$row_memb['user_id'].'" />
			  <input type="hidden" name="group_id" value="'. $group_id .'" />
			  <a href="/users/'.$row_memb['user_name'].'">'.$row_memb['realname'].'</a>
			</td>
			<td style="white-space: nowrap; text-align: right;">';
			echo role_box($group_id,'role_id',$row_memb['role_id']);
			echo '</td><td><input type="submit" name="updateuser" value="'._("Update").'" />';
			echo '</td><td><input type="submit" name="rmuser" value="'._("Remove").'" />
                        </td>
			</tr>
                </form>';
}
echo '</tbody></table>';
echo $HTML->boxBottom(); 
?></td>
	</tr>

</table>

<?php

project_admin_footer(array());

?>
