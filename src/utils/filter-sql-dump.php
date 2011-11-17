#! /usr/bin/php
<?php
/**
 * Copyright 2011 Roland Mas
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

require_once (dirname(__FILE__).'/../common/include/sqlparser.php');

$file = $argv[1];

$queries = array();
foreach (parse_sql_file($file) as $q) {
	$q = trim($q);
	$q = preg_replace('/\s+/', ' ', $q);

	if (preg_match('/^COMMENT/', $q)) continue;
	if (preg_match('/^SET/', $q)) continue;
	if (preg_match('/^\\\connect/', $q)) continue;

	$queries[] = $q;
}

sort($queries);

foreach ($queries as $q) {
	print "$q\n";
}
?>
