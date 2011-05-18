<?php
/**
 * Project Admin Users Page
 *
 * This page contains administrative information for the project as well
 * as allows to manage it. This page should be accessible to all project
 * members, but only admins may perform most functions.
 *
 * Copyright 2004 GForge, LLC
 * Copyright 2006 federicot
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfwww.'include/role_utils.php';
require_once $gfcommon.'include/account.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$group_id = getIntFromRequest('group_id');

session_require_perm ('project_admin', $group_id) ;

// get current information
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
}

// Add hook to replace users managements by a plugin.
$html_code = array();
if (plugin_hook_listeners("project_admin_users") > 0) {
	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("project_admin_users", $hook_params);
}

function cache_external_roles () {
	global $used_external_roles, $unused_external_roles, $group, $group_id;

	if (USE_PFO_RBAC) {
		$unused_external_roles = array () ;
		foreach (RBACEngine::getInstance()->getPublicRoles() as $r) {
			$grs = $r->getLinkedProjects () ;
			$seen = false ;
			foreach ($grs as $g) {
				if ($g->getID() == $group_id) {
					$seen = true ;
					break ;
				}
			}
			if (!$seen) {
				$unused_external_roles[] = $r ;
			}
		}
		$used_external_roles = array () ;
		foreach ($group->getRoles() as $r) {
			if ($r->getHomeProject() == NULL
			    || $r->getHomeProject()->getID() != $group_id) {
				$used_external_roles[] = $r ;
			}
		}

		sortRoleList ($used_external_roles, $group, 'composite') ;
		sortRoleList ($unused_external_roles, $group, 'composite') ;

	}
}

cache_external_roles () ;

if (getStringFromRequest('submit')) {
	if (getStringFromRequest('adduser')) {
		/*
			add user to this project
			*/
		$form_unix_name = getStringFromRequest('form_unix_name');
		$user_object = &user_get_object_by_name($form_unix_name);
		if ($user_object === false) {
			$warning_msg .= _('No Matching Users Found');
		} else {
			$role_id = getIntFromRequest('role_id');
			if (!$role_id) {
				$warning_msg .= _('Role not selected');
			} else {
				$user_id = $user_object->getID();
				if (!$group->addUser($form_unix_name,$role_id)) {
					$error_msg = $group->getErrorMessage();
				} else {
					$feedback = _("Member Added Successfully");
					//if the user have requested to join this group
					//we should remove him from the request list
					//since it has already been added
					$gjr=new GroupJoinRequest($group,$user_id);
					if ($gjr || is_object($gjr) || !$gjr->isError()) {
						$gjr->delete(true);
					}
				}
			}
		}
	} else if (getStringFromRequest('rmuser')) {
		/* remove a member from this project */
		$user_id = getIntFromRequest('user_id');
		$role_id = getIntFromRequest('role_id');
		$role = RBACEngine::getInstance()->getRoleById($role_id) ;
		if ($role->getHomeProject() == NULL) {
			session_require_global_perm ('forge_admin') ;
		} else {
			session_require_perm ('project_admin', $role->getHomeProject()->getID()) ;
		}
		if (!$role->removeUser (user_get_object ($user_id))) {
			$error_msg = $role->getErrorMessage() ;
		} else {
			$feedback = _("Member Removed Successfully");
		}
	} else if (getStringFromRequest('updateuser')) {
		/* Adjust Member Role */
		$user_id = getIntFromRequest('user_id');
		$role_id = getIntFromRequest('role_id');
		if (! $role_id) {
			$error_msg = _("Role not selected");
		}
		else {
			if (!$group->updateUser($user_id,$role_id)) {
				$error_msg = $group->getErrorMessage();
			} else {
				$feedback = _("Member Updated Successfully");
			}
		}
	} elseif (getStringFromRequest('acceptpending')) {
		/*
			add user to this project
			*/
		$role_id = getIntFromRequest('role_id');
		if (!$role_id) {
			$warning_msg .= _("Role not selected");
		} else {
			$form_userid = getIntFromRequest('form_userid');
			$form_unix_name = getStringFromRequest('form_unix_name');
			if (!$group->addUser($form_unix_name,$role_id)) {
				$error_msg = $group->getErrorMessage();
			} else {
				$gjr=new GroupJoinRequest($group,$form_userid);
				if (!$gjr || !is_object($gjr) || $gjr->isError()) {
					$error_msg = _('Error Getting GroupJoinRequest');
				} else {
					$gjr->delete(true);
				}
				$feedback = _("Member Added Successfully");
			}
		}
	} elseif (getStringFromRequest('rejectpending')) {
		/*
			reject adding user to this project
			*/
		$form_userid = getIntFromRequest('form_userid');
		$gjr=new GroupJoinRequest($group,$form_userid);
		if (!$gjr || !is_object($gjr) || $gjr->isError()) {
			$error_msg .= _('Error Getting GroupJoinRequest');
		} else {
			if (!$gjr->reject()) {
				$error_msg = $gjr->getErrorMessage();
			} else {
				$feedback .= 'Rejected';
			}
		}
	} else if (getStringFromRequest('linkrole')) {
		/* link a role to this project */
		if (USE_PFO_RBAC) {
			$role_id = getIntFromRequest('role_id');
			foreach ($unused_external_roles as $r) {
				if ($r->getID() == $role_id) {
					if (!$r->linkProject($group)) {
						$error_msg = $r->getErrorMessage();
					} else {
						$feedback = _("Role linked successfully");
						cache_external_roles () ;
					}
				}
			}
		}
	} else if (getStringFromRequest('unlinkrole')) {
		/* unlink a role from this project */
		if (USE_PFO_RBAC) {
			$role_id = getIntFromRequest('role_id');
			foreach ($used_external_roles as $r) {
				if ($r->getID() == $role_id) {
					if (!$r->unLinkProject($group)) {
						$error_msg = $r->getErrorMessage();
					} else {
						$feedback = _("Role unlinked successfully");
						cache_external_roles () ;
					}
				}
			}
		}
	}
}

$group->clearError();

project_admin_header(array('title'=>sprintf(_('Members of %s'), $group->getPublicName()),'group'=>$group->getID()));

?>

<table width="100%" cellpadding="2" cellspacing="2">
	<tr valign="top">
		<td width="50%"><?php
		//
		//	Pending requests
		//
		$reqs =& get_group_join_requests($group);
		if (count($reqs) > 0) {
			echo $HTML->boxTop(_("Pending Membership Requests"));
			for ($i=0; $i<count($reqs); $i++) {
				$user =& user_get_object($reqs[$i]->getUserId());
				if (!$user || !is_object($user)) {
					echo "Invalid User";
				}
				?>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>"
			method="post">
		<table width="100%">
			<tr>
				<td style="white-space: nowrap;"><input type="hidden" name="submit"
					value="y" /> <input type="hidden" name="form_userid"
					value="<?php echo $user->getId(); ?>" /> <input type="hidden"
					name="form_unix_name" value="<?php echo $user->getUnixName(); ?>" /><a
					href="/users/<?php echo $user->getUnixName(); ?>"><?php echo $user->getRealName(); ?></a>
				</td>
				<td style="white-space: nowrap; text-align: right;"><?php echo role_box($group_id,'role_id'); ?>
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

			echo $HTML->boxMiddle(_("Add Member"));
		} else {
			echo $HTML->boxTop(_("Add Member"));
		}

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
			value="<?php echo _("Add Member") ?>" />
		</p>
		</form>
		<p><a
                        href="massadd.php?group_id=<?php echo $group_id; ?>"><?php echo _("Add Users From List"); ?></a></p>

			<?php
		}

		echo $HTML->boxMiddle(_("Current Project Members"));

		/*

		Show the members of this project

		*/

$members = $group->getUsers() ;

echo '<table width="100%"><thead><tr>';
echo '<th>'._('User name').'</th>';
echo '<th>'._('Role').'</th>';
echo '<th>'._('Action').'</th>';
echo '</tr></thead><tbody>';

foreach ($members as $user) {
	$roles = array () ;
	foreach (RBACEngine::getInstance()->getAvailableRolesForUser ($user) as $role) {
		if ($role->getHomeProject() && $role->getHomeProject()->getID() == $group->getID()) {
			$roles[] = $role ;
		}
	}

	sortRoleList ($roles) ;

	$seen = false ;
	foreach ($roles as $role) {
		echo '<tr>' ;
		if (!$seen) {
			echo '<td style="white-space: nowrap;" rowspan="'.(count($roles)+1).'"><a href="/users/'.$user->getUnixName().'">';
			$display = $user->getRealName();
			if (!empty($display)) {
				echo $user->getRealName();
			} else {
				echo $user->getUnixName();
			}
			echo '</a></td>';
			$seen = true ;
		}

		echo '
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
			  <input type="hidden" name="submit" value="y" />
			  <input type="hidden" name="user_id" value="'.$user->getID().'" />
			  <input type="hidden" name="group_id" value="'. $group_id .'" />' ;

		echo '<td style="white-space: nowrap;">';
		echo $role->getName() ;
		echo '<input type="hidden" name="role_id" value="'.$role->getID().'" />' ;
		echo '</td><td><input type="submit" name="rmuser" value="'._("Remove").'" />
                        </td>
                </form></tr>';
	}

	echo '
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
			  <input type="hidden" name="submit" value="y" />
			  <input type="hidden" name="form_unix_name" value="'.$user->getUnixName().'" />
			  <input type="hidden" name="group_id" value="'. $group_id .'" />' ;

	echo '<tr><td style="white-space: nowrap;">';
	echo role_box($group_id,'role_id',$role->getID());
	echo '</td><td><input type="submit" name="adduser" value="'._("Grant extra role").'" />
                        </td>
                </form></tr>';
}
echo '</tbody></table>';

		echo $HTML->boxBottom();

		?></td>
		<td><?php







		//
		//      RBAC Editing Functions
		//
		echo $HTML->boxTop(_("Edit Roles"));
echo '<table width="100%"><thead><tr>';
echo '<th>'._('Role name').'</th>';
echo '<th>'._('Action').'</th>';
echo '</tr></thead><tbody>';

if (!USE_PFO_RBAC) {
		echo '
        <form action="roleedit.php?group_id='. $group_id .'&amp;role_id=observer" method="post">
        <p><input type="submit" name="edit" value="'._("Edit Observer").'" /></p>
        </form>';
}

$roles = $group->getRoles() ;
sortRoleList ($roles, $group, 'composite') ;

foreach ($roles as $r) {
	echo '<tr><form action="roleedit.php?group_id='. $group_id .'" method="post">' ;
	echo '<input type="hidden" name="role_id" value="'.$r->getID().'" />' ;
	echo '<td>'.$r->getDisplayableName($group).'</td>' ;
	echo '<td><input type="submit" name="edit" value="'._("Edit Permissions").'" /></td>' ;
	echo '</form></tr>' ;
}

echo '<tr>' ;
echo '<form action="roleedit.php?group_id='. $group_id .'" method="post">';
echo '<td><input type="text" name="role_name" size="10" value="" /></td>';
echo '<td><input type="submit" name="add" value="'._("Create Role").'" /></td>' ;
echo '</form></tr>';

echo '</table>' ;


if (USE_PFO_RBAC) {
	if (count ($used_external_roles)) {
		echo $HTML->boxMiddle(_("Currently used external roles"));
echo '<table width="100%"><thead><tr>';
echo '<th>'._('Role name').'</th>';
echo '<th>'._('Action').'</th>';
echo '</tr></thead><tbody>';

foreach ($used_external_roles as $r) {
	echo '<tr><form action="'.getStringFromServer('PHP_SELF').'" method="post">' ;
	echo '<input type="hidden" name="submit" value="y" />' ;
	echo '<input type="hidden" name="role_id" value="'.$r->getID().'" />' ;
	echo '<input type="hidden" name="group_id" value="'.$group_id.'" />' ;
	echo '<td>'.$r->getDisplayableName($group).'</td>' ;
	echo '<td><input type="submit" name="unlinkrole" value="'._("Unlink Role").'" /></td>' ;
	echo '</form></tr>' ;
}
echo '</table>' ;
	}

	if (count ($unused_external_roles)) {
		echo $HTML->boxMiddle(_("Available external roles"));
echo '<table width="100%"><thead><tr>';
echo '<th>'._('Role name').'</th>';
echo '<th>'._('Action').'</th>';
echo '</tr></thead><tbody>';

$ids = array () ;
$names = array () ;
foreach ($unused_external_roles as $r) {
	$ids[] = $r->getID() ;
	$names[] = $r->getDisplayableName($group) ;
}
echo '<tr><form action="'.getStringFromServer('PHP_SELF').'" method="post">' ;
echo '<input type="hidden" name="submit" value="y" />' ;
echo '<input type="hidden" name="group_id" value="'.$group_id.'" />' ;
echo '<td>' ;
echo html_build_select_box_from_arrays($ids,$names,'role_id','',false,'',false,'');
echo '</td><td>' ;
echo '<input type="submit" name="linkrole" value="'._("Link external role").'" /></form><br />' ;
echo '</td>' ;
echo '</form></tr>';
echo '</table>' ;
	}
}

echo $HTML->boxBottom();
?></td>
	</tr>

</table>

<?php

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
