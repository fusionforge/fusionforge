<?php
/**
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2019, Franck Villaume - TrivialDev
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

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';

// the header that displays for the project portion of the plugin

$user = session_get_user(); // get the session user

if (!$user || !is_object($user)) {
	exit_error(_('Invalid User'), 'home');
} elseif ( $user->isError() ) {
	exit_error($user->getErrorMessage, 'home');
} elseif ( !$user->isActive()) {
	exit_error(_('User not active'), 'home');
}

$type = getStringFromRequest('type');

if (!$type) {
	exit_missing_param($_SERVER['HTTP_REFERER'], array('No TYPE specified'), 'quota_management');
}

$quota_management = plugin_get_object('quota_management');

switch ($type) {
	case 'globaladmin': {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		session_require_global_perm('forge_admin');
		$action = getStringFromRequest('action');
		$view = getStringFromRequest('view');
		switch ($action) {
			default:
				break;
		}
		$quota_management->getHeader($type);
		switch ($view) {
			default:
				include $quota_management->name.'/view/quota.php';
				break;
		}
		break;
	}
	case 'projectadmin': {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		$group_id = getIntFromRequest('group_id');
		session_require_perm('project_admin', $group_id);
		$action = getStringFromRequest('action');
		$view = getStringFromRequest('view');
		switch ($action) {
			default:
				break;
		}
		switch ($view) {
			default:
				$quota_management->getHeader($type, $group_id);
				include $quota_management->name.'/view/quota_project.php';
				break;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
