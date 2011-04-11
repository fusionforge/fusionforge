<?php
/**
 * MantisBT plugin
 *
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright 2009, Fabien Dubois - Capgemini
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
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

$type = getStringFromRequest('type');

if (!$type) {
	if (forge_get_config('use_ssl'))
		$url = "https://";
	else
		$url = "http://";

	$url .= forge_get_config('web_host');
	exit_missing_param(substr($_SERVER['HTTP_REFERER'], strlen($url)), array('No TYPE specified'), 'mantisbt');
}

$use_tooltips = 1;
$editable = 1;
$mantisbt = plugin_get_object('mantisbt');

switch ($type) {
	case 'group': {
		$group_id = getIntFromRequest('group_id');
		if (!$group_id) {
			if (forge_get_config('use_ssl'))
				$url = "https://";
			else
				$url = "http://";

			$url .= forge_get_config('web_host');
			exit_missing_param(substr($_SERVER['HTTP_REFERER'], strlen($url)), array('No GROUP_ID specified'), 'mantisbt');
		}
		$group = group_get_object($group_id);
		if (!$group) {
			exit_no_group();
		}
		if (!$group->usesPlugin($mantisbt->name)) {//check if the group has the MantisBT plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'), $mantisbt->name), 'home');
		}
		if ( $group->isError()) {
			$error_msg .= $group->getErrorMessage();
		}

		if (session_loggedin()) {
			$user = session_get_user(); // get the session user

			if (!$user || !is_object($user)) {
				exit_error(_('Invalid User'), 'home');
			} else if ( $user->isError()) {
				exit_error($user->isError(), 'home');
			} else if ( !$user->isActive()) {
				exit_error(_('User not active'), 'home');
			}
		}

		$mantisbtConf = $mantisbt->getMantisBTConf();
		$view = getStringFromRequest('view');
		if ($mantisbtConf['id_mantisbt'] === 0) {
			$warning_msg = _('The mantisbt plugin for this project is not initialized.');
			$redirect_url = '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&view=init&warning_msg='.urlencode($warning_msg);
			if ($error_msg) {
				$redirect_url .= '&error_msg='.urlencode($error_msg);
			}
			session_redirect($redirect_url);
		}

		$action = '';
		if (isset($user)) {
			$userperm = $group->getPermission($user);
			if ($userperm->IsMember()) {
				$mantisbtUserConf = $mantisbt->getUserConf($user->getID());
				if ($mantisbtUserConf) {
					$username = $mantisbtUserConf['user'];
					$password = $mantisbtUserConf['password'];
				} else {
					$warning_msg = _('Your mantisbt user is not initialized.');
					session_redirect('/plugins/'.$mantisbt->name.'/?type=user&pluginname='.$mantisbt->name.'&view=inituser&warning_msg='.urlencode($warning_msg));
				}
				$action = getStringFromRequest('action');
			}
			$use_tooltips = $user->usesTooltips();
		}

		if (!isset($username) || !isset($password)) {
			$username = $mantisbtConf['soap_user'];
			$password = $mantisbtConf['soap_password'];
			$editable = 0;
		}

		$sort = getStringFromRequest('sort');
		$dir = getStringFromRequest('dir');
		$idBug = getStringFromRequest('idBug');
		$idNote = getStringFromRequest('idNote');
		$idAttachment = getStringFromRequest('idAttachment');
		$actionAttachment = getStringFromRequest('actionAttachment');
		$page = getStringFromRequest('page');
		global $gfplugins;

		switch ($action) {
			case "updateIssue":
			case "addNote":
			case "addIssue":
			case "deleteNote":
			case "addAttachment":
			case "deleteAttachment": {
				include($gfplugins.$mantisbt->name.'/action/'.$action.'.php');
				break;
			}
			case "updateNote":
			case "privateNote":
			case "publicNote": {
				include($gfplugins.$mantisbt->name.'/action/updateNote.php');
				break;
			}
		}

		$mantisbt->getHeader('project');
		// URL analysis

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
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		$user = session_get_user();
		if (!($user) || !($user->usesPlugin($mantisbt->name))) {
			exit_error(sprintf(_('First activate the User\'s %s plugin through Account Maintenance Page'), $mantisbt->name), 'my');
		}

		$action = getStringFromRequest('action');
		$view = getStringFromRequest('view');
		$sort = getStringFromRequest('sort');
		$dir = getStringFromRequest('dir');
		$action = getStringFromRequest('action');
		$idBug = getStringFromRequest('idBug');
		$idNote = getStringFromRequest('idNote');
		$page = getStringFromRequest('page');

		if ($view != 'inituser' && $action != 'inituser') {
			$mantisbtConf = $mantisbt->getUserConf($user->getID());
			if ($mantisbtConf) {
				$username = $mantisbtConf['user'];
				$password = $mantisbtConf['password'];
			}  else {
				$warning_msg = _('Your mantisbt user is not initialized.');
				$redirect_url = '/plugins/'.$mantisbt->name.'/?type=user&pluginname='.$mantisbt->name.'&view=inituser&warning_msg='.urlencode($warning_msg);
				if ($error_msg) {
					$redirect_url .= '&error_msg='.urlencode($error_msg);
				}
				session_redirect($redirect_url);
			}
		}
		$use_tooltips = $user->usesTooltips();

		switch ($action) {
			case 'inituser':
			case 'updateIssue':
			case 'updateNote':
			case 'addNote':
			case 'deleteNote':
			case 'addAttachment':
			case 'deleteAttachment':
			case 'updateuserConf': {
				global $gfplugins;
				include($gfplugins.$mantisbt->name.'/action/'.$action.'.php');
				break;
			}
		}

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
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		$group_id = getIntFromRequest('group_id');
		if (!$group_id) {
			if (forge_get_config('use_ssl'))
				$url = "https://";
			else
				$url = "http://";

			$url .= forge_get_config('web_host');
			exit_missing_param(substr($_SERVER['HTTP_REFERER'], strlen($url)), array('No GROUP_ID specified'), 'mantisbt');
		}

		$group = group_get_object($group_id);
		if (!$group) {
			exit_no_group();
		}
		if (!$group->usesPlugin($mantisbt->name)) {//check if the group has the MantisBT plugin active
			exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$mantisbt->name),'home');
		}
		if ($group->isError()) {
			$error_msg .= $group->getErrorMessage();
		}
		$user = session_get_user();
		$userperm = $group->getPermission($user);//we'll check if the user belongs to the group
		if (!$userperm->IsMember()) {
			exit_permission_denied(_('You are not a member of this project'), 'home');
		}

		if (!$userperm->isAdmin()) {
			exit_permission_denied(_('You are not Admin of this project'), 'mantisbt');
		}

		$mantisbtConf = $mantisbt->getMantisBTConf();
		$action = getStringFromRequest('action');
		$view = getStringFromRequest('view');
		if ($view != 'init' && $action != 'init') {
			if ($mantisbtConf['id_mantisbt'] === 0) {
				$warning_msg = _('The mantisbt plugin for this project is not initialized.');
				$redirect_url = '/plugins/'.$mantisbt->name.'/?type=admin&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&view=init&warning_msg='.urlencode($warning_msg);
				if ($error_msg) {
					$redirect_url .= '&error_msg='.urlencode($error_msg);
				}
				session_redirect($redirect_url);
			}

			if (isset($user)) {
				$mantisbtUserConf = $mantisbt->getUserConf($user->getID());
				if ($mantisbtUserConf) {
					$username = $mantisbtUserConf['user'];
					$password = $mantisbtUserConf['password'];
				}
				$use_tooltips = $user->usesTooltips();
			}

			// no user init ? we shoud force this user to init his account
			if (!isset($username) || !isset($password)) {
				$warning_msg = _('Your mantisbt user is not initialized.');
				session_redirect('/plugins/'.$mantisbt->name.'/?type=user&pluginname='.$mantisbt->name.'&view=inituser&warning_msg='.urlencode($warning_msg));
			}
		}

		switch ($action) {
			case 'init': {
				global $gfplugins;
				include($gfplugins.$mantisbt->name.'/action/'.$action.'.php');
				break;
			}
			case 'addCategory':
			case 'addVersion':
			case 'renameCategory':
			case 'deleteCategory':
			case 'deleteVersion':
			case 'updateVersion':
			case 'updateConf': {
				global $gfplugins;
				include($gfplugins.$mantisbt->name.'/action/'.$action.'.php');
				break;
			}
		}

		$mantisbt->getHeader('project');
		//only project admin can access here

		switch ($view) {
			case 'init': {
				$mantisbt->getInitDisplay();
				break;
			}
			default: {
				$mantisbt->getAdminView();
				break;
			}
		}
		break;
	}
	case 'globaladmin': {
		$action = getStringFromRequest('action');
		switch ($action) {
			case 'updateGlobalConf': {
				global $gfplugins;
				include($gfplugins.$mantisbt->name.'/action/'.$action.'.php');
				break;
			}
		}
		$mantisbt->getHeader('globaladmin');
		$mantisbt->getGlobalAdminView();
		break;
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
