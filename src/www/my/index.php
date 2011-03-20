<?php
/**
 * FusionForge User's Personal Page
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge Team
 * Copyright 2009, Roland Mas
 * Copyright 2011, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once 'my_utils.php';
require_once $gfwww.'include/vote_function.php';
require_once $gfcommon.'tracker/ArtifactsForUser.class.php';
require_once $gfcommon.'forum/ForumsForUser.class.php';
require_once $gfcommon.'pm/ProjectTasksForUser.class.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

if (!session_loggedin()) { // || $sf_user_hash) {
	exit_not_logged_in();
}

use_javascript('/tabber/tabber.js');
site_user_header(array('title'=>sprintf(_('Personal Page For %s'), user_getname())));

$sql = "SELECT l.*
		FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
		WHERE o.owner_type = $1
		AND o.owner_id = $2
		AND o.is_default = 1
		";
$res = db_query_params($sql,array('u', user_getid()));
$layout_id = db_result($res, 0 , 'id');

echo '<ul class="widget_toolbar">';
$url = "/widgets/widgets.php?owner=u".user_getid().
	"&amp;layout_id=".$layout_id;
echo '	<li ><a href="'. $url .'">'. _("Add widgets") .'</a></li>';
echo '	<li><a href="'. $url.'&amp;update=layout' .'">'. _("Customize layout") .'</a></li>';
echo '</ul>';


$lm = new WidgetLayoutManager();
$lm->displayLayout(user_getid(), WidgetLayoutManager::OWNER_TYPE_USER);

site_user_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
