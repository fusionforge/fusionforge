<?php
/**
 * FRSRelease UUID implementation for FusionForge
 *
 * Copyright © 2010
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
 * Copyright 2016, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option)
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
 *-
 * Follow up to the task information page by UUID (project_task_id)
 * via a redirection.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/FRSRelease.class.php';

$frsreleaseid = getIntFromRequest('frsreleaseid');
if (!$frsreleaseid) {
	$frsreleaseid = util_path_info_last_numeric_component();
}
if (!$frsreleaseid) {
    exit_missing_param('',array(_('frsreleaseid')), 'frs');
}

$frsrelease = frsrelease_get_object($frsreleaseid);
if ($frsrelease && is_object($frsrelease) && !$frsrelease->isError()) {
	session_redirect('/frs/?group_id='.$frsrelease->FRSPackage->Group->getID().'&release_id='.$frsreleaseid);
}
exit_error(_('No FRSRelease with ID')._(': ').$frsreleaseid, 'frs');
