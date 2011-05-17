<?php
/**
 * Forum Attachments Admin Page
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
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';

$action = getStringFromRequest('action');
$group_id = getIntFromRequest('group_id');

$fa = new ForumAdmin($group_id);

session_require_perm ('forum_admin', $group_id) ;

forum_header(array('title'=>_('Forums: Administration')));
$fa->ExecuteAction($action);
forum_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
