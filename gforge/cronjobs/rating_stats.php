#! /usr/bin/php4 -f
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
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

/*if (!strstr($REMOTE_ADDR,$sys_internal_network)) {
        exit_permission_denied();
}*/

#
#    aggregate the ratings
#

db_begin();

$rel = db_query("DELETE FROM survey_rating_aggregate;");
echo db_error();


$query = "INSERT INTO survey_rating_aggregate SELECT type,id,avg(response),count(*) FROM survey_rating_response GROUP BY type,id;";
$rel = db_query($query);

db_commit();

if (db_error()) {
	echo "Error: ".db_error();
}

?>
