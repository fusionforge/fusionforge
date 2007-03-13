<?php

/**
 * GForge Forum Pending Messages Management Admin Page
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

/* message moderation
	by Daniel Perez - 2005
*/

require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('www/forum/admin/ForumAdmin.class');
require_once('common/forum/Forum.class');
require_once('common/forum/ForumMessage.class');
require_once('www/forum/include/AttachManager.class'); //attachent manager

$action = getStringFromRequest('action');
$group_id = getIntFromRequest('group_id');
$forum_id = getStringFromRequest("forum_id");

$fa = new ForumAdmin();

if ($fa->Authorized($group_id)) {
	//user authorized, continue check
	
	//if there�s no forum_id input, then the user must have access to all forums, thus he�s a group admin for the forums
	if (!$forum_id) {
		if ($fa->isGroupAdmin()) {
			forum_header(array('title'=>_('Forums: Administration')));
			if (getStringFromRequest("Go")) {
				$fa->ExecuteAction("view_pending");
			} else {
				$fa->ExecuteAction($action);
			}
			forum_footer(array());
		} else {
			exit_permission_denied();
		}
	} else {
//		if ($forum_id=="A") {
			//all messages
//			if (!$fa->isGroupAdmin()) {
//				exit_permission_denied();
//			}
//		} else {
			if (!$fa->isForumAdmin($forum_id)) {
				exit_permission_denied();
			}
//		}
		forum_header(array('title'=>_('Forums: Administration')));
		if (getStringFromRequest("Go")) {
			$fa->ExecuteAction("view_pending");
		} else {
			$fa->ExecuteAction($action);
		}
		forum_footer(array());
	}
}	else {
	//manage auth errors
	if ($fa->isGroupIdError()) {
		exit_no_group();
	}	elseif ($fa->isPermissionDeniedError()) {
		exit_permission_denied();
	}
	
}



?>
