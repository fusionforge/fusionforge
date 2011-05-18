#! /usr/bin/php
<?php
/**
 * check_stale_tracker_items.php - Check for stale tracker items.
 *
 * This script goes through the database looking for tracker items that have a
 * status of 'Pending' older than the admin-defined timeout period.  The items
 * that it finds it goes ahead and closes them out.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @author Darrell Brogdon dbrogdon@valinux.com
 * @date 2001-04-20
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

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$res = db_query_params ('UPDATE artifact SET status_id = 2
			WHERE artifact_id IN (
				SELECT artifact_id
				FROM artifact a NATURAL JOIN artifact_group_list agl
				WHERE (agl.status_timeout + a.close_date) < $1
				AND a.status_id=4)',
			array (time()));

cron_entry(2,db_error());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
