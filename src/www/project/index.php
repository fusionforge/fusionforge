<?php
/**
 * Project Index page
 *
 * Copyright 2019, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';

$group_id=getIntFromGet('group_id');

/* validate group */
if (!$group_id) {
	exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g)) {
	exit_no_group();
}

session_require_perm('project_read', $group_id);

if ($g->isError()) {
	exit_error($g->getErrorMessage(), 'home');
}

/* everything sounds ok, now let's do the job */
$action = getStringFromRequest('action');
if (file_exists(forge_get_config('source_path').'/common/project/actions/'.$action.'.php')) {
	include(forge_get_config('source_path').'/common/project/actions/'.$action.'.php');
}

session_redirect('/projects/'.$g->getUnixName());
