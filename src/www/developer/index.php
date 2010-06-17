<?php
/**
 * GForge Developer's Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */


/*
	Developer Info Page
	Written by dtype Oct 1999
*/

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

$user_id = getIntFromRequest('user_id');

if (!$user_id) {
	$user_id = getIntFromRequest('form_dev');
}

if (isset ($sys_noforcetype) && $sys_noforcetype) {
	if (!$user_id) {
		exit_error(_('Missing User Argument'),_('A user must be specified for this page.'));
	} else {
		$user =& user_get_object($user_id);
		if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
			exit_error(_('That user does not exist.'),_('Invalid User'));
		}
		include $gfwww.'include/user_home.php';
	}
} else {
	header("Location: ".util_make_url ('/users/'.user_getname($user_id).'/'));
}

?>
