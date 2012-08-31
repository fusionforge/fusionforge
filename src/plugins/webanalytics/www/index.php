<?php
/**
 * webanalytics plugin
 *
 * Copyright 2012, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

$type = getStringFromRequest('type');

if (!$type) {
	exit_missing_param($_SERVER['HTTP_REFERER'], array('No TYPE specified'), 'webanalytics');
}

global $use_tooltips;
$webanalytics = plugin_get_object('webanalytics');

switch ($type) {
	case 'globaladmin': {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		session_require_global_perm('forge_admin');
		$action = getStringFromRequest('action');
		switch ($action) {
			case 'addLink':
			case 'deleteLink':
			case 'updateLinkValue':
			case 'updateLinkStatus': {
				global $gfplugins;
				include($gfplugins.$webanalytics->name.'/action/'.$action.'.php');
				break;
			}
		}
		$webanalytics->getHeader('globaladmin');
		$view = getStringFromRequest('view');
		switch ($view) {
			case 'updateLinkValue':
				global $gfplugins;
				include($gfplugins.$webanalytics->name.'/view/admin/'.$view.'.php');
				break;
			default:
				$webanalytics->getGlobalAdminView();
				break;
		}
		break;
	}
}

site_project_footer(array());
?>
