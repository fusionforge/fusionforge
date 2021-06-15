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

global $HTML;

$group_id = getIntFromGet("group_id");
$form_grp = getIntFromGet("form_grp");

if (!$group_id && $form_grp) {
	$group_id = $form_grp;
}

session_require_perm('members', $group_id);

site_project_header(array('title'=>_('Project Member List'),'group'=>$group_id,'toptab'=>'memberlist'));

echo html_e('p', array(), _('If you would like to contribute to this project by becoming a member, contact one of the project admins, designated in bold text below.'));

// beginning of the user descripion block
$project = group_get_object($group_id);
$project_stdzd_uri = util_make_url_g ($project->getUnixName(), $group_id);
$usergroup_stdzd_uri = $project_stdzd_uri.'members/';
$content = html_e('span', array('rel' => 'http://www.w3.org/2002/07/owl#sameAs', 'resource' => ''), '', false);
$content .= html_e('span', array('rev' => 'sioc:has_usergroup', 'resource' => $project_stdzd_uri), '', false);
echo html_e('div', array('about' => $usergroup_stdzd_uri, 'typeof' => 'sioc:UserGroup'), $content);

$title_arr=array();
$title_arr[]=_('Member');
$title_arr[]=_('User Name');
$title_arr[]=_('Role(s)/Position(s)');
if(forge_get_config('use_people')) {
	$title_arr[]=_('Skills');
}

echo $HTML->listTableTop($title_arr);

// list members
$members = $project->getUsers() ;

$i=0;
foreach ($members as $user) {
	$cells = array();
	// RDFa
	$member_uri = util_make_url_u($user->getUnixName(), $user->getID());
	$content = html_e('span', array('rev' => 'sioc:has_member', 'resource' => $usergroup_stdzd_uri), '', false);
	$content .= html_e('span', array('property' => 'sioc:name', 'content' => $user->getUnixName()), '', false);
	if (RBACEngine::getInstance()->isActionAllowedForUser($user, 'project_admin', $project->getID())) {
		$content .= html_e('strong', array(), $user->getRealName());
	} else {
		$content .= $user->getRealName();
	}
	$cells[][] = html_e('div', array('about' => $member_uri, 'typeof' => 'sioc:UserAccount'), $content);
	$cells[][] = util_display_user($user->getUnixName(), $user->getID(), $user->getRealName(), 's');

	$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($user) ;
	sortRoleList ($roles) ;
	$role_names = array () ;
	foreach ($roles as $role) {
		if ($role->getHomeProject() && $role->getHomeProject()->getID() == $project->getID()) {
			$role_names[] = $role->getName() ;
		}
	}
	$role_string = implode (', ', $role_names) ;
	$cells[] = array($role_string, 'class' => 'align-center');
	if (forge_get_config('use_people')) {
		$cells[] = array(util_make_link('/people/viewprofile.php?user_id='.$user->getID(), _('View')), 'class' => 'align-center');
	}
	echo $HTML->multiTableRow(array(), $cells);
}
// end of community member description block
echo $HTML->listTableBottom();

site_project_footer();
