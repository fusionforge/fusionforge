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

site_user_header(array('title' => _('System actions queue (past day)')));

$u = session_get_user();
$groups = $u->getGroups();
$gids = array();
foreach($groups as $g)
	$gids[] = $g->getID();
if (empty($gids))
	$gids[] = -1;  // avoid empty 'IN (...)' SQL clause
$gids = implode(',', $gids);

$title_arr = array(
	_('Task ID'),
	_('Plugin'),
	_('SysTask Type'),
	_('Group ID'),
	_('Status'),
	_('Requested'),
	_('Started'),
	_('Stopped'),
);

echo $HTML->listTableTop($title_arr);
$query = "
SELECT systask_id, COALESCE(unix_group_name, '-') AS unix_group_name,
plugin_name, systask_type, systasks.status,
    EXTRACT(epoch FROM requested) AS requested,
    EXTRACT(epoch FROM started) AS started,
    EXTRACT(epoch FROM stopped) AS stopped,
    EXTRACT(epoch FROM started-requested) AS queued,
    EXTRACT(epoch FROM stopped-started) AS run
  FROM systasks LEFT JOIN groups ON (systasks.group_id = groups.group_id)
    LEFT JOIN plugins ON (systasks.plugin_id = plugins.plugin_id)
  WHERE user_id=$1 or systasks.group_id IN ($gids)
  AND requested > NOW() - interval '1 day'
  ORDER BY systask_id";
$res = db_query_params($query, array($u->getID()));
for ($i=0; $i<db_numrows($res); $i++) {
	$cells = array();
	$cells[][] = db_result($res,$i,'systask_id');
	$plugin_name = db_result($res,$i,'plugin_name');
	if ($plugin_name == null)
		$cells[][] = 'core';
	else
		$cells[][] = $plugin_name;
	$cells[][] = db_result($res,$i,'systask_type');
	$cells[][] = db_result($res,$i,'unix_group_name');
	$cells[][] = db_result($res,$i,'status');
	$cells[][] = date("H:i:s", db_result($res, $i,'requested'));
	$cells[][] = date("H:i:s", db_result($res, $i,'started'))
		. ' (+' . round(db_result($res, $i,'queued'), 1) . 's)';
	$cells[][] = date("H:i:s", db_result($res, $i,'stopped'))
		. ' (+' . round(db_result($res, $i,'run'), 1) . 's)';
	echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i+1, true)), $cells);
}

echo $HTML->listTableBottom();

site_user_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
