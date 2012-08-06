<?php
/**
 * Forum Pending Messages Detail
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2005 (c) Daniel Perez
 * http://fusionforge.org/
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
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/ForumAdmin.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';

$msg_id = getIntFromRequest("msg_id");
$group_id = getIntFromRequest('group_id');
$forum_id = getIntFromRequest("forum_id");

global $HTML;

$fa = new ForumAdmin($group_id);

if ( (!$forum_id) || (!$group_id) || (!$msg_id) ) {
	exit_missing_param('',array(_('Forum ID'),_('Project ID'),_('Message ID')),'forums');
}

session_require_perm ('forum', $group_id, 'moderate') ;

//print the message
$g =& $fa->GetGroupObject();
$f=new Forum($g,$forum_id);
if (!$f || !is_object($f)) {
	exit_error(_('Error'),"Error getting new Forum");
} elseif ($f->isError()) {
	exit_error(_('Error'),$f->getErrorMessage());
}
$fm = new ForumMessage($f,$msg_id,false,true); //create the pending message
if (!$fm || !is_object($fm)) {
	exit_error(_('Error getting new ForumMessage'),'forums');
} elseif ($fm->isError()) {
	exit_error(_('Error getting new ForumMessage: ').$fm->getErrorMessage(),'forums');
}
$fhtml = new ForumHTML($f);
if (!$fhtml || !is_object($fhtml)) {
	exit_error(_('Error getting new ForumHTML'),'forums');
} elseif ($fhtml->isError()) {
	exit_error($fhtml->getErrorMessage(),'forums');
}
forum_header(array());
echo $fhtml->showPendingMessage($fm);
$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
