<?php
/**
 * Convert Debian plugin upgrades tracking
 *
 * Copyright (C) 2014  Sylvain Beucler
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

// Debian used a plugin_$name_metadata table to track updates, with a schema version number
// FusionForge 5.3 tracks updates with a $name:$script_name entry in database_changes

require_once(dirname(__FILE__).'/../common/include/env.inc.php');
require_once($gfcommon.'include/pre.php');

function is_less_than($old, $new) {
	return !exec("dpkg --compare-versions $old lt $new");
}

// Convert scmgit
if (db_check_table_exists('plugin_scmgit_meta_data')) {
	$res = db_query_params("SELECT value FROM plugin_scmgit_meta_data WHERE key=$1", array('db-version'));
	$version = db_result($res, 0, 'value');
	print "  Converting scmgit, db version $version\n";
	if (is_less_than($version, '0.2'))
		db_query_params("INSERT INTO database_changes VALUES ('scmgit:20121019-multiple-repos.sql')", array());
	if (is_less_than($version, '0.2.1'))
		db_query_params("INSERT INTO database_changes VALUES ('scmgit:20121123-use-shared-table.sql')", array());
	if (is_less_than($version, '0.3'))
		db_query_params("INSERT INTO database_changes VALUES ('scmgit:20121128-drop-old-tables.sql')", array());
}

// Convert scmhook
if (db_check_table_exists('plugin_scmhook_meta_data')) {
	$res = db_query_params("SELECT value FROM plugin_scmhook_meta_data WHERE key=$1", array('db-version'));
	$version = db_result($res, 0, 'value');
	print "  Converting scmhook, db version $version\n";
	if (is_less_than($version, '0.2'))
		db_query_params("INSERT INTO database_changes VALUES ('scmhook:20130702-create_scmhook_git_committracker.sql')", array());
}

// Convert headermenu
if (db_check_table_exists('plugin_headermenu_meta_data')) {
	print "  Converting headermenu, db version 0.1\n";
	foreach(array('headermenu:20120930-addoutermenusupport.sql',
		 'headermenu:20121231-reorderentry.sql',
		 'headermenu:20130120-addprojectcolumn.sql') as $filename)
		db_query_params("INSERT INTO database_changes VALUES ($1)", array($filename));
}

// Plugins 'block', 'cvstracker' and 'wiki' also have db upgrades, but
// AFAICS the Debian packaging didn't apply them.


// Drop old tables
$res = db_query_params("SELECT relname FROM pg_class WHERE relname LIKE 'plugin_%_meta_data' AND relkind='r'", array());
while($row = db_fetch_array($res)) {
	print "  Dropping {$row['relname']}\n";
	db_drop_table_if_exists($row['relname']);
}

// Report to upgrade-db.php
echo "SUCCESS\n";
exit(0);
