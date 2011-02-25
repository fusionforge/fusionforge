<?php
/*
 * MantisBT plugin
 *
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfconfig.'plugins/mantisbt/config.php';

if (!session_loggedin()) {
	exit_not_logged_in();
}

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
	exit_error(_('Invalid User'), 'home');
} else if ( $user->isError()) {
	exit_error($user->isError(), 'home');
} else if ( !$user->isActive()) {
	exit_error(_('User not active'), 'home');
}

$type = getStringFromRequest('type');
$group_id = getIntFromRequest('group_id');
$user_id = getIntFromRequest('user_id');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));
$action = getStringFromRequest('action');
$view = getStringFromRequest('view');

if (!$type) {
	exit_missing_params($_SERVER['HTTP_REFERER'], array('No TYPE specified'), 'mantisbt');
} elseif (!$group_id) {
	exit_missing_params($_SERVER['HTTP_REFERER'], array('No GROUP_ID specified'), 'mantisbt');
}

$mantisbt = plugin_get_object('mantisbt');

switch ($type) {
	case 'group': {
		$group = group_get_object($group_id);
		if (!$group) {
			exit_no_group();
		}
		if (!$group->usesPlugin($mantisbt->name)) {//check if the group has the MantisBT plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $mantisbt->name), 'home');
		}

		$userperm = $group->getPermission($user);//we'll check if the user belongs to the group (optional)
		if ( !$userperm->IsMember()) {
			exit_permission_denied(_('You are not a member of this project'), 'home');
		}

		$mantisbtConf = $mantisbt->getMantisBTConf($group_id);


		if ($mantisbtConf['id_mantisbt'] === 0) {
			$warning_msg = _('The mantisbt plugin for this project is not initialized.');
			session_redirect('/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&view=init&warning_msg='.urlencode($warning_msg));
		}

		if (!$mantisbtConf['sync_users']) {
			$username = $mantisbtConf['soap_user'];
			$password = $mantisbtConf['soap_password'];
		}

		switch ($action) {
			case "updateIssue":
			case "addNote":
			case "addIssue":
			case "deleteNote":
			case "addAttachment":
			case "deleteAttachment": {
				include ("mantisbt/action/$action.php");
				break;
			}
			case "updateNote":
			case "privateNote":
			case "publicNote": {
				include ("mantisbt/action/updateNote.php");
				break;
			}
		}

		$mantisbt->getHeader('project');
		// URL analysis
		$sort = getStringFromRequest('sort');
		$dir = getStringFromRequest('dir');
		$action = getStringFromRequest('action');
		$idBug = getStringFromRequest('idBug');
		$idNote = getStringFromRequest('idNote');
		$idAttachment = getStringFromRequest('idAttachment');
		$actionAttachment = getStringFromRequest('actionAttachment');
		$page = getStringFromRequest('page');

		// Si la variable $_GET['page'] existe...
		if($page != null && $page != ''){
			$pageActuelle=intval($page);
		} else {
			$pageActuelle=1; // La page actuelle est la n°1
		}

		$format = "%07d";
		// do the job
		include ($mantisbt->name.'/www/group/index.php');
		break;
	}
	case 'user': {
		if (!($user) || !($user->usesPlugin($pluginname))) {
			exit_error(sprintf(_('First activate the User\'s %s plugin through Account Maintenance Page'), $pluginname), 'my');
		}
		if ( (!$user) || ($user->getID() != $user_id)) { // if someone else tried to access the private MantisBT part of this user
			exit_permission_denied(sprintf(_('You cannot access other user\'s personal %s'), $pluginname), 'my');
		}

		// URL analysis
		$sort = getStringFromRequest('sort');
		$dir = getStringFromRequest('dir');
		$action = getStringFromRequest('action');
		$idBug = getStringFromRequest('idBug');
		$idNote = getStringFromRequest('idNote');
		$page = getStringFromRequest('page');
		// Si la variable $_GET['page'] existe...
		if($page != null && $page != '') {
			$pageActuelle=intval($page);
		} else {
			$pageActuelle=1; // La page actuelle est la n°1
		}

		$format = "%07d";
		// do the job
		$mantisbt->getHeader('user');
		include($mantisbt->name.'/www/user/index.php');
		break;
	}
	case 'admin': {
		$group = group_get_object($group_id);
		if (!$group) {
			exit_no_group();
		}
		if (!$group->usesPlugin($mantisbt->name)) {//check if the group has the MantisBT plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$mantisbt->name),'home');
		}
		$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
		if (!$userperm->IsMember()) {
			exit_permission_denied(_('You are not a member of this project'));
		}

		switch ($action) {
			case "init": {
				global $gfplugins;
				include($gfplugins.$mantisbt->name.'/action/'.$action.'.php');
				break;
			}
		}

		$mantisbt->getHeader('project');
		//only project admin can access here
		if ($userperm->isAdmin()) {
			switch ($view) {
				case "init": {
					$mantisbt->getInitDisplay();
					break;
				}
			}
		} else {
			exit_permission_denied(_('You are not Admin of this project'), 'home');
		}
		break;
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
