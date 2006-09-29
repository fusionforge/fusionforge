<?php
/**
 * Implement CVS ACLs based on GForge roles
 *
 * Copyright 2004 GForge, LLC
 *
 * @version   $Id$
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('common/include/escapingUtils.php');
require_once('squal_pre.php');

if (!$sys_use_scm) {
	exit_disabled();
}

$env_group = getStringFromPost('group');
$env_user = getStringFromPost('user');
# Group must contain 3 - 15 alphanumeric chars or -
preg_match("/^([[:alnum:]-]{3,15})$/", $env_group, $matches);
# User rules
# 1. Must only contain alphanumeric chars
# 2. Must be 3 - 15 chars
preg_match("/[[:alnum:]_]{3,15}/", $env_user, $matches2);

if (count($matches) == 0) {
	exit_error('','Invalid CVS repository');
} else {
	if (count($matches2) == 0) {
		exit_error('','Invalid username');
	}

	$userName = $matches2[count($matches2)-1];
	$User =& user_get_object_by_name($userName);
	if (!$User || !is_object($User)) {
		exit_error('','User not found');
	}
	session_set_new($User->getID());

	$projectName = $matches[count($matches)-1];
	$Group =& group_get_object_by_name($projectName);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}

	$perm =& permission_get_object($Group, $User);
	if (!$perm || !is_object($perm) || !$perm->isCVSWriter()) {
		exit_permission_denied();
	}
}

exit(0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
