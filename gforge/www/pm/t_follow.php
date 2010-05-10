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
 * Follow up to the task information page by UUID (project_task_id)
 * via a redirection.
 */

/*
 * I'm not a real programmer. I throw together things until it works
 * then I move on. The real programmers will say "yeah it works but
 * you're leaking memory everywhere. Perhaps we should fix that." I'll
 * just restart apache every 10 requests.
 * -- Rasmus Lerdorf
 */

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfcommon.'pm/ProjectTaskSqlQueries.php';

$tid = getIntFromRequest('tid');
if (!$tid)
	$tid = util_path_info_last_numeric_component();
if (!$tid) {
	header("HTTP/1.0 404 Not Found");
	echo "You forgot to pass the tid.\n";
	exit;
}

$tinfo = getGroupProjectIdGroupId($tid);

if (!$tinfo) {
	header("HTTP/1.0 404 Not Found");
	echo "There is no task with id ".$tid."!\n";
	exit;
}

$dsturl = util_make_url("/pm/task.php?func=detailtask&project_task_id=" .
    $tinfo['project_task_id'] . "&group_id=" . $tinfo['group_id'] .
    "&group_project_id=" . $tinfo['group_project_id']);
header("HTTP/1.0 302 Found");
header("Location: " . $dsturl);
echo "The result is at:\n" . $dsturl . "\n";
exit;
