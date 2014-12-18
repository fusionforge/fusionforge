<?php
/**
 * User's current and completed system actions
 *
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/SysTasksQ.class.php';

global $HTML; // Layout object

if (!session_loggedin()) {
		exit_not_logged_in();
}


// Test
if ($_SERVER['QUERY_STRING'] == 'create') {
		$sa = new SysTasksQ();
		$sa->add(null, 1, 1, null);
		if ($sa->isError()) {
				exit_error($sa->getErrorMessage());
		}
}


site_user_header(array('title' => _('System actions queue')));

$u = session_get_user();
$groups = $u->getGroups();
$gids = array();
foreach($groups as $g)
		$gids[] = $g->getID();
$gids = implode(',', $gids);

$res = pg_query_params("SELECT * FROM systasks WHERE user_id=$1 or group_id IN ($gids)"
					   . " AND requested > NOW() - interval '1 day'",
					   array($u->getID()));
while($row = db_fetch_array($res)) {
		print_r($row);
		print "<br />";
}

site_user_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
