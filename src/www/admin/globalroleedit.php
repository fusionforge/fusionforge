<?php
/**
 * Role Editing Page
 *
 * Copyright 2010, Roland Mas
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfwww.'include/role_utils.php';

site_admin_header(array('title'=>_('Site Admin')));

$role_id = getIntFromRequest('role_id');
$data = getStringFromRequest('data');

if (getStringFromRequest('add')) {
	$role_name = trim(getStringFromRequest('role_name')) ;
	$role = new Role (NULL) ;
	$role_id=$role->createDefault($role_name) ;
} else {
	$role = RBACEngine::getInstance()->getRoleById($role_id) ;
}

if (!$role || !is_object($role)) {
	exit_error(_('Could Not Get Role'),'admin');
} elseif ($role->isError()) {
	exit_error($role->getErrorMessage(),'admin');
}

$old_data = $role->getGlobalSettings () ;
$new_data = array () ;

if (!is_array ($data)) {
	$data = array () ;
}
foreach ($old_data as $section => $values) {
	if (!array_key_exists ($section, $data)) {
		continue ;
	}
	foreach ($values as $ref_id => $val) {
		if (!array_key_exists ($ref_id, $data[$section])) {
			continue ;
		}
		$new_data[$section][$ref_id] = $data[$section][$ref_id] ;
	}
}
$data = $new_data ;

if (getStringFromRequest('submit')) {
	if ($role instanceof RoleExplicit) {
		$role_name = trim(getStringFromRequest('role_name'));
		$public = getIntFromRequest('public') ? true : false ;
	} else {
		$role_name = $role->getName() ;
		$public = $role->isPublic () ;
	}
	if (!$role_name) {
		$warning_msg .= ' Missing Role Name ';
	} else {
		if (!$role_id) {
			$role_id=$role->create($role_name,$data);
			if (!$role_id) {
				$error_msg .= $role->getErrorMessage();
			} else {
				$feedback = _('Successfully Created New Role');
			}
		} else {
			if ($role instanceof RoleExplicit) {
				$role->setPublic($public) ;
			}
			if (!$role->update($role_name,$data)) {
				$error_msg .= $role->getErrorMessage();
			} else {
				$feedback = _('Successfully Updated Role');
			}
		}
	}
}

if (getStringFromRequest('adduser')) {
	if ($role instanceof RoleExplicit) {
		$user_name = getStringFromRequest ('form_unix_name') ;
		$u = user_get_object_by_name ($user_name) ;
		if ($u && $u instanceof GFUser && !$u->isError()) {
			if ($role->addUser ($u)) {
				$feedback .= _('User added successfully') ;
			} else {
				$error_msg .= _("Error while adding user to role") ;
			}
		}
	} else {
		$error_msg .= _("Can't add user to this type of role") ;
	}
}

if (getStringFromRequest('rmuser')) {
	if ($role instanceof RoleExplicit) {
		$user_id = getIntFromRequest ('user_id') ;
		$u = user_get_object ($user_id) ;
		if ($u && $u instanceof GFUser && !$u->isError()) {
			if ($role->removeUser ($u)) {
				$feedback .= _('User removed successfully') ;
			} else {
				$error_msg .= _("Error while removing user from role") ;
			}
		}
	} else {
		$error_msg .= _("Can't remove user from this type of role") ;
	}
}


if ($role instanceof RoleExplicit) {
	$users = $role->getUsers () ;
	if (count ($users) > 0) {
		echo '<p><strong>'._('Current users with this role').'</strong></p>' ;

		echo '<table><thead><tr>';
		echo '<th>'._('User name').'</th>';
		echo '<th>'._('Remove').'</th>';
		echo '</tr></thead><tbody>';
		
		foreach ($users as $user) {
			echo '
		<form action="'.util_make_url('/admin/globalroleedit.php').'" method="post">
		<input type="hidden" name="role_id" value="'.$role_id.'">
                        <tr>
                        <td style="white-space: nowrap;">
			  <input type="hidden" name="user_id" value="'.$user->getID().'" />
			  <a href="/users/'.$user->getUnixName().'">';
			$display = $user->getRealName();
			if (!empty($display)) {
				echo $user->getRealName();
			} else {
				echo $user->getUnixName();
			}
			echo '</a>
			</td>';
			echo '<td><input type="submit" name="rmuser" value="'._("Remove").'" />
                        </td>
			</tr>
                </form>';
			echo '</tbody></table>';
		}
	} else {
		echo '<p><strong>'._('No users currently have this role').'</strong></p>' ;
	}

			?>
		<form
			action="<?php echo util_make_url('/admin/globalroleedit.php'); ?>"
			method="post">
		<p><input type="text"
			name="form_unix_name" size="10" value="" />
		<input type="submit" name="adduser"
			value="<?php echo _("Add User") ?>" />
		<input type="hidden" name="role_id" value="<?php echo $role_id; ?>">
		</p>
		</form>
<?php
}
		
echo '
<p>
<form action="'.util_make_url('/admin/globalroleedit.php').'" method="post">';
echo '<input type="hidden" name="role_id" value="'.$role_id.'">' ;
		
if ($role instanceof RoleExplicit) {
	echo '<p><strong>'._('Role Name').'</strong><br /><input type="text" name="role_name" value="'.$role->getName().'"></p>';
	echo '<input type="checkbox" name="public" value="1"' ;
	if ($role->isPublic()) {
		echo ' checked' ;
	}
	echo '> '._('Public role (can be referenced by projects)').'</p>' ;
} else {
	echo '<p><strong>'._('Role Name').'</strong><br />'.$role->getName().'</p>';
}

$titles[]=_('Section');
$titles[]=_('Subsection');
$titles[]=_('Setting');

setup_rbac_strings () ;

echo $HTML->listTableTop($titles);

//
//	Get the keys for this role and interate to build page
//
//	Everything is built on the multi-dimensial arrays in the Role object
//
$j = 0;

$keys = array_keys($role->getGlobalSettings ()) ;
$keys2 = array () ;
foreach ($keys as $key) {
	if (in_array ($key, $role->global_settings)) {
		$keys2[] = $key ;
	}
}
$keys = $keys2 ;

for ($i=0; $i<count($keys); $i++) {
	echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
		<td colspan="2"><strong>'.$rbac_edit_section_names[$keys[$i]].'</strong></td>
		<td>';
	echo html_build_select_box_from_assoc($role->getRoleVals($keys[$i]), "data[".$keys[$i]."][-1]", $role->getVal($keys[$i],-1), false, false ) ;
	echo '</td>
		</tr>';
	
}

echo $HTML->listTableBottom();

echo '<p><input type="submit" name="submit" value="'._('Submit').'" /></p>
</form>';

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
