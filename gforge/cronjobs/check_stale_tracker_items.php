#!/usr/local/bin/php -q
<?php
/**
 * check_stale_tracker_items.php - Check for stale tracker items.
 *
 * This script goes through the database looking for tracker items that have a
 * status of 'Pending' older than the admin-defined timeout period.  The items
 * that it finds it goes ahead and closes them out.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 * @author Darrell Brogdon dbrogdon@valinux.com
 # @date 2001-04-20
 *
 */

require ('squal_pre.php');

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

?>
