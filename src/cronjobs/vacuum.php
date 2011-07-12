#! /usr/bin/php
<?php
/**
 * nightly VACUUM job
 *
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

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

//
//	PG 7.1 and earlier
//
//$res = db_query_params ('VACUUM ANALYZE;',array()) ;

//
//	PG 7.2 and 7.3
//
$res = db_query_params ('VACUUM FULL ANALYZE;',
			array()) ;


if (!$res) {
	$err .= "Error on DB1: " . db_error();
}

cron_entry(12,$err);

?>
