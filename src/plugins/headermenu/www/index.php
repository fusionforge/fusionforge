<?php
/**
 * headermenu plugin
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
	exit_missing_param($_SERVER['HTTP_REFERER'], array('No TYPE specified'), 'headermenu');
}

global $use_tooltips;
$headermenu = plugin_get_object('headermenu');

switch ($type) {
	case 'globaladmin': {
		if (!session_loggedin()) {
			exit_not_logged_in();
		}
		session_require_global_perm('forge_admin');
		$action = getStringFromRequest('action');
		switch ($action) {
			case 'addLink': {
				global $gfplugins;
				include($gfplugins.$headermenu->name.'/action/'.$action.'.php');
				break;
			}
		}
		$headermenu->getHeader('globaladmin');
		$headermenu->getGlobalAdminView();
		break;
	}
}

site_project_footer(array());

?>

