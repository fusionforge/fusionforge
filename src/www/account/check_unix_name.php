<?php
/**
 * Check if unix name exists
 *
 * Copyright (C) 2015  Inria (Sylvain Beucler)
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

$response = null;
$unix_name = trim(strtolower(getStringFromRequest('unix_name')));
$result = db_query_params ('SELECT user_id as id  
                        FROM users 
			WHERE user_name LIKE $1
			UNION 
			SELECT group_id as id 
			FROM groups 
			WHERE unix_group_name LIKE $1',
            		    array ($unix_name),
            		    $max_rows+1,
            		    $offset);
$avail_rows=db_numrows($result);
if ($avail_rows > 0) {
	$response = "<span style='color:red'>"
		._('Invalid Unix Name.')
		." "
		._('That username already exists.')
		."</span>";
}
echo $response;
die;
