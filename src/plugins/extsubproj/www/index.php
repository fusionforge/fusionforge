<?php
/**
 * Copyright 2004 (c) GForge LLC
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

$user = session_get_user(); // get the session user

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot Process your request for this user.");
}

$type = getStringFromRequest('type');
$plugin = plugin_get_object('extsubproj');

if (!$type) {
	exit_error("Cannot Process your request: No TYPE specified", 'home'); // you can create items in Base.tab and customize this messages
}

switch ($type) {
	case "group": {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		$id = getStringFromRequest('id');
		if (!$id) {
			exit_error("Cannot Process your request: No ID specified", 'home');
		}
		$group = group_get_object($id);
		if ( !$group) {
			exit_error("Invalid Project", 'home');
		}
		if (!$group->usesPlugin($plugin->name)) {//check if the group has the projects-hierarchy plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $plugin->name), 'home');
		}
		session_require_perm('project_admin', $id);

		$action = getStringFromRequest('action');
		global $gfplugins;
		switch ($action) {
			/*
			case "addChild":
			case "projectsHierarchyDocman":
			case "removeChild":
			case "removeParent":
			case "validateRelationship": {
				include($gfplugins.$plugin->name.'/actions/'.$action.'.php');
				break;
			}*/
			default: {
				$plugin->redirect($_SERVER['HTTP_REFERER'], 'error_msg', _('Unknown action.'));
				break;
			}
		}
		break;
	}
	case "globaladmin": {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		session_require_global_perm('forge_admin');
		$action = getStringFromRequest('action');
		switch ($action) {
			/*
			case 'updateGlobalConf': {
				global $gfplugins;
				include($gfplugins.$plugin->name.'/actions/'.$action.'.php');
				break;
			}*/
			default: {
				// do nothing, see getGlobalAdminView() below
				break;
			}
				
		}
		$plugin->getHeader('globaladmin');
		$plugin->getGlobalAdminView();
		$plugin->getFooter('globaladmin');
		break;
	}
	case "admin": {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		$id = getStringFromRequest('group_id');
		session_require_perm('project_admin', $id);
		if (!$id) {
			exit_error("Cannot Process your request: No ID specified", 'home');
		}
		$group = group_get_object($id);
		if ( !$group) {
			exit_error("Invalid Project", 'home');
		}
		if (!$group->usesPlugin($plugin->name)) {//check if the group has the projects-hierarchy plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $plugin->name), 'home');
		}
		$action = getStringFromRequest('action');
		switch ($action) {
			/*
			case 'updateProjectConf': {
				global $gfplugins;
				include($gfplugins.$plugin->name.'/actions/'.$action.'.php');
				break;
			}
			*/
			default: {
				break;
			}
		}
		// params needed by site_project_header() inside getHeader()
		$params = array(
			'toptab' => $plugin->name,
			'group' => $id);
		$plugin->getHeader('admin', $params);
		//$plugin->getProjectAdminView();
		$plugin->redirect($_SERVER['HTTP_REFERER'], 'error_msg', _('Unknown action.'));
		$plugin->getFooter('admin');
		break;
	}
	default: {
		exit_error("No TYPE specified", 'home');
		break;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
