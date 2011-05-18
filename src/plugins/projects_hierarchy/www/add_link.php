<?php
/**
 * Project Hierarchy Plugin
 *
 * Copyright 2006 Fabien Regnier - Sogeti
 * Copyright 2010, Franck Villaume - Capgemini
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest('group_id');
$sub_project_id = getIntFromRequest('sub_project_id');
$com = getStringFromRequest('com');

session_require_perm ('project_admin', $group_id) ;
//add link between two projects
db_begin();
db_query_params ('INSERT INTO plugin_projects_hierarchy (project_id ,sub_project_id,link_type,com) VALUES ($1 , $2, $3,$4)',
			array ($group_id,
				$sub_project_id,
				'navi',
				$com)) or die(db_error());
db_commit();

header("Location: ".util_make_url ('/project/admin/index.php?group_id='.$group_id));
?>
