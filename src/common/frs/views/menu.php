<?php
/**
 * FusionForge FRS
 *
 * Copyright 2014 Franck Villaume - TrivialDev
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $HTML; // html object
global $group_id; // id of group

if (!forge_check_perm('frs_admin', $group_id, 'read')) {
	$error_msg = _('FRS Access Denied');
	session_redirect('/frs/?group_id='.$group_id);
}

$fpFactory = new FRSPackageFactory($g);
if (!$fpFactory || !is_object($fpFactory)) {
	exit_error(_('Could Not Get FRSPackageFactory'), 'frs');
} elseif ($fpFactory->isError()) {
	exit_error($fpFactory->getErrorMessage(), 'frs');
}

$permissionlevel = $fpFactory->getPermissionOfASpecificUser();

/* create the submenu following role, rules and content */
$menu_text = array();
$menu_links = array();
$menu_attr = array();

switch ($permissionlevel) {
	case 2: { // file
		$menu_text = array(_('View File Releases'), _('Administration'));
		$menu_links = array('/frs/?group_id='.$group_id, '/frs/?view=admin&group_id='.$group_id);
		$menu_attr = array(NULL,NULL);
		break;
	}
	case 3: // release
	case 4: { // admin
		$menu_text = array(_('View File Releases'),_('Reporting'),_('Administration'));
		$menu_links = array('/frs/?group_id='.$group_id,'/frs/?view=reporting&group_id='.$group_id,'/frs/?view=admin&group_id='.$group_id);
		$menu_attr = array(NULL,NULL,NULL);
		break;
	}
}

if (count($menu_text)) {
	echo $HTML->subMenu($menu_text, $menu_links, $menu_attr);
}

plugin_hook('blocks', 'files index');
