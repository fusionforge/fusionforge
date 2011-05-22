<?php
/**
 * Projects Hierarchy Plugin
 *
 * Copyright 2006, Fabien Regnier fabien.regnier@sogeti.com
 * Copyright 2011, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest('group_id');
$sub_group_id = getIntFromRequest('sub_group_id');

session_require_perm('project_admin', $group_id);

//plugin webcal
$params[0] = $sub_group_id;
$params[1] = $group_id ;

plugin_hook('del_cal_link_father',$params);

//del link between two projects
db_begin();
db_query_params ('DELETE FROM plugin_projects_hierarchy WHERE project_id  = $1 AND sub_project_id = $2',
			array ($group_id,
				$sub_group_id)) or die(db_error());
db_commit();

header("Location: ".util_make_url ('/project/admin/index.php?group_id='.$group_id));
?>
