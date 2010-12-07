#! /usr/bin/php
<?php

// upgrade-db.php          => Upgrade the main database.
// upgrade-db.php all      => Upgrade the main database and active plugins.
// upgrade-db.php <plugin> => Upgrade only the database of the given active plugin.

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

/** 
* Sets up CLI environment based on SAPI and PHP version 
*/ 
if (version_compare(phpversion(), '4.3.0', '<') || php_sapi_name() == 'cgi') { 
   // Handle output buffering 
   @ob_end_flush(); 
   ob_implicit_flush(TRUE); 

   // PHP ini settings 
   set_time_limit(0);
   ini_set('track_errors', TRUE); 
   ini_set('html_errors', FALSE); 
   ini_set('magic_quotes_runtime', FALSE); 

   // Define stream constants 
   define('STDIN', fopen('php://stdin', 'r')); 
   define('STDOUT', fopen('php://stdout', 'w')); 
   define('STDERR', fopen('php://stderr', 'w')); 

   // Close the streams on script termination 
   register_shutdown_function( 
       create_function('', 
       'fclose(STDIN); fclose(STDOUT); fclose(STDERR); return true;') 
       ); 
}

$db_path = dirname(__FILE__).'/';
$date = -1;
$version = '';

// a huge message will warn not to run it without doing a backup first // warning.

// Check if table 'database_startpoint' exists
$res = db_query_params ('SELECT COUNT(*) AS proceed FROM pg_class WHERE relname = $1 AND relkind = $2',
			array('database_startpoint',
			'r')) ;

if (!$res) { // db error
	show("DB-ERROR-2: ".db_error()."\n");
	exit();
} else {
	$proceed = db_result($res, 0, 'proceed');
	if (!$proceed) { // table does not exist
		show("ERROR: table 'database_startpoint' does not exist.\nRun startpoint.php first.\n");
		exit();
	} else {
		// Check if table 'database_startpoint' has proper values
		$res = db_query_params ('SELECT * FROM database_startpoint',
			array()) ;

		if (!$res) { // db error
			show("DB-ERROR-3: ".db_error()."\n");
			exit();
		} else if (db_numrows($res) == 0) { // table 'database_startpoint' is empty
			show("ERROR: table 'database_startpoint' is empty.\nRun startpoint.php first.\n");
			exit();
		} else { // get the start date from the db
			$date = (int) db_result($res, 0, 'db_start_date');
			$version = db_result($res, 0, 'db_version');
		}
	}
}

if (!apply_fixes($version)) {
	show("ERROR applying fixes to version $version!\n");
	exit();
}

// Upgrade main database if no argument or if all)
if ($argc == 1 || $argv[1] == 'all') {
	$scripts = get_scripts($db_path);
	foreach ($scripts as $script) {
		if ((int) $script['date'] > $date) {
			$res = db_query_params ('SELECT * FROM database_changes WHERE filename=$1',
						array ("{$script['filename']}")) ;
			if (!$res) {
				// error
				show("ERROR-2: ".db_error()."\n");
				exit();
			} else if (db_numrows($res) == 0) {
				show("Running script: {$script['filename']}\n");
				$result = run_script($script);
				if ($result) {
					$res = db_query_params ('INSERT INTO database_changes (filename) VALUES ($1)',
								array ("{$script['filename']}")) ;
					if (!$res)
					{
						show("ERROR-3: ".db_error()."\n");
						exit();
					}
				} else {
					// error
					exit();
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
				show($db_path.$filename." ran correctly\n\n");
				$return = true;
			} else {
				show($db_path.$filename." FAILED!\n\n");
				foreach ($result as $line) {
					show($line."\n");
				}
			}
		} else {
			show($db_path.$filename." FAILED!\n\n");
		}
		
	} else if ($ext == 'sql') {
		if (//$filename == '20021124-3_gforge-debian-sf-sync.sql' ||
			$filename == '20021223-drops.sql') {
//20021223-drops.sql
echo "\nskipping $filename";
			$return = true;
		} else {
			// run the sql script
			$queries = array();
			if (run_sql_script($filename)) {
				show($db_path.$filename." ran correctly\n\n");
				$return = true;
			} else {
				show($db_path.$filename." FAILED!\n\n");
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
	$content = fread($file, filesize($sql_file));
	fclose($file);
	
	$content = preg_replace("/--(.*)/", '', $content);
	
	$parts = explode(";\n", $content);
	$queries = array();
	$query_temp = '';
	$is_function = false;
	$is_copy_stdin = false;
	
	for ($i=0;$i<count($parts);$i++) {
		$q = $parts[$i];
		// Check if it's a function
		if ((in_string($q, 'create function') || in_string($q, 'create or replace function') ||
			in_string($q, 'replace function')) && !in_string($q, 'language plpgsql') &&
			!in_string($q, 'language \'plpgsql\'')&& !in_string($q, 'language \'c\'') &&
			!in_string($q, 'language c')) {

			while (!in_string($q, 'language plpgsql') && !in_string($q, 'language \'plpgsql\'')) {
				$i++;
				$q = $q.';'.$parts[$i];
			}
			$queries[] = trim($q);
		// Check if it is a COPY FROM stdin
		} else if (in_string($q, 'copy') && in_string($q, 'from stdin')) {
			while (!in_string($q, '\.')) {
				$i++;
				$q = $q.";\n".$parts[$i];
			}
			$aux = explode('\.', $q, 2);
			$queries[] = ltrim($aux[0]."\\.\n");
			if (trim($aux[1]) != '') {
				$queries[] = trim($aux[1]);
			}
		// Else, we just add it up
		} else {
			if (trim($q) != '') {
				$queries[] = trim(preg_replace("/\s+/", ' ', str_replace("\n", ' ', $q)));
			}
		}
	}

	$i = 0;

	//db_begin();
	
	foreach ($queries as $query) {
		// Check if it is a DROP TABLE
		if (in_string($query, 'drop table')) {
			$aux = explode(' ', trim($query));
			if (count($aux) == 3 || count($aux) == 4) { // PERFECT!
				drop_table_if_exists($aux[2], (count($aux) == 4) && preg_match('/CASCADE/i', trim($aux[3])));
			} else {
				print_r($aux);
			}
		// Check if it is a DROP SEQUENCE
		} else if (in_string($query, 'drop sequence')) {
			$aux = explode(' ', trim($query));
			if (count($aux) == 3) { // PERFECT!
				drop_seq_if_exists($aux[2]);
			} else {
				print_r($aux);
			}
		// Check if it is a DROP TRIGGER
		} else if (in_string($query, 'drop trigger')) {
			$aux = explode(' ', trim($query));
			if (count($aux) == 5 || count($aux) == 6) { // PERFECT!
				drop_trigger_if_exists($aux[2], $aux[4]);
			} else {
				print_r($aux);
			}
		// Check if it is a DROP VIEW
		} else if (in_string($query, 'drop view')) {
			$aux = explode(' ', trim($query));
			if (count($aux) == 3 || count($aux) == 4) { // PERFECT!
				drop_view_if_exists($aux[2]);
			} else {
				print_r($aux);
			}
		// Check if it is a DROP INDEX
		} else if (in_string($query, 'drop index')) {
			$aux = explode(' ', trim($query));
			if (count($aux) == 3 || count($aux) == 4) { // PERFECT!
				drop_index_if_exists($aux[2]);
			} else {
				print_r($aux);
			}
		// Check if it is a DROP CONSTRAINT
		} else if (in_string($query, 'alter table') && in_string($query, 'drop constraint')) {
			$aux = explode(' ', trim($query));
			$table = trim($aux[2], "\" ");
			$constraint = trim($aux[5], "\" ");
			
			drop_constraint_if_exists($table, $constraint, $query);
		} else {
			$res = db_query($query);
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
	} else if ($version == 'sfee3.0') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('sfee3.0fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			run_script(array('filename'=>'sfee3.0-sf26-1.sql','ext'=>'sql'));
			run_script(array('filename'=>'sfee3.0-sf26-2.php','ext'=>'php'));
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('sfee3.0fixes')";
		}
	} else if ($version == '2.5') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('2.5fixes'));
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 2.5\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('2.5fixes')";
		}
	} else if ($version == '2.6') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('2.6fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 2.6\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('2.6fixes')";
		}
	} else if ($version == '3.0pre5') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('3.0pre5fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 3.0pre5\n");
			if (!run_sql_script('fix-gforge3.0pre5.sql')) {
				show("Error applying fixes for version 3.0pre5\n");
				//exit();
			}
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('3.0pre5fixes')";
		}
	} else if ($version == '3.0pre6') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1', array ('3.0pre6fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 3.0pre6\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('3.0pre6fixes')";
		}
	} else if ($version == '3.0pre7') {
		$res = db_query_params ('SELECT COUNT(*) AS applied FROM database_changes WHERE filename=$1',
					array ('3.0pre7fixes')) ;
		if ($res && db_result($res, 0, 'applied') == '0') {
			show("Applying fixes for version 3.0pre7\n");
			$queries[] = "ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_f CHECK (1 = 1)";
			$queries[] = "INSERT INTO database_changes (filename) VALUES ('3.0pre7fixes')";
		}
	} else if ($version == '4.7') {
		run_script(array('filename'=>'20070924-project-perm.sql','ext'=>'sql'));
		run_script(array('filename'=>'20070924-forum-perm.sql','ext'=>'sql'));
		run_script(array('filename'=>'20070924-artifact-perm.sql','ext'=>'sql'));
	}

	//db_begin();
	foreach ($queries as $query) {
		$res = db_query($query);
		if (!$res) {
			show("ERROR: ".db_error()."\n");
	//		db_rollback();
			return false;
		}
	}
	//db_commit();
	return true;
}

function drop_view_if_exists($name) {
	$result = drop_if_exists($name, 'DROP VIEW', 'v');
	return $result;
}

function drop_seq_if_exists($name) {
	$result = drop_if_exists($name, 'DROP SEQUENCE', 'S');
	return $result;
}

function drop_index_if_exists($name) {
	$result = drop_if_exists($name, 'DROP INDEX', 'i');
	return $result;
}

function drop_table_if_exists($name, $cascade) {
	if($cascade)  {
		$result = drop_if_exists($name, 'DROP TABLE', 'r', 'CASCADE');
	} else {
		$result = drop_if_exists($name, 'DROP TABLE', 'r');
	}
	return $result;
}

function drop_if_exists($name, $command, $kind, $commandSuffix = '') {
	// Strip "name" => name
	if (preg_match('/^"(.*)"$/', $name, $match)) {
		$name = $match[1];
	}
	$res = db_query_params ('SELECT COUNT(*) AS exists FROM pg_class WHERE relname=$1 AND relkind=$2',
				array ($name,
				       $kind)) ;
	if (!$res) {
		show("ERROR:".db_error()."\n");
		return false;
	}
	if (db_result($res, 0, 'exists') != '0') {
		$res = db_query("$command $name $commandSuffix");
		if (!$res) {
			show("ERROR:".db_error()."\n");
			//db_rollback();
			//exit();
		}
	}
	return true;
}

function drop_constraint_if_exists($table, $constraint, $query) {
	$res = db_query_params ('SELECT COUNT(*) AS exists FROM information_schema.constraint_table_usage WHERE table_name=$1 AND constraint_name=$2',
			array($table,
			$constraint)) ;

	if (!$res) {
		show("ERROR:".db_error()."\n");
		return false;
	}
	if (db_result($res, 0, 'exists') != '0') {
		$res = db_query($query);
		if (!$res) {
			show("ERROR:".db_error()."\n");
		}
	}
	return true;
}

function drop_trigger_if_exists($name, $on) {
	$res = db_query_params ('SELECT COUNT(*) AS exists FROM pg_trigger WHERE tgname=$1',
				array ($name)) ;
	if (!$res) {
		show("ERROR:".db_error()."\n");
		return false;
	}
	if (db_result($res, 0, 'exists') != '0') {
		$res = db_query("DROP TRIGGER $name ON $on");
		if (!$res) {
			show("ERROR:".db_error()."\n");
			//db_rollback();
			//exit();
		}
	}
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
