<?php
/**
 * Role Editing Page
 *
 * Copyright 2004 (c) GForge LLC
 * Copyright 2010, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'include/Role.class.php';
require_once $gfcommon.'include/RoleObserver.class.php';
require_once $gfcommon.'include/rbac_texts.php';

$group_id = getIntFromRequest('group_id');
session_require_perm ('project_admin', $group_id) ;

$role_id = getStringFromRequest('role_id');
$data = getStringFromRequest('data');

$group = group_get_object($group_id);

if (getStringFromRequest('delete')) {
	session_redirect('/project/admin/roledelete.php?group_id='.$group_id.'&role_id='.$role_id);
}

//
//	The observer is a special role, which is actually
//	just controlling the is_public/allow anon flags
//
//	Get observer role instead of regular role
//
if ($role_id=='observer') {
	$role = new RoleObserver($group);
	if (!$role || !is_object($role)) {
		exit_error('Error','Could Not Get RoleObserver');
	} elseif ($role->isError()) {
		exit_error('Error',$role->getErrorMessage());
	}

	if (getStringFromRequest('submit')) {
		if (!$role->update($data)) {
			$feedback = $role->getErrorMessage();
		} else {
			$feedback = _('Successfully Updated Role');
		}
	}
} else {
	if (USE_PFO_RBAC) {
		if (getStringFromRequest('add')) {
			$role_name = trim(getStringFromRequest('role_name')) ;
			$role = new Role ($group) ;
			$role_id=$role->createDefault($role_name) ;
		} else {
			$role = RBACEngine::getInstance()->getRoleById($role_id) ;
		}
	} else {
		$role = new Role($group,$role_id);
	}
	if (!$role || !is_object($role)) {
		exit_error('Error',_('Could Not Get Role'));
	} elseif ($role->isError()) {
		exit_error('Error',$role->getErrorMessage());
	}

	$old_data = $role->getSettingsForProject ($group) ;
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
		if (($role->getHomeProject() != NULL)
		    && ($role->getHomeProject()->getID() == $group_id)) {
			$role_name = trim(getStringFromRequest('role_name'));
		} else {
			$role_name = $role->getName() ;
		}
		if (!$role_name) {
			$feedback .= ' Missing Role Name ';
		} else {
			if (!$role_id) {
				$role_id=$role->create($role_name,$data);
				if (!$role_id) {
					$feedback .= $role->getErrorMessage();
				} else {
					$feedback = _('Successfully Created New Role');
				}
			} else {
				if (!$role->update($role_name,$data)) {
					$feedback .= $role->getErrorMessage();
				} else {
					$feedback = _('Successfully Updated Role');
				}
			}
			//plugin webcal
			//change assistant for webcal
			$params = getIntFromRequest('group_id');
			plugin_hook('change_cal_permission_auto',$params);
		}
	}
}

project_admin_header(array('title'=>_('Edit Role'),'group'=>$group_id));

//
//	If observer role, show title
//
if ($role_id=='observer') {
	echo '<h1>'._('Edit Observer').'</h1>';
	echo _('Use this page to edit the permissions and access levels of non-members of your project. Non-members includes users who are not logged in.');
} else {
	if (!$role_id) {
		echo '<h1>'._('New Role').'</h1>';
	} else {
		echo '<h1>'._('Edit Role').'</h1>';
	}
	if (USE_PFO_RBAC) {
		echo _('Use this page to edit the permissions attached to each role.  Note that each role has at least as much access as the Anonymous and LoggedIn roles.  For example, if the the Anonymous role has read access to a forum, all other roles will have it too.');
	} else {
		echo _('Use this page to edit your project\'s Roles. Note that each member has at least as much access as the Observer. For example, if the Observer can read CVS, so can any other role in the project.');
	}
}

echo '
<p>
<form action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;role_id='. $role_id .'" method="post">';

if (USE_PFO_RBAC) {
	if ($role->getHomeProject() == NULL) {
		echo '<p><strong>'._('Role Name').'</strong><br />' ;
		printf (_('%s (global role)'),
			$role->getName ()) ;
	} elseif ($role->getHomeProject()->getID() != $group_id) {
		echo '<p><strong>'._('Role Name').'</strong><br />' ;
		printf (_('%s (in project %s)'),
			$role->getName (),
			$role->getHomeProject()->getPublicName()) ;
	} else {
		echo '<p><strong>'._('Role Name').'</strong><br /><input type="text" name="role_name" value="'.$role->getName().'">' ;
	}
	echo '</p>';
} else {
	if ($role_id != 'observer') {
		echo '<p><strong>'._('Role Name').'</strong><br />
	<input type="text" name="role_name" value="'.$role->getName().'" />
	</p>';
	}
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
if (USE_PFO_RBAC) {
	$keys = array_keys($role->getSettingsForProject ($group)) ;
	$keys2 = array () ;
	foreach ($keys as $key) {
		if (!in_array ($key, $role->global_settings)) {
			$keys2[] = $key ;
		}
	}
	$keys = $keys2 ;
} else {
	$keys = array_keys($role->role_values);
}
for ($i=0; $i<count($keys); $i++) {
        if ((!$group->usesForum() && preg_match("/forum/", $keys[$i])) ||
                (!$group->usesTracker() && preg_match("/tracker/", $keys[$i])) ||
                (!$group->usesPM() && preg_match("/pm/", $keys[$i])) ||
                (!$group->usesFRS() && preg_match("/frs/", $keys[$i])) ||
                (!$group->usesSCM() && preg_match("/scm/", $keys[$i])) ||
                (!$group->usesDocman() && preg_match("/docman/", $keys[$i]))) {

                //We don't display modules not used


//
//	Handle forum settings for all roles
//
	} elseif ($keys[$i] == 'forum' || $keys[$i] == 'forumpublic' || $keys[$i] == 'forumanon') {

		if ($keys[$i] == 'forumanon') {
			//skip as we have special case below
		} else {
			$res=db_query_params ('SELECT group_forum_id,forum_name,is_public,allow_anonymous 
				FROM forum_group_list WHERE group_id=$1',
			array($group_id));
			for ($q=0; $q<db_numrows($res); $q++) {
				//
				//	Special cases - when going through the keys, we want to show trackeranon
				//	on the same line as tracker public
				//
				if ($keys[$i] == 'forumpublic') {
					$txt=' &nbsp; '.html_build_select_box_from_assoc(
					$role->getRoleVals('forumanon'),
					"data[forumanon][".db_result($res,$q,'group_forum_id')."]",
					$role->getVal('forumanon',db_result($res,$q,'group_forum_id')),
					false, false );
				} else {
					$txt='';
				}
				echo '<tr ' . $HTML->boxGetAltRowStyle($j++) . '>
				<td>'.$rbac_edit_section_names[$keys[$i]].'</td>
				<td>'.db_result($res,$q,'forum_name').'</td>
				<td>'.html_build_select_box_from_assoc(
					$role->getRoleVals($keys[$i]), 
					"data[".$keys[$i]."][".db_result($res,$q,'group_forum_id')."]", 
					$role->getVal($keys[$i],db_result($res,$q,'group_forum_id')), 
					false, false ). $txt .'</td></tr>';
			}
		}
//
//	Handle task mgr settings for all roles
//
	} elseif ($keys[$i] == 'pm' || $keys[$i] == 'pmpublic') {

		$res=db_query_params ('SELECT group_project_id,project_name,is_public 
			FROM project_group_list WHERE group_id=$1',
			array($group_id));
		for ($q=0; $q<db_numrows($res); $q++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>'.$rbac_edit_section_names[$keys[$i]].'</td>
			<td>'.db_result($res,$q,'project_name').'</td>
			<td>'.html_build_select_box_from_assoc(
				$role->getRoleVals($keys[$i]), 
				"data[".$keys[$i]."][".db_result($res,$q,'group_project_id')."]", 
				$role->getVal($keys[$i],db_result($res,$q,'group_project_id')), 
				false, false ).'</td></tr>';
		}

//
//	Handle tracker settings for all roles
//
	} elseif ($keys[$i] == 'tracker' || $keys[$i] == 'trackerpublic' || $keys[$i] == 'trackeranon') {

		if ($keys[$i] == 'trackeranon') {
			//skip as we have special case below
		} else {
			$res=db_query_params ('SELECT group_artifact_id,name,is_public,allow_anon
				FROM artifact_group_list WHERE group_id=$1',
			array($group_id));
			for ($q=0; $q<db_numrows($res); $q++) {
				//
				//	Special cases - when going through the keys, we want to show trackeranon
				//	on the same line as tracker public
				//
				if ($keys[$i] == 'trackerpublic') {
					$txt = ' &nbsp; '.html_build_select_box_from_assoc(
					$role->getRoleVals('trackeranon'),
					"data[trackeranon][".db_result($res,$q,'group_artifact_id')."]",
					$role->getVal('trackeranon',db_result($res,$q,'group_artifact_id')),
					false, false );
				} else {
					$txt='';
				}
				echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
				<td>'.$rbac_edit_section_names[$keys[$i]].'</td>
				<td>'.db_result($res,$q,'name').'</td>
				<td>'.html_build_select_box_from_assoc(
					$role->getRoleVals($keys[$i]), 
					"data[".$keys[$i]."][".db_result($res,$q,'group_artifact_id')."]", 
					$role->getVal($keys[$i],db_result($res,$q,'group_artifact_id')), 
					false, false ). $txt .'</td></tr>';
			}
		}

//
//	File release system - each package can be public/private
//
	} elseif ($keys[$i] == 'frspackage') {

		$res=db_query_params ('SELECT package_id,name,is_public 
			FROM frs_package WHERE group_id=$1',
			array($group_id));
		for ($q=0; $q<db_numrows($res); $q++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>'.$rbac_edit_section_names[$keys[$i]].'</td>
			<td>'.db_result($res,$q,'name').'</td>
			<td>'.html_build_select_box_from_assoc(
				$role->getRoleVals($keys[$i]), 
				"data[".$keys[$i]."][".db_result($res,$q,'package_id')."]", 
				$role->getVal($keys[$i],db_result($res,$q,'package_id')), 
				false, false ).'</td></tr>';
		}

//
//	Handle all other settings for all roles
//
	} else {

		echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
		<td colspan="2"><strong>'.$rbac_edit_section_names[$keys[$i]].'</strong></td>
		<td>';
        	if (USE_PFO_RBAC) {
			echo html_build_select_box_from_assoc($role->getRoleVals($keys[$i]), "data[".$keys[$i]."][$group_id]", $role->getVal($keys[$i],$group_id), false, false ) ;
                } else {
			echo html_build_select_box_from_assoc($role->getRoleVals($keys[$i]), "data[".$keys[$i]."][0]", $role->getVal($keys[$i],0), false, false ) ;
                }
		echo '</td>
		</tr>';

	}

}

echo $HTML->listTableBottom();

echo '<p><input type="submit" name="submit" value="'._('Submit').'" /></p>
</form>';

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
