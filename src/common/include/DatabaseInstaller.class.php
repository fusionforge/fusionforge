<?php
/**
 * FusionForge Database Installer Class
 *
 * Copyright (C) 2010-2011 Alain Peyrat
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

class DatabaseInstaller extends Error {

	/**
	 * __construct - DatabaseInstaller constructor
	 *
	 * @param string $name If set, name of plugin.
	 * @param string $path If set, path of upgrade scripts.
	 */
	function __construct($name = '', $path = '') {
		$this->name = $name;
		$this->path = $path;
	}

	/**
	 * install - Run database install scripts (for plugins)
	 *
	 * @return bool|string Output
	 */
	function install() {
		$name = $this->name;
		$path = $this->path;

		$init = "$path/$name-init.sql";
		if (is_file($init)) {
			$ret = $this->_runScript($init);
			if (!$ret) {
				return false;
			}
			return $this->upgrade($name, $path);
		}
		return $this->setError(_('No database installation scripts found.'));
	}

	/**
	 * upgrade - Upgrade database with upgrade scripts (usually in src/sql)
	 *
	 * @return bool|string Output
	 */
	function upgrade() {
		$name = $this->name;
		$path = $this->path;

		if ($name) {
			$prefix = $name.':';
			$date   = -1;
		} else {
			$prefix = '';
			$date   = $this->getDatabaseDate();
		}

		$scripts = $this->_getScripts($path);
		$output  = '';
		foreach ($scripts as $script) {
			if ((int)$script['date'] > $date) {
				$res = db_query_params('SELECT * FROM database_changes WHERE filename=$1',
					array($prefix.$script['filename']));
				if (!$res) {
					return $this->setError("ERROR-2: ".db_error());
				} elseif (db_numrows($res) == 0) {
					$output .= "Running script: {$script['filename']}\n";
					$result = $this->_runScript($path.'/'.$script['filename']);
					if ($result) {
						$res = db_query_params('INSERT INTO database_changes (filename) VALUES ($1)',
							array($prefix.$script['filename']));
						if (!$res) {
							return $this->setError("ERROR-3: ".db_error());
						}
					} else {
						return false;
					}
				}
			}
		}
		return $output;
	}

	/**
	 * getDatabaseDate - Get status (date format) of the database.
	 *
	 * @return bool|int date if success, false otherwise.
	 */
	private function getDatabaseDate() {
		// Check if table 'database_startpoint' has proper values
		$res = db_query_params('SELECT * FROM database_startpoint', array());
		if (!$res) { // db error
			return $this->setError("DB-ERROR-3: ".db_error()."\n");
		} elseif (db_numrows($res) == 0) { // table 'database_startpoint' is empty
			return $this->setError("Table 'database_startpoint' is empty, run startpoint.php first.");
		}
		return (int)db_result($res, 0, 'db_start_date');
	}

	/**
	 * _runScript - Execute migration script given as argument (file)
	 *
	 * @param string $file Filename
	 * @return bool True if success
	 */
	private function _runScript($file) {
		// If a condition statement if found, then run the script only if true.
		$content = file($file);
		if (preg_match('/^-- TRUE\? (.+)/', $content[0], $match)) {
			$res = db_query_params($match[1], array());
			if (db_result($res, 0, 0) == 'f') {
				return true;
			}
		}
		$res = db_query_from_file($file);
		if ($res) {
			while ($res) {
				db_free_result($res);
				$res = db_next_result();
			}
		} else {
			return $this->setError(_('Database initialisation error:').' '.db_error());
		}
		return true;
	}

	/**
	 * _getScripts - Return a sorted array of available database upgrade scripts.
	 *
	 * @static
	 * @param string $dir
	 * @return array
	 */
	private static function _getScripts($dir) {
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
							if ((int)$date_aux > 20000000 && ($type_aux == 'sql' || $type_aux == 'php')
								&& strpos($file, 'debian') === false
							) {
								$data[] = array('date'=> $date_aux, 'filename'=> $file, 'ext'=> $type_aux);
							}
						}
					}
				}
				closedir($dh);
			}
			usort($data, array('DatabaseInstaller', '_compare_scripts'));
			reset($data);
		}
		return $data;
	}

	/**
	 * _compare_scripts - compare script for sorting (using filename)
	 *
	 * @static
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private static function _compare_scripts($a, $b) {
		return strcmp($a['filename'], $b['filename']);
	}
}
