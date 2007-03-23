<?php
/**
 * Role Editing Page
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-03-16
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
require_once('common/include/Role.class');
require_once('common/include/RoleObserver.class');

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$role_id = getStringFromRequest('role_id');
$data = getStringFromRequest('data');

//
//	The observer is a special role, which is actually
//	just controlling the is_public/allow anon flags
//
//	Get observer role instead of regular role
//
if ($role_id=='observer') {
	$role = new RoleObserver(group_get_object($group_id));
	if (!$role || !is_object($role)) {
		exit_error('Error','Could Not Get RoleObserver');
	} elseif ($role->isError()) {
		exit_error('Error',$role->getErrorMessage());
	}

	if (getStringFromRequest('submit')) {
		if (!$role->update($data)) {
			$feedback .= $role->getErrorMessage();
		} else {
			$feedback .= ' Successfully Updated Role ';
		}
	}
} else {
	$role = new Role(group_get_object($group_id),$role_id);
	if (!$role || !is_object($role)) {
		exit_error('Error','Could Not Get Role');
	} elseif ($role->isError()) {
		exit_error('Error',$role->getErrorMessage());
	}

	if (getStringFromRequest('submit')) {
		$role_name = getStringFromRequest('role_name');
		if (!$role_id) {
			$role_id=$role->create($role_name,$data);
			if (!$role_id) {
				$feedback .= $role->getErrorMessage();
			} else {
				$feedback .= ' Successfully Created New Role ';
			}
		} else {
			if (!$role->update($role_name,$data)) {
				$feedback .= $role->getErrorMessage();
			} else {
				$feedback .= ' Successfully Updated Role ';
			}
		}
		//plugin webcal
			//change assistant for webcal
			$params = getIntFromRequest('group_id');
			plugin_hook('change_cal_permission_auto',$params);	
	}
}

project_admin_header(array('title'=>_('Edit Role'),'group'=>$group_id));

//
//	If observer role, show title
//
if ($role_id=='observer') {
	echo '<h2>'._('Edit Observer').'</h2>';
	echo _('Use this page to edit the permissions and access levels of non-members of your project. Non-members includes users who are not logged in.');
} else {
	if (!$role_id) {
		echo '<h2>'._('New Role').'</h2>';
	} else {
		echo '<h2>'._('Edit Role').'</h2>';
	}
	echo _('Use this page to edit your project\'s Roles. Note that each member has at least as much access as the Observer. For example, if the Observer can read CVS, so can any other role in the project.');

}

echo '
<p>
<form action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&role_id='. $role_id .'" method="post">';

if ($role_id != 'observer') {
	echo '<strong>'._('Role Name').'</strong><br />
	<input type="text" name="role_name" value="'.$role->getName().'">
	<p>';
}

$titles[]=_('Section');
$titles[]=_('Subsection');
$titles[]=_('Setting');

echo $HTML->listTableTop($titles);

//
//	Get the keys for this role and interate to build page
//
//	Everything is built on the multi-dimensial arrays in the Role object
//
$j = 0;
$keys = array_keys($role->role_values);
for ($i=0; $i<count($keys); $i++) {


//
//	Handle forum settings for all roles
//
	if ($keys[$i] == 'forum' || $keys[$i] == 'forumpublic' || $keys[$i] == 'forumanon') {

		if ($keys[$i] == 'forumanon') {
			//skip as we have special case below
		} else {
			$res=db_query("SELECT group_forum_id,forum_name,is_public,allow_anonymous 
				FROM forum_group_list WHERE group_id='$group_id'");
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
				echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
				<td>'.$Language->getText('rbac_edit',$keys[$i]).'</td>
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

		$res=db_query("SELECT group_project_id,project_name,is_public 
			FROM project_group_list WHERE group_id='$group_id'");
		for ($q=0; $q<db_numrows($res); $q++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>'.$Language->getText('rbac_edit',$keys[$i]).'</td>
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
			$res=db_query("SELECT group_artifact_id,name,is_public,allow_anon
				FROM artifact_group_list WHERE group_id='$group_id'");
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
				<td>'.$Language->getText('rbac_edit',$keys[$i]).'</td>
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

		$res=db_query("SELECT package_id,name,is_public 
			FROM frs_package WHERE group_id='$group_id'");
		for ($q=0; $q<db_numrows($res); $q++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j++) . '>
			<td>'.$Language->getText('rbac_edit',$keys[$i]).'</td>
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
		<td><strong>'.$Language->getText('rbac_edit',$keys[$i]).'</strong></td>
		<td>-</td>
		<td>'.html_build_select_box_from_assoc($role->getRoleVals($keys[$i]), "data[".$keys[$i]."][0]", $role->getVal($keys[$i],0), false, false ).'</td>
		</tr>';

	}

}

echo $HTML->listTableBottom();

echo '<input type="submit" name="submit" value="'._('Submit').'">
</form>';

project_admin_footer(array());

?>
