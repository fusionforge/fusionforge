<?php
/**
 * Role Delete Page
 *
 * Copyright 2010 (c) Alcatel-Lucent
 *
 * @author Alain Peyrat
 * @date 2010-05-18
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$role_id = getIntFromRequest('role_id');

session_require_global_perm ('forge_admin') ;

if (!$role_id) {
	session_redirect('/admin');
}

$role = RBACEngine::getInstance()->getRoleById($role_id);

if (!$role || !is_object($role)) {
	exit_error(_('Could Not Get Role'),'admin');
} elseif ($role->isError()) {
	exit_error($role->getErrorMessage(),'admin');
}

if ($role->getHomeProject() != NULL) {
	exit_error(_("You can only delete a global role from here."),'admin');
}

if (getStringFromRequest('submit')) {
	if (getIntFromRequest('sure')) {
		if (!$role->delete()) {
			$error_msg = _('ERROR: ').$role->getErrorMessage();
		} else {
			$feedback = _('Successfully Deleted Role');
			session_redirect('/admin/index.php?feedback='.urlencode($feedback));
		}
	} else {
		$error_msg = _('Error: Please confirm the deletion of the role.');
	}

	session_redirect('/admin/globalroleedit.php?role_id='.$role_id.'&error_msg='.urlencode($error_msg));
}
