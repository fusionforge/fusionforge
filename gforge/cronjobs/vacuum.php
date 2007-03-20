#! /usr/bin/php4 -f
<?php
/**
 * nightly VACUUM job
 *
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
require ('common/include/cron_utils.php');

$err='';

//
//	PG 7.1 and earlier
//
//$res = db_query("VACUUM ANALYZE;");
//
//	PG 7.2 and 7.3
//
if ($sys_database_type != 'mysql') {
	$res = db_query("VACUUM FULL ANALYZE;");

	if (!$res) {
		$err .= "Error on DB1: " . db_error();
	}
}

cron_entry(12,$err);

?>
