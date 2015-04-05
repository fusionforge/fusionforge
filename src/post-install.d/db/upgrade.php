#! /usr/bin/php
<?php
/**
 * Apply database updates (core and plugins)
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2003-2004 (c) GForge
 * Copyright (C) 2009, 2012  Roland Mas
 * Copyright 2011, 2014 (c) Franck Villaume
 * Copyright (C) 2010, 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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

// upgrade-db.php          => Upgrade the main database.
// upgrade-db.php all      => Upgrade the main database and active plugins.
// upgrade-db.php <plugin> => Upgrade only the database of the given active plugin.

require_once dirname(__FILE__).'/../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/sqlparser.php';

$db_path = forge_get_config('source_path').'/db/';
$date = -1;
$version = '';

// a huge message will warn not to run it without doing a backup first // warning.

// Check if table 'database_startpoint' exists
if (!db_check_table_exists('database_startpoint')) {
	show("ERROR: table 'database_startpoint' does not exist.\nRun startpoint.php first.\n");
	exit(1);
}

// Check if table 'database_startpoint' has proper values
$res = db_query_params('SELECT * FROM database_startpoint',
		       array());

if (!$res) { // db error
	show("DB-ERROR-3: ".db_error()."\n");
	exit(1);
} elseif (db_numrows($res) == 0) { // table 'database_startpoint' is empty
	show("ERROR: table 'database_startpoint' is empty.\nRun startpoint.php first.\n");
	exit(1);
} else { // get the start date from the db
	$date = (int) db_result($res, 0, 'db_start_date');
	$version = db_result($res, 0, 'db_version');
}

// Upgrade main database if no argument or if all)
if ($argc == 1 || $argv[1] == 'all') {
	$scripts = get_scripts($db_path);
	foreach ($scripts as $script) {
		if ((int) $script['date'] > $date) {
			$res = db_query_params('SELECT * FROM database_changes WHERE filename=$1',
						array ("{$script['filename']}"));
			if (!$res) {
				// error
				show("ERROR-2: ".db_error()."\n");
				exit(1);
			} elseif (db_numrows($res) == 0) {
				show("Running script: {$script['filename']}\n");
				db_begin();
				$result = run_script($script);
				if ($result) {
					$res = db_query_params ('INSERT INTO database_changes (filename) VALUES ($1)',
								array ("{$script['filename']}")) ;
					if (!$res)
					{
						show("ERROR-3: ".db_error()."\n");
						exit(1);
					}
					db_commit();
				} else {
					db_rollback();
					// error
					exit(1);
				}
			} else {
	//			show("Skipping script: {$script['filename']}\n");
			}
		}
	}
}

if ($argc == 2) {
	require_once $gfcommon.'include/DatabaseInstaller.class.php';
	// Upgrade activated plugins.
	$activated_plugins = get_installed_plugins();
	$plugins = array();
        if ($argv[1] == 'all') {
                // Upgrade activated plugins.
                $plugins = get_installed_plugins();
        } else if (in_array($argv[1], $activated_plugins)) {
		// Upgrade a specific plugin, if activated
                $plugins[] = $argv[1];
        }
	foreach ($plugins as $plugin) {
		$di = new DatabaseInstaller($plugin, dirname($db_path) . '/plugins/' . $plugin . '/db');
		echo $di->upgrade();
	}
}

function get_installed_plugins() {
	$plugins = array();
	$res = db_query_params ('SELECT plugin_name FROM plugins', array ());
	while ($row = db_fetch_array($res)) {
		$plugins[] = $row['plugin_name'];
	}
	return $plugins;
}

function get_scripts($dir) {
	$data = array();
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				$pos = strrpos($file, '.');
				if ($pos !== false && $pos > 0) {
					$name = substr($file, 0, $pos);
					if (strlen($name) >= 8) {
						$date_aux = substr($name, 0, 8);
						$type_aux = substr($file, $pos + 1);
						if ($type_aux=='sql' || $type_aux=='php') {
							$data[] = array('date'=>$date_aux, 'filename'=>$file, 'ext'=>$type_aux);
						}
					}
				}
			}
			closedir($dh);
		}
		usort($data, 'compare_scripts');
		reset($data);
	}

	return $data;
}

function compare_scripts($script1, $script2) {
	return strcmp($script1['filename'], $script2['filename']);
}

function run_script($script) {
	global $db_path;
	$return = false;

	$ext = strtolower($script['ext']);
	$filename = $script['filename'];
	if ($ext == 'php') {
		// run the php script
		$result = array();
		$exec = 'php -f '.$db_path.$filename.' 2>&1';
		exec($exec, $result);

		if (count($result)) { // the script produced an output
			if ($result[count($result)-1] == 'SUCCESS') {
				show(realpath($db_path.$filename)." ran correctly\n\n");
				$return = true;
			} else {
				show(realpath($db_path.$filename)." FAILED!\n\n");
				show("Script output follows:\n");
				foreach ($result as $line) {
					show($line."\n");
				}
				show("[End of script output]\n");
			}
		} else {
			show(realpath($db_path.$filename)." FAILED!\n\n");
		}

	} elseif ($ext == 'sql') {
		// run the sql script
		$queries = array();
		if (run_sql_script($filename)) {
			show(realpath($db_path.$filename)." ran correctly\n\n");
			$return = true;
		} else {
			show(realpath($db_path.$filename)." FAILED!\n\n");
		}
	} else {
		// something went wrong
		show("\nThe script is not a PHP file nor an SQL file. Something went wrong. Please report this bug\n");
	}
	return $return;
}

function run_sql_script($filename) {
	global $db_path;

	$sql_file = $db_path.$filename;
	$file = @fopen($sql_file, 'rb');
	if (!$file) {
		return false;
	}
	$queries = parse_sql_file($db_path.$filename);

	$i = 0;

	//db_begin();

	foreach ($queries as $query) {
		$res = db_query_params($query, array());
		if (!$res) {
			show(db_error()."\n");
			show("QUERY: $query\n");
			show("Continue executing ([Y]es/[N]o)?\n");
			// Read the input
			$answer = strtolower(trim(fgets(STDIN)));
			if ($answer != 'y' && $answer != 'yes') {
				//db_rollback();
				return false;
			} else {
				//db_commit();
				//db_begin();
			}
		}
	}

	//db_commit();
	return true;
}

function in_string($haystack, $needle, $case_sensitive = false) {
	if (!$case_sensitive) {
		$haystack = strtolower($haystack);
	}
	if (strpos($haystack, $needle) !== false) {
		return true;
	} else {
		return false;
	}
}

function show($text) {
	//echo $text;
	fwrite(STDOUT, $text);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
