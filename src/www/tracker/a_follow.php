<?php
/**
 * Artifact UUID implementation for FusionForge
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
require_once $gfcommon.'tracker/Artifact.class.php';

$aid = getIntFromRequest('aid');
if (!$aid) {
	$aid = util_path_info_last_numeric_component();
}
if (!$aid) {
	exit_missing_param('',array(_('aid')), 'tracker');
}

$at = artifact_get_object($aid);

if ($at && is_object($at) && !$at->isError()) {
	session_redirect('/tracker/?func=detail&atid='.$at->ArtifactType->getID().'&aid='.$aid.'&group_id='.$at->ArtifactType->Group->getID());
}
exit_error(_('No Artifact with ID')._(': ').$aid, 'tracker');
