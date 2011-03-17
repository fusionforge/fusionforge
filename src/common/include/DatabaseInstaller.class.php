<?php
/**
 * FusionForge Database Installer Class
 *
 * Copyright (C) 2010-2011 Alain Peyrat
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

class DatabaseInstaller extends Error {
	
	function DatabaseInstaller($name='', $path='') {
		$this->name = $name;
		$this->path	= $path;	
	}

	/**
	 * TODO: Enter description here ...
	 * @return boolean|Ambigous <boolean, string>
	 */
	function install() {
		$name = $this->name;
		$path = $this->path;

		$init = "$path/$name-init.sql";
		if (is_file($init)) {
			$ret = $this->runScript($init);
			if (!$ret) {
				return false;
			}
			return $this->upgrade($name, $path);
		}
		return $this->setError(_('No database installation scripts found.'));
	}
	
	/**
	 * TODO: Enter description here ...
	 * @return boolean|string
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

		$scripts = $this->getScripts($path);
		$output  = '';
		foreach ($scripts as $script) {
			if ((int) $script['date'] > $date) {
				$res = db_query_params ('SELECT * FROM database_changes WHERE filename=$1',
					array ($prefix.$script['filename'])) ;
				if (!$res) {
					return $this->setError("ERROR-2: ".db_error());
				} else if (db_numrows($res) == 0) {
					$output .= "Running script: {$script['filename']}\n";
					$result = $this->runScript($path.'/'.$script['filename']);
					if ($result) {
						$res = db_query_params ('INSERT INTO database_changes (filename) VALUES ($1)',
							array ($prefix.$script['filename'])) ;
						if (!$res) {
							return $this->setError("ERROR-3: ".db_error());
						}
					} else {
						return false;
					}
				} else {
//					$output .= "Skipping script: $prefix{$script['filename']}\n";
				}
			}
		}
		return $output;
	}
	
	private static function getDatabaseDate() {
		// Check if table 'database_startpoint' has proper values
		$res = db_query_params ('SELECT * FROM database_startpoint', array()) ;
		if (!$res) { // db error
			return $this->setError("DB-ERROR-3: ".db_error()."\n");
		} else if (db_numrows($res) == 0) { // table 'database_startpoint' is empty
			return $this->setError("Table 'database_startpoint' is empty, run startpoint.php first.");
		} else { // get the start date from the db
			return (int) db_result($res, 0, 'db_start_date');
		}
		return false;
	}

	private function runScript($file) {
		// If a condition statement if found, then run the script only if true.
		$content = file($file);
		if (preg_match('/^-- TRUE\? (.+)/', $content[0], $match)) {
			$res = db_query_params($match[1], array());
			if (db_result($res,0,0) == 'f') {
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

	private static function getScripts($dir) {
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
							if ((int) $date_aux > 20000000 && ($type_aux=='sql' || $type_aux=='php') 
								&& strpos($file, 'debian') === false) {
								$data[] = array('date'=>$date_aux, 'filename'=>$file, 'ext'=>$type_aux);
							}
						}
					}
				}
				closedir($dh);
			}
			usort($data, array('DatabaseInstaller', 'compare_scripts'));
			reset($data);
		}
		return $data;
	}

	private static function compare_scripts($script1, $script2) {
		return strcmp($script1['filename'], $script2['filename']);
	}
}
