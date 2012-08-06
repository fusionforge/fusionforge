<?php
/**
 * Implement CVS ACLs based on FusionForge roles
 *
 * Copyright 2004 GForge, LLC
 * Copyright 2010, Franck Villaume
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once '../../../www/env.inc.php';
require_once $gfcommon.'include/escapingUtils.php';
require_once $gfcommon.'include/pre.php';

if (!forge_get_config('use_scm')) {
	exit_disabled('home');
}

$env_group = getStringFromPost('group');
$env_user = getStringFromPost('user');
# Group must contain 3 - 15 alphanumeric chars or -
preg_match("/^([[:alnum:]-]{3,15})$/", $env_group, $matches);
# User rules
# 1. Must only contain alphanumeric chars or _ or -
# 2. Must be 3 - 15 chars
preg_match("/[[:alnum:]_-]{3,15}/", $env_user, $matches2);

if (count($matches) == 0) {
	exit_error(_('Invalid CVS repository : ').$env_group,'home');
} else {
	if (count($matches2) == 0) {
		exit_error(_('Invalid username : ').$env_user,'home');
	}

	$userName = $matches2[count($matches2)-1];
	$User =& user_get_object_by_name($userName);
	if (!$User || !is_object($User)) {
		exit_error(sprintf(_('User not found %s'),$userName),'home');
	}
	session_set_new($User->getID());

	$projectName = $matches[count($matches)-1];
	$Group = group_get_object_by_name($projectName);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}

	if (! forge_check_perm_for_user ($User, 'scm', $Group->getID(), 'write')) {
		exit_permission_denied('','home');
	}
}

exit(0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
