#! /usr/bin/php
<?php

// upgrade-db.php          => Upgrade the main database.
// upgrade-db.php all      => Upgrade the main database and active plugins.
// upgrade-db.php <plugin> => Upgrade only the database of the given active plugin.
echo "Entering  upgrade-db.php\n";

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/sqlparser.php';

$db_path = dirname(__FILE__).'/';
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

if (!apply_fixes($version)) {
	show("ERROR applying fixes to version $version!\n");
	exit(1);
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

// Upgrade activated plugins.
if ($argc == 2) {
	require_once $gfcommon.'include/DatabaseInstaller.class.php';
	$plugins = get_installed_plugins();
	foreach ($plugins as $plugin) {
		if ($argv[1] == 'all' || $argv[1] == $plugin) {
			$di = new DatabaseInstaller($plugin, dirname($db_path) . '/plugins/' . $plugin . '/db');
			echo $di->upgrade();
		}
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
						if ((int) $date_aux > 20000000 && ($type_aux=='sql' || $type_aux=='php') && strpos($file, 'debian') === false) {
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
		$exec = 'php -f '.$db_path.$filename;
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
		if (//$filename == '20021124-3_gforge-debian-sf-sync.sql' ||
			$filename == '20021223-drops.sql') {
//20021223-drops.sql
echo "\nskipping $filename";
			$return = true;
		} else {
			// run the sql script
			$queries = array();
			if (run_sql_script($filename)) {
				show(realpath($db_path.$filename)." ran correctly\n\n");
				$return = true;
			} else {
				show(realpath($db_path.$filename)." FAILED!\n\n");
			}
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

	// Patch for some 3.0preX versions
	if ($filename == '20021216.sql') {
		db_query_params ('SELECT setval($1, (SELECT MAX(theme_id) FROM themes), true)',
			array('themes_theme_id_key')) ;

		show("Applying fix for some 3.0preX versions\n");
	}

	//db_commit();
	return true;
}

function apply_fixes($version) {
	$queries = array();
	if ($version == 'sfee3.3') {
		$res = db_query_params('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1', array ('sfee3.3fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Converting SFEE3.3 to SFEE3.0\n");
			run_script(array('filename'=>'sfee3.3-3.0-1.sql','ext'=>'sql'));
			run_script(array('filename'=>'sfee3.3-3.0-2.php','ext'=>'php'));
			run_script(array('filename'=>'sfee3.3-3.0-3.sql','ext'=>'sql'));
			show("Converting SFEE3.0 to SF2.6\n");
//sfee3.0-sf26-1.sql
			run_script(array('filename'=>'sfee3.0-sf26-1.sql','ext'=>'sql'));
			run_script(array('filename'=>'sfee3.0-sf26-2.php','ext'=>'php'));
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('sfee3.3fixes')";
		}
	} elseif ($version == 'sfee3.0') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('sfee3.0fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			run_script(array('filename'=>'sfee3.0-sf26-1.sql','ext'=>'sql'));
			run_script(array('filename'=>'sfee3.0-sf26-2.php','ext'=>'php'));
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('sfee3.0fixes')";
		}
	} elseif ($version == '2.5') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('2.5fixes'));
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 2.5\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('2.5fixes')";
		}
	} elseif ($version == '2.6') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('2.6fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 2.6\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('2.6fixes')";
		}
	} elseif ($version == '3.0pre5') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('3.0pre5fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 3.0pre5\n");
			if (!run_sql_script('fix-gforge3.0pre5.sql')) {
				show("Error applying fixes for version 3.0pre5\n");
				//exit(1);
			}
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('3.0pre5fixes')";
		}
	} elseif ($version == '3.0pre6') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1', array ('3.0pre6fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 3.0pre6\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('3.0pre6fixes')";
		}
	} elseif ($version == '3.0pre7') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('3.0pre7fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 3.0pre7\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('3.0pre7fixes')";
		}
	} elseif ($version == '4.7') {
		run_script(array('filename'=>'20070924-project-perm.sql','ext'=>'sql'));
		run_script(array('filename'=>'20070924-forum-perm.sql','ext'=>'sql'));
		run_script(array('filename'=>'20070924-artifact-perm.sql','ext'=>'sql'));
	}

	//db_begin();
	foreach ($queries as $query) {
		$res = db_query_params($query, array());
		if (!$res) {
			show("ERROR: ".db_error()."\n");
	//		db_rollback();
			return false;
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

?>
