<?php

/**
 * GForge Forum Pending Messages Detail
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
require_once('www/forum/include/ForumHTML.class.php');
require_once('www/forum/admin/ForumAdmin.class.php');
require_once('common/forum/Forum.class.php');
require_once('common/forum/ForumMessage.class.php');

$msg_id = getIntFromRequest("msg_id");
$group_id = getIntFromRequest('group_id');
$forum_id = getIntFromRequest("forum_id");

global $HTML;

$fa = new ForumAdmin();

if ( (!$forum_id) || (!$group_id) || (!$msg_id) ) {
	exit_missing_param();
}
if ($fa->Authorized($group_id)) {
	//user authorized, continue check
	if ($fa->isForumAdmin($forum_id)) {
		//print the message
		forum_header(array());
		$g =& $fa->GetGroupObject();
		$f=new Forum($g,$forum_id);
		if (!$f || !is_object($f)) {
			exit_error(_('Error'),"Error getting new Forum");
		} elseif ($f->isError()) {
			exit_error(_('Error'),$f->getErrorMessage());
		}
		$fm = new ForumMessage($f,$msg_id,false,true); //create the pending message
		if (!$fm || !is_object($fm)) {
			exit_error(_('Error'), "Error getting new ForumMessage");
		} elseif ($fm->isError()) {
			exit_error(_('Error'),"Error getting new ForumMessage: ".$fm->getErrorMessage());
		}
		$fhtml = new ForumHTML($f);
		if (!$fhtml || !is_object($fhtml)) {
			exit_error(_('Error'), "Error getting new ForumHTML");
		} elseif ($fhtml->isError()) {
			exit_error(_('Error'),$fhtml->getErrorMessage());
		}
		echo $fhtml->showPendingMessage($fm);
		$HTML->footer(array());
	}
	else {
		exit_permission_denied();
	}
} else {
	exit_permission_denied();
}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
