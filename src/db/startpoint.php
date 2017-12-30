#! /usr/bin/php
<?php
/**
 * Create database_changes & database_version
 *
 * Copyright 2017, Franck Villaume - TrivialDev
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

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

if (!$gfconn) {
	print forge_get_config ('forge_name')." Could Not Connect to Database: ".db_error();
	exit;
}

if (!check_tables()) {
	echo "ERROR: Could not check tables\n";
	exit();
} else {
	set_version('4.8.50', 20090507);
}

function check_tables() {
	db_begin();

	if (!db_check_table_exists('database_startpoint')) {
		$res = db_query_params('CREATE TABLE database_startpoint (db_version varchar(10), db_start_date int)', array());

		if (!$res) { // db error
			echo "DB-ERROR-2: ".db_error()."\n";
			db_rollback();
			return false;
		}
	}

	if (!db_check_table_exists('database_changes')) {
		$res = db_query_params('CREATE TABLE database_changes (filename text)', array());

		if (!$res) { // db error
			echo "DB-ERROR-4: ".db_error()."\n";
			db_rollback();
			return false;
		}
	}

	db_commit();
	return true;
}

function set_version($version, $date) {
	db_begin();
	$res = db_query_params('TRUNCATE TABLE database_startpoint', array());

	if (!$res) { // db error
		echo "DB-ERROR-5: ".db_error()."\n";
		db_rollback();
		return false;
	}
	$res = db_query_params('INSERT INTO database_startpoint (db_version, db_start_date) VALUES ($1, $2)', array($version, $date));

	if (!$res) { // db error
		echo "DB-ERROR-5: ".db_error()."\n";
		db_rollback();
		return false;
	}
	echo "FusionForge Database Version: $version ($date)\n";
	db_commit();
}
