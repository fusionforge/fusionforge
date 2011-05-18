#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
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

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

#
#    aggregate the ratings
#

db_begin();

$rel = db_query_params ('DELETE FROM survey_rating_aggregate;',
			array()) ;

$err .= db_error();

$rel = db_query_params ('INSERT INTO survey_rating_aggregate SELECT type,id,avg(response),count(*) FROM survey_rating_response GROUP BY type,id',
			array());

db_commit();

if (db_error()) {
	$err .= "Error: ".db_error();
}

cron_entry(9,$err);

?>
