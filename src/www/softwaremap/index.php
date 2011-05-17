<?php
/**
 * Trove Software Map
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * copyright 2010 (c) Franck Villaume - Capgemini
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
if (forge_get_config('use_project_tags')) {
	session_redirect('softwaremap/tag_cloud.php');
} elseif (forge_get_config('use_trove')){
	session_redirect('softwaremap/trove_list.php');
} elseif (forge_get_config('use_project_full_list')) {
	session_redirect('softwaremap/full_list.php');
} else {
	session_redirect('/');
}
?>
