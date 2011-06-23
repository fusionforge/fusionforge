<?php
/**
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

// the header that displays for the project portion of the plugin
function quota_management_Project_Header($params) {
	global $id;
	$params['toptab'] = 'quota_management';
	$params['group'] = $id;
	/*
	 Show horizontal links
	 */
	site_project_header($params);
}

// the header that displays for the user portion of the plugin
function quota_management_User_Header($params) {
	global $user_id;
	$params['toptab'] = 'quota_management';
	$params['user'] = $user_id;
	/*
	 Show horizontal links
	 */
	site_user_header($params);
}

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
	exit_error(_('Invalid User'),'home');
} else if ( $user->isError() ) {
	exit_error($user->getErrorMessage,'home');
} else if ( !$user->isActive()) {
	exit_error(_('User not active'),'home');
}

$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$pluginname = getStringFromRequest('pluginname');

if (!$type) {
	exit_missing_params($_SERVER['HTTP_REFERER'],array(_('No TYPE specified')), 'home');
} elseif (!$id) {
	exit_missing_params($_SERVER['HTTP_REFERER'],array(_('No ID specified')), 'home');
} else {
	if ($type == 'group') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if (!$group->usesPlugin($pluginname)) {//check if the group has the quota_management plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginnname),'home');
		}
		$userperm = $group->getPermission();//we'll check if the user belongs to the group (optional)
		if (!$userperm->IsMember()) {
			exit_permission_denied(_('You are not a member of this project'),'home');
		}
		quota_management_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
		include('quota_management/www/quota_project.php');
	} elseif ($type == 'user') {
		$realuser = user_get_object($id);//
		if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
			exit_error(sprintf(_('First activate the User\'s %s plugin through Account Maintenance Page'),$pluginname),'my');
		}
		if ( (!$user) || ($user->getID() != $id)) { // if someone else tried to access the private quota_management part of this user
			exit_permission_denied(sprintf(_('You cannot access other user\'s personal %s'),$pluginname),'my');
		}
		quota_management_User_Header(array('title'=>'My '.$pluginname,'pagename'=>"$pluginname",'sectionvals'=>array($realuser->getUnixName())));
		// DO THE STUFF FOR THE USER PART HERE
		echo "We are in the User quota_management plugin <br>";
		echo "Greetings from planet " . $world; // $world comes from the config file in /etc
	} elseif ($type == 'admin') {
		$group = group_get_object($id);
		if ( !$group) {
			exit_no_group();
		}
		if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the quota_management plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginnname),'home');
		}
		$userperm = $group->getPermission ();//we'll check if the user belongs to the group
		if ( !$userperm->IsMember()) {
			exit_permission_denied(_('You are not a member of this project'),'home');
		}
		//only project admin can access here
		if ( $userperm->isAdmin() ) {
			quota_management_Project_Header(array('title'=>$pluginname . ' Project Plugin!','pagename'=>"$pluginname",'sectionvals'=>array(group_getname($id))));
			include('quota_management/www/quota_project.php');
		} else {
			exit_permission_denied(_('You are not Admin of this project'),'home');
		}
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
