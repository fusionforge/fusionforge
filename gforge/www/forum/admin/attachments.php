<?php

/**
 * GForge Forum Attachments Admin Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* attachment manager
	by Daniel Perez - 2005
*/

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'forum/include/ForumHTML.class.php';
require_once $gfwww.'forum/admin/ForumAdmin.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';

$action = getStringFromRequest('action');
$group_id = getIntFromRequest('group_id');

$fa = new ForumAdmin();

if ($fa->Authorized($group_id)) {
	//user authorized, continue
	if ($fa->isGroupAdmin()) {
		forum_header(array('title'=>_('Forums: Administration')));
		$fa->ExecuteAction($action);
		forum_footer(array());
	} else {
		exit_permission_denied();
	}
}	else {
	//manage errors
	if ($fa->isGroupIdError()) {
		exit_no_group();
	}	elseif ($fa->isPermissionDeniedError()) {
		exit_permission_denied();
	}
	
}



?>
