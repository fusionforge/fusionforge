<?php
/**
 * Project Members Information
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2014, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromGet("group_id");
$form_grp = getIntFromGet("form_grp");

if (!$group_id && $form_grp) {
	$group_id = $form_grp;
}

session_require_perm('project_read', $group_id);

site_project_header(array('title'=>_('Project Member List'),'group'=>$group_id,'toptab'=>'memberlist'));

echo '<p>' . _('If you would like to contribute to this project by becoming a member, contact one of the project admins, designated in bold text below.') . '</p>';

// beginning of the user descripion block
$project = group_get_object($group_id);
$project_stdzd_uri = util_make_url_g ($project->getUnixName(), $group_id);
$usergroup_stdzd_uri = $project_stdzd_uri.'members/';
print '<div about="'. $usergroup_stdzd_uri .'" typeof="sioc:UserGroup">';
print '<span rel="http://www.w3.org/2002/07/owl#sameAs" resource=""></span>';
print '<span rev="sioc:has_usergroup" resource="'. $project_stdzd_uri . '"></span>';
print '</div>';

$title_arr=array();
$title_arr[]=_('Member');
$title_arr[]=_('User Name');
$title_arr[]=_('Role(s)/Position(s)');
if(forge_get_config('use_people')) {
	$title_arr[]=_('Skills');
}

echo $GLOBALS['HTML']->listTableTop ($title_arr);

// list members
$members = $project->getUsers() ;

$i=0;
foreach ($members as $user) {
	echo '<tr '.$HTML->boxGetAltRowStyle($i++).'>'."\n";
	// RDFa
	$member_uri = util_make_url_u ($user->getUnixName(),$user->getID());
	echo "		<td>\n";
	print '<div about="'. $member_uri .'" typeof="sioc:UserAccount">';
	print '<span rev="sioc:has_member" resource="'. $usergroup_stdzd_uri .'"></span>';
	print '<span property="sioc:name" content="'. $user->getUnixName() .'"></span>';
	if ( RBACEngine::getInstance()->isActionAllowedForUser($user,'project_admin',$project->getID())) {
//                echo '<div rev="doap:developer" typeof="doap:Project" xmlns:doap="http://usefulinc.com/ns/doap#">';
		echo '<strong>'.$user->getRealName().'</strong>';
//                echo '</div>';
	} else {
//		echo '<div rev="doap:maintainer" typeof="doap:Project" xmlns:doap="http://usefulinc.com/ns/doap#">';
		echo $user->getRealName();
//                echo '</div>';
	}
	echo "</div>\n";
	echo '</td>';

	/*
        print '<span property ="dc:Identifier" content="'.$user->getID().'">';
        echo '</span>';
        print '<span property="foaf:accountName" content="'.$user->getUnixName().'">';
        echo '</span>';
        print '<span property="fusionforge:has_job" content="'.$role_string.'">';
        echo '</span>';*/

	$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($user) ;
	sortRoleList ($roles) ;
	$role_names = array () ;
	foreach ($roles as $role) {
		if ($role->getHomeProject() && $role->getHomeProject()->getID() == $project->getID()) {
			$role_names[] = $role->getName() ;
		}
	}
	$role_string = implode (', ', $role_names) ;

	echo '<td>';
	echo util_display_user($user->getUnixName(),$user->getID(),$user->getUnixName(), 's');
	echo '</td>';
	echo '<td align="center">'.$role_string.'</td>';
	if(forge_get_config('use_people')) {
		echo '<td align="center">'.util_make_link ('/people/viewprofile.php?user_id='.$user->getID(),_('View')).'</td>';
	}
   	echo '</tr>';
}
// end of community member description block
echo $GLOBALS['HTML']->listTableBottom();

site_project_footer(array());
