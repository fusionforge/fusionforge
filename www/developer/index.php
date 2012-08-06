<?php
/**
 * Developer's Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems, dtype
 * Copyright 2010 (c) Franck Villaume
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


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$user_id = getIntFromRequest('user_id');

if (!$user_id) {
	$user_id = getIntFromRequest('form_dev');
}

if (isset ($sys_noforcetype) && $sys_noforcetype) {
	if (!$user_id) {
		exit_missing_param('',array(_('A user must be specified for this page.')),'home');
	} else {
		$user =& user_get_object($user_id);
		if (!$user || !is_object($user)) {
			    exit_error(_('Invalid User'),'home');
		} else if ( $user->isError()) {
			    exit_error($user->isError(),'home');
		} else if ( !$user->isActive()) {
			    exit_error(_('User not active'),'home');
		}

		include $gfwww.'include/user_home.php';
	}
} else {
	session_redirect('/users/'.user_getname($user_id).'/');
}

?>
