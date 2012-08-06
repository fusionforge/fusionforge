<?php
/**
 * Task UUID implementation for FusionForge
 *
 * Copyright © 2010
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
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
 * Locate task information by UUID (project_task_id) and return as JSON.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'pm/ProjectTaskSqlQueries.php';

$tid = getIntFromRequest('tid');
if (!$tid)
	$tid = util_path_info_last_numeric_component();
if (!$tid) {
    exit_missing_param('',array(_('Task ID')),'pm');
}

$tinfo = getGroupProjectIdGroupId($tid);

if (!$tinfo) {
    exit_error(_('No Task with ID: ').$tid,'pm');
}

$asuser = getStringFromRequest('asuser');

if (getIntFromRequest('text'))
	$asformat = "text/plain; charset=\"UTF-8\"";
else
	$asformat = "application/json; charset=\"UTF-8\"";

$islogin = session_loggedin();
$isadmin = forge_check_global_perm ('forge_admin');
$ishttps = session_issecure();
$ispublic = isProjectTaskInfoPublic($tid);

if (!$ishttps) {
	$islogin = false;
	$isadmin = false;
}

if ($ispublic) {
	$showall = true;
} else if ($islogin) {
	if (!$isadmin || !$asuser) {
		/* operate as ourselves */
		$asuser = session_get_user()->getUnixName();
	}

	if (isUserAndTaskinSameGroup($tid, $asuser))
		$showall = true;
	else
		$showall = false;
} else {
	$showall = false;
}

if ($showall) {
	$tinfo = getAllFromProjectTask($tid);
}

$tinfo['public'] = $ispublic;
$tinfo['forge_base'] = forge_get_config ('web_host') ;
$tinfo['forge_name'] = forge_get_config ('forge_name') ;

sysdebug_off("Content-type: " . $asformat);
setup_gettext_from_langname ('English') ;
echo json_encode($tinfo) . "\n";
exit;
