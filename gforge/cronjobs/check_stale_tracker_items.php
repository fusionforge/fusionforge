#! /usr/bin/php4 -f
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
 * @version   $Id$
 * @author Darrell Brogdon dbrogdon@valinux.com
 * @date 2001-04-20
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$time = time();

$sql = "UPDATE
			artifact
		SET
		    status_id='2'
		WHERE
	        artifact_id IN (
				SELECT
					artifact_id
				FROM
					artifact a NATURAL JOIN artifact_group_list agl
				WHERE
					(agl.status_timeout+a.close_date) < '$time'
				AND
					a.status_id=4);";
$res = db_query($sql);

cron_entry(2,db_error());

?>
