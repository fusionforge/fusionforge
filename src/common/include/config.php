<?php
/**
 * FusionForge configuration functions
 *
 * Copyright 2009, Roland Mas
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

// See docs in http://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/Configuration

/**
 *
 * Singleton FusionForge configuration database manager TODO : Enter better description here ...
 *
 */
class FusionForgeConfig {
	static protected $instance = NULL ;
	private $settings ;
	private $bools = array () ;

	/**
	 * Singleton accessor to the configuration database
	 * @return FusionForgeConfig instance
	 */
	static public function get_instance () {
		if (self::$instance == NULL) {
			self::$instance = new FusionForgeConfig () ;
		}
		return self::$instance ;
	}

	// TODO: add a constructor that initializes self::$instance to self ?

	public function get_sections () {
		return array_keys ($this->settings) ;
	}

	public function get_variables ($section='core') {
		if (isset ($this->settings[$section])) {
			return array_keys ($this->settings[$section]) ;
		}
		return array () ;
	}

	public function get_value ($section, $var) {
		if (!isset ($this->settings[$section])
		    || !isset ($this->settings[$section][$var])) {
			return NULL ;
		}

		$tmp = $this->settings[$section][$var] ;
		preg_match_all ('/\$[a-z_]+\/[a-z_]+/', $tmp, $matches) ;

		foreach ($matches[0] as $m) {
			$c = explode ('/', substr($m,1)) ;

			if (isset ($this->settings[$c[0]][$c[1]])) {
				$tmp = str_replace ($m, $this->get_value($c[0],$c[1]), $tmp) ;
			}
		}
		if ($this->is_bool ($section, $var)) {
			$tmp = $this->_interpret_as_bool ($tmp) ;
		}
		return $tmp ;
	}

	public function get_raw_value ($section, $var) {
		if (!isset ($this->settings[$section])
		    || !isset ($this->settings[$section][$var])) {
			return NULL ;
		}
		return $this->settings[$section][$var] ;
	}

	public function set_value ($section, $var, $value) {
		if (!isset ($this->settings[$section])) {
			$this->settings[$section] = array () ;
		}

		if (!isset ($this->settings[$section][$var])) {
			$this->settings[$section][$var] = $value ;
		}
	}

	public function reset_value ($section, $var, $value) {
		if (!isset ($this->settings[$section])) {
			$this->settings[$section] = array () ;
		}

		$this->settings[$section][$var] = $value ;
	}

	function read_config_file ($filename) {
		if (getenv ('FUSIONFORGE_USE_PRE_51_CONFIG') == 'true') {
			$fallback_only = true;
		} else {
			$fallback_only = false;
		}

		if (file_exists($filename) && is_readable($filename)) {
			$sections = parse_ini_file ($filename, true) ;
			if (is_array($sections)) {
				foreach ($sections as $section => $options) {
					foreach ($options as $var => $value) {
						if ($fallback_only) {
							$this->set_value($section,$var,$value);
						} else {
							$this->reset_value($section,$var,$value);
						}
					}
				}
			}
		}
		return ;
	}

	function mark_as_bool ($section, $var) {
		if (!array_key_exists ($section, $this->bools)) {
			$this->bools[$section] = array () ;
		}
		$this->bools[$section][$var] = true ;
	}

	function is_bool ($section, $var) {
		if (isset ($this->bools[$section])
		    && isset ($this->bools[$section][$var])) {
			return $this->bools[$section][$var] ;
		}
		return false ;
	}

	private function _interpret_as_bool ($val) {
		$val = strtolower ($val) ;
		switch ($val) {
		case 'true':
		case 'on':
		case 'yes':
		case '1':
			return true ;
		}

		return false ;
	}

  }

if (!isset ($fusionforge_config)) {
	$fusionforge_config = new FusionForgeConfig () ;
}

/**
 * Get value of variable "name" in section "section"
 * @param string $var
 * @param string $section defaults to "core" if missing
 * @return Ambigous <NULL, boolean>
 */
function forge_get_config ($var, $section = 'core') {
	$c = FusionForgeConfig::get_instance () ;
	return $c->get_value ($section, $var) ;
}

function forge_get_config_array () {
	$c = FusionForgeConfig::get_instance () ;

	$ret = array () ;

	foreach (func_get_args() as $item) {
		if (! is_array ($item)) {
			$item = array ($item) ;
		}
		$var = $item[0] ;
		if (isset ($item[1])) {
			$section = $item[1] ;
		} else {
			$section = 'core' ;
		}
		$ret[] = $c->get_value ($section, $var) ;
	}

	return $ret ;
}

function forge_set_vars_from_config () {
	$c = FusionForgeConfig::get_instance () ;

	foreach (func_get_args() as $item) {
		if (is_array ($item)) {
			$var = $item[0] ;
			$x = $var ;
			if (isset ($item[1])) {
				$section = $item[1] ;
				$x = $section.'__'.$var ;
				$value = forge_get_config ($var, $section) ;
			}
		} else {
			$var = $item ;
			$x = $item ;
			$value = forge_get_config ($var) ;
		}

		global $$x ;
		$$x = $value ;
	}
}


/**
 * Define a new configuration item with given name/section and default value
 * @param string $var name
 * @param string $section
 * @param any $default
 */
function forge_define_config_item ($var, $section, $default) {
	$c = FusionForgeConfig::get_instance () ;

	return $c->set_value ($section, $var, $default) ;
}

/**
 * Tag the variable as boolean, which allows human-readable values in the configuration files (such as yes, true, on and 1; anything else is mapped to false)
 * @param string $var name
 * @param string $section
 */
function forge_set_config_item_bool ($var, $section) {
	$c = FusionForgeConfig::get_instance () ;

	return $c->mark_as_bool ($section, $var) ;
}

/**
 * Read a *.ini file and inject its contents into the configuration database
 * @param string $file
 */
function forge_read_config_file ($filename) {
	$c = FusionForgeConfig::get_instance () ;

	return $c->read_config_file ($filename) ;
}

/**
 * Read all configuration files in a directory
 * @param string $path
 */
function forge_read_config_dir ($path) {
	$c = FusionForgeConfig::get_instance () ;

	$files = array () ;

	if (is_dir($path)){
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "."
			    	&& $file != ".."
			    	// Avoid .bak, .old, .dpkg-old and so on, but keep .ini
			    	&& preg_match ('/^[0-9a-zA-Z_-]+(.ini)?$/', $file)) {
					$files[] = "$path/$file" ;
				}
			}
			closedir($handle);
		}
	}

	natsort ($files) ;
	foreach ($files as $file) {
		$c->read_config_file ($file) ;
	}
}

function forge_reset_config_item ($var, $section, $default) {
	$c = FusionForgeConfig::get_instance () ;

	return $c->reset_value ($section, $var, $default) ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
