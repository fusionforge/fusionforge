<?php
/**
 * FusionForge plugin system
 *
 * Copyright 2002, 2009, Roland Mas
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

class PluginManager extends Error {
	var $plugins_objects ;
	var $plugins_to_hooks ;
	var $hooks_to_plugins ;
	var $returned_value = array();

	/**
	 * PluginManager() - constructor
	 *
	 */
	function PluginManager () {
		$this->Error() ;
		$this->plugins_objects = array () ;
		$this->plugins_to_hooks = array () ;
		$this->hooks_to_plugins = array () ;
	}
	//to work with Codendi files
	static function instance() {
		global $PLUGINMANAGER_OBJ;
		if (!isset($PLUGINMANAGER_OBJ) || !$PLUGINMANAGER_OBJ) {
			$PLUGINMANAGER_OBJ = new PluginManager ;
		}
		return $PLUGINMANAGER_OBJ ;

	}
	/**
	 * GetPlugins() - get a list of installed plugins
	 *
	 * @return hash of plugin id => plugin names
	 */
	function GetPlugins () {
		if (!isset($this->plugins_data)) {
			$this->plugins_data = array () ;
			$res = db_query_params ('SELECT plugin_id, plugin_name FROM plugins',
					array ());
			$rows = db_numrows($res);
			for ($i=0; $i<$rows; $i++) {
				$plugin_id = db_result($res,$i,'plugin_id');
				$this->plugins_data[$plugin_id] = db_result($res,$i,'plugin_name');
			}
		}
		return $this->plugins_data ;
	}

	/**
	 * GetPluginObject() - get a particular plugin object
	 *
	 * @param	string	name of plugin
	 * @return	object	plugin object or false if not available
	 */
	function GetPluginObject ($pluginname) {
		if (!isset($this->plugins_objects [$pluginname])) {
			return false; 
		}
		return $this->plugins_objects [$pluginname] ;
	}

	//Added for Codendi compatibility
	function getPluginByName ($pluginname) {
		return @$this->plugins_objects [$pluginname] ;
	}
	/**
	 * PluginIsInstalled() - is a plugin installed?
	 *
	 * @param	string	name of plugin
	 * @return	boolean	true if installed
	 */
	function PluginIsInstalled ($pluginname) {
		$plugins_data = $this->getPlugins() ;
		foreach ($plugins_data as $p_id => $p_name) {
			if ($p_name == $pluginname) {
				return true ;
			}
		}
		return false ;
	}
	function isPluginAvailable ($plugin) {
		$pluginname = $plugin->GetName();
		$plugins_data = $this->getPlugins() ;
		foreach ($plugins_data as $p_id => $p_name) {
			if ($p_name == $pluginname) {
				return true ;
			}
		}
		return false ;
	}
	function activate($pluginname) {
		$res = db_query_params('INSERT INTO plugins (plugin_name,plugin_desc) VALUES ($1,$2)',
				array($pluginname, "This is the $pluginname plugin"));

		$res = db_query_params ('SELECT plugin_id, plugin_name FROM plugins WHERE plugin_name=$1',
				array ($pluginname));
		if (db_numrows($res) == 1) {
			$plugin_id = db_result($res,0,'plugin_id');
			$this->plugins_data[$plugin_id] = db_result($res,0,'plugin_name');
		} else {
			return false;
		}
		return $res;
	}

	function deactivate($pluginname) {
		$res = db_query_params('DELETE FROM plugins WHERE plugin_name = $1', array($pluginname));

		$p_id = NULL ;

		foreach ($this->plugins_data as $i => $n) {
			if ($n == $pluginname) {
				$p_id = $i;
			}
		}
		if ($p_id != NULL) {
			unset($this->plugins_data[$p_id]) ;
		}
		return $res;
	}

	/**
	 * LoadPlugin() - load a specific plugin
	 *
	 */
	function LoadPlugin ($p_name) {
		global $gfplugins, $gfcommon, $gfwww;

		$plugins_data = $this->GetPlugins() ;
		$include_path = forge_get_config('plugins_path') ;
		$filename = $include_path . '/'. $p_name . "/common/".$p_name."-init.php" ;
		if (file_exists ($filename)) {
			require_once ($filename) ;
		} else {
			$filename = $include_path . '/'. $p_name . "/common/".$p_name."Plugin.class.php" ;
			if (file_exists($filename)) {
				require_once($filename);
				$p_class = $p_name.'Plugin';
				register_plugin (new $p_class) ;
			} else { //if we didn't find it in common/ it may be an old plugin that has its files in include/
				$filename = $include_path . '/' . $p_name . "/include/".$p_name."-init.php" ;
				if (file_exists ($filename)) {
					require_once ($filename) ;
				} else {
					// we can't find the plugin so we remove it from the array
					foreach ($plugins_data as $i => $n) {
						if ($n == $p_name) {
							$p_id = $i;
						}
					}
					unset($this->plugins_data[$p_id]);
					return false ;
				}
			}
		}
		return true ;
	}

	/**
	 * LoadPlugins() - load available plugins
	 *
	 */
	function LoadPlugins () {
		$plugins_data = $this->GetPlugins() ;
		foreach ($plugins_data as $p_id => $p_name) {
			if (!$this->LoadPlugin($p_name)) {
				// we can't find the plugin so we remove it from the array
				unset($this->plugins_data[$p_id]);
			}
		}
		return true ;
	}

	/**
	 * SetupHooks() - setup all hooks for installed plugins
	 *
	 */
	function SetupHooks () {
		$this->hooks_to_plugins = array();
		foreach ($this->plugins_to_hooks as $p_name => $hook_list) {
			foreach ($hook_list as $hook_name) {
				if (!isset ($this->hooks_to_plugins[$hook_name])) {
					$this->hooks_to_plugins[$hook_name] = array () ;
				}
				$this->hooks_to_plugins[$hook_name][] = $p_name ;
			}
		}
		return true ;
	}

	/**
	 * RegisterPlugin() - register a plugin
	 *
	 * @param	object	an object of a subclass of the Plugin class
	 */
	function RegisterPlugin (&$pluginobject) {
		if (!$pluginobject->GetName ()) {
			exit_error (_("Some plugin did not provide a name. I'd gladly tell you which one, but obviously I can't. Sorry."),'') ;
		}
		$p_name = $pluginobject->GetName () ;
		$this->plugins_objects [$p_name] =& $pluginobject ;
		$this->plugins_to_hooks [$p_name] = $pluginobject->GetHooks () ;
		return true ;
	}

	/**
	 * RunHooks() - call hooks from a particular point
	 *
	 * @param hookname - name of the hook
	 * @param params - array of extra parameters
	 *
	 * @return boolean, true if all returned true.
	 */
	function RunHooks ($hookname, & $params) {
		$result = true;
		if (isset($this->hooks_to_plugins[$hookname])) {
			$p_list = $this->hooks_to_plugins[$hookname];
			foreach ($p_list as $p_name) {
				$p_obj = $this->plugins_objects[$p_name] ;
				if (method_exists($p_obj, $hookname)) {
					$returned = $p_obj->$hookname($params);
				} else {
					$returned = $p_obj->CallHook ($hookname, $params);
				}
				$this->returned_value[$hookname] = $returned;
				$result = $result && $returned ;
			}
		}
		// Return true only if all the plugins have returned true.
		return $result;
	}

	function getReturnedValue($hookname) {
		return $this->returned_value[$hookname];
	}

	/**
	 * CountHookListeners() - number of listeners on a particular hook
	 *
	 * @param	string	name of the hook
	 * @return	int	nb of listeners for this hookname
	 */
	function CountHookListeners ($hookname) {
		if (isset($this->hooks_to_plugins[$hookname])) {
			$p_list = $this->hooks_to_plugins[$hookname];
			return count ($p_list) ;
		} else {
			return 0 ;
		}
	}

	function isPluginAllowedForProject($p, $group_id) {
		$Group = group_get_object($group_id);
		return $Group->usesPlugin($p->getName());
	}
}

/**
 * plugin_manager_get_object() - get the PluginManager object
 *
 * @return the PluginManager object
 */
function &plugin_manager_get_object() {
	global $PLUGINMANAGER_OBJ;
	if (!isset($PLUGINMANAGER_OBJ) || !$PLUGINMANAGER_OBJ) {
		$PLUGINMANAGER_OBJ = new PluginManager ;
	}
	return $PLUGINMANAGER_OBJ ;
}

/**
 * plugin_get_object() - get a particular Plugin object
 *
 * @param pluginname - a plugin name
 * @return the Plugin object
 */
function &plugin_get_object ($pluginname) {
	global $PLUGINMANAGER_OBJ;
	$result=$PLUGINMANAGER_OBJ->Getpluginobject ($pluginname) ;
	return $result;
}

/**
 * register_plugin () - register a plugin
 *
 * @param pluginobject - an object of a subclass of the Plugin class
 */
function register_plugin (&$pluginobject) {
	$pm =& plugin_manager_get_object () ;
	return $pm->RegisterPlugin ($pluginobject) ;
}

/**
 * plugin_hook () - run a set of hooks
 *
 * @param hookname - name of the hook
 * @param params - parameters for the hook
 */
function plugin_hook ($hookname, $params = false) {
	$pm =& plugin_manager_get_object () ;
	return $pm->RunHooks ($hookname, $params) ;
}

/**
 * plugin_hook_by_reference () - run a set of hooks with params passed by reference
 *
 * @param hookname - name of the hook
 * @param params - parameters for the hook
 */
function plugin_hook_by_reference ($hookname, &$params) {
	$pm =& plugin_manager_get_object () ;
	return $pm->RunHooks ($hookname, $params) ;
}

/**
 * plugin_hook_listeners () - count the number of listeners on a hook
 *
 * @param hookname - name of the hook
 */
function plugin_hook_listeners ($hookname, $params=false) {
	$pm =& plugin_manager_get_object () ;
	return $pm->CountHookListeners ($hookname) ;
}

/**
 * setup_plugin_manager () - initialise the plugin infrastructure
 *
 */
function setup_plugin_manager () {
	$pm =& plugin_manager_get_object() ;
	$pm->LoadPlugins () ;
	$pm->SetupHooks () ;
	return true ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
