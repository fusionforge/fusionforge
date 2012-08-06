<?php
/**
 * Extra tabs plugin
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
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

require_once ('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest('group_id');
if (! $group_id) {
	exit_no_group();
}

$group =& group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

$tab_name = htmlspecialchars(trim(getStringFromRequest ('tab_name')));
$result = db_query_params('SELECT * FROM plugin_extratabs_main WHERE group_id=$1 AND tab_name=$2',
						array ($group_id, $tab_name));
if ($result && db_numrows($result)) {
	$tab_url = rtrim(db_result($result, 0, 'tab_url'), '/');
	site_project_header(array('title'=>_($tab_name), 'group' => $group_id, 'toptab'=>$tab_name));
	?>
	<iframe src="<?php echo $tab_url ?>" frameborder="0" height="600px" width="100%"></iframe>
	<?php
	site_project_footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
