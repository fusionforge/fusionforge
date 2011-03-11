<?php
/**
 * FusionForge plugin system
 *
 * Copyright 2002, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
 *
 * Portions Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */

class Plugin extends Error {
	var $name ;
	var $hooks ;

	/**
	 * Plugin() - constructor
	 *
	 */
	function Plugin ($id=0) {
		$this->Error() ;
		$this->name = false ;
		$this->hooks = array () ;
	}

	/**
	 * GetHooks() - get list of hooks to subscribe to
	 *
	 * @return List of strings
	 */
	function GetHooks () {
		return $this->hooks ;
	}
	/**
	 * _addHooks() - add a hook to the list of hooks
	 */
	function _addHook ($name) {
		return $this->hooks[]=$name ;
	}

	/**
	 * GetName() - get plugin name
	 *
	 * @return the plugin name
	 */
	function GetName () {
		return $this->name ;
	}

	/**
	 * getInstallDir() - get installation dir for the plugin
	 *
	 * @return the directory where the plugin should be linked.
	 */
	function getInstallDir() {
		if (isset($this->installdir) && $this->installdir)
			return $this->installdir;
		else
			return 'plugins/'.$this->name ;
	}

	/**
	 * provide() - return true if plugin provides the feature.
	 *
	 * @return bool if feature is provided or not.
	 */
	function provide($feature) {
		return (isset($this->provides[$feature]) && $this->provides[$feature]);
	}

	/**
	 * Added for Codendi compatibility
	 * getPluginPath() - get installation dir for the plugin
	 *
	 * @return the directory where the plugin should be linked.
	 */
	function getPluginPath () {
		if (isset($this->installdir) && $this->installdir)
			return $this->installdir;
		else
			return 'plugins/'.$this->name ;
	}

	/**
	 * CallHook() - call a particular hook
	 *
	 * @param hookname - the "handle" of the hook
	 * @param params - array of parameters to pass the hook
	 */
	function CallHook ($hookname, &$params) {
		return true ; 
	}

	/**
	 *  getGroups -  get a list of all groups using a plugin
	 *
	 *  @return	array	array containing group ids
	 */
	function getGroups() {
		$result = array () ;
		$res = db_query_params ('SELECT group_plugin.group_id
				           FROM group_plugin, plugins
                                           WHERE group_plugin.plugin_id=plugins.plugin_id
                                             AND plugins.plugin_name=$1
                                         ORDER BY group_plugin.group_id ASC',
					array ($this->name));
		$rows = db_numrows($res);
		
		for ($i=0; $i<$rows; $i++) {
			$group_id = db_result($res,$i,'group_id');
			$result[] = group_get_object ($group_id) ;
		}
		
		return $result ;
	}

	function getThemePath(){
		return util_make_url('plugins/'.$this->name.'/themes/default');
	}

	function registerRoleValues(&$params, $values) {
		$role =& $params['role'] ;
	}

	function install() {
		$this->installCode();
		$this->installConfig();
		$this->installDatabase();
	}

	function installCode() {
		$name = $this->name;
		$path = forge_get_config('plugins_path') . '/' . $name;
		$installdir = $this->getInstallDir();

		// Create a symbolic links to plugins/<plugin>/www (if directory exists).
		if (is_dir($path . '/www')) { // if the plugin has a www dir make a link to it
			// The apache group or user should have write perms the www/plugins folder...
			if (!is_link('../'.$installdir)) {
				$code = symlink($path . '/www', '../'.$installdir);
				if (!$code) {
					$this->setError('['.'../'.$installdir.'->'.$path . '/www]'.
						'<br />Soft link to www couldn\'t be created. Check the write permissions for apache in gforge www/plugins dir or create the link manually.');
				}
			}
		}

		// Create a symbolic links to plugins/<plugin>/etc/plugins/<plugin> (if directory exists).
		if (is_dir($path . '/etc/plugins/' . $name)) {
			// The apache group or user should have write perms in /etc/gforge/plugins folder...
			if (!is_link(forge_get_config('config_path'). '/plugins/'.$name) && !is_dir(forge_get_config('config_path'). '/plugins/'.$name)) {
				$code = symlink($path . '/etc/plugins/' . $name, forge_get_config('config_path'). '/plugins/'.$name);
				if (!$code) {
					$this->setError('['.forge_get_config('config_path'). '/plugins/'.$name.'->'.$path . '/etc/plugins/' . $name . ']'.
					_('<br />Config file could not be linked to etc/gforge/plugins/%1$s. Check the write permissions for apache in /etc/gforge/plugins or create the link manually.'), $name);
				}
			}
		}
	}

	function installConfig() {
		$name = $this->name;
		$path = forge_get_config('plugins_path') . '/' . $name;

		// Create a symbolic links to plugins/<plugin>/etc/plugins/<plugin> (if directory exists).
		if (is_dir($path . '/etc/plugins/' . $name)) {
			// The apache group or user should have write perms in /etc/gforge/plugins folder...
			if (!is_link(forge_get_config('config_path'). '/plugins/'.$name) && !is_dir(forge_get_config('config_path'). '/plugins/'.$name)) {
				$code = symlink($path . '/etc/plugins/' . $name, forge_get_config('config_path'). '/plugins/'.$name);
				if (!$code) {
					$this->setError('['.forge_get_config('config_path'). '/plugins/'.$name.'->'.$path . '/etc/plugins/' . $name . ']'.
					_('<br />Config file could not be linked to etc/gforge/plugins/%1$s. Check the write permissions for apache in /etc/gforge/plugins or create the link manually.'), $name);
				}
			}
		}
	}

	function installDatabase() {
		$name = $this->name;
		$path = forge_get_config('plugins_path') . '/' . $name . '/db';

		require_once $GLOBALS['gfcommon'].'include/DatabaseInstaller.class.php';
		$di = new DatabaseInstaller($name, $path);

		// Search for database tables, if present then upgrade.
		$res=db_query_params ('SELECT COUNT(*) FROM pg_class WHERE (relname=$1 OR relname like $2) AND relkind=$3',
			array ('plugin_'.$name, 'plugin_'.$name.'_%', 'r'));
		$count = db_result($res,0,0);
		if ($count == 0) {
			$di->install();
		} else {
			$di->upgrade();
		}
	}

	function groupisactivecheckbox (&$params) {
		//Check if the group is active
		// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
		$group = group_get_object($params['group']);
		$flag = strtolower('use_'.$this->name);
		echo "<tr>";
		echo "<td>";
		echo ' <input type="checkbox" name="'.$flag.'" value="1" ';
		// checked or unchecked?
		if ( $group->usesPlugin ( $this->name ) ) {
			echo "checked=\"checked\"";
		}
		echo " /><br/>";
		echo "</td>";
		echo "<td>";
		echo "<strong>Use ".$this->text." Plugin</strong>";
		echo "</td>";
		echo "</tr>";
	}

	function groupisactivecheckboxpost (&$params) {
		// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
		$group = group_get_object($params['group']);
		$flag = strtolower('use_'.$this->name);
		if ( getIntFromRequest($flag) == 1 ) {
			$group->setPluginUse ( $this->name );
		} else {
			$group->setPluginUse ( $this->name, false );
		}
		return true;
	}

	function userisactivecheckbox (&$params) {
		//check if user is active
		// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
		$user = $params['user'];
		$flag = strtolower('use_'.$this->name);
		echo "<tr>";
		echo "<td>";
		echo ' <input type="checkbox" name="'.$flag.'" value="1" ';
		// checked or unchecked?
		if ( $user->usesPlugin ( $this->name ) ) {
			echo 'checked="checked"';
		}
		echo " />    Use ".$this->text." Plugin";
		echo "</td>";
		echo "</tr>";
	}

	function userisactivecheckboxpost (&$params) {
		// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
		$user = $params['user'];
		$flag = strtolower('use_'.$this->name);
		if (getIntFromRequest($flag) == 1) {
			$user->setPluginUse ( $this->name );
		} else {
			$user->setPluginUse ( $this->name, false );
		}
		echo "<tr>";
		echo "<td>";
		echo ' <input type="checkbox" name="'.$flag.'" value="1" ';
		// checked or unchecked?
		if ( $user->usesPlugin ( $this->name ) ) {
			echo 'checked="checked"';
		}
		echo " />    Use ".$this->text." Plugin";
		echo "</td>";
		echo "</tr>";
	}
}

class PluginSpecificRoleSetting {
	var $role ;
	var $name = '' ;
	var $section = '' ;
	var $values = array () ;
	var $default_values = array () ;
	var $global = false ;

	function PluginSpecificRoleSetting (&$role, $name, $global = false) {
		$this->global = $global ;
		$this->role =& $role ;
		$this->name = $name ;
	}
	
	function SetAllowedValues ($values) {
		$this->role->role_values = array_replace_recursive ($this->role->role_values,
								    array ($this->name => $values)) ;
		if ($this->global) {
			$this->role->global_settings[] = $this->name ;
		}
	}

	function SetDefaultValues ($defaults) {
		foreach ($defaults as $rname => $v) {
			$this->role->defaults[$rname][$this->name] = $v ;
		}
	}

	function setValueDescriptions ($descs) {
		global $rbac_permission_names ;
		foreach ($descs as $k => $v) {
			$rbac_permission_names[$this->name.$k] = $v ;
		}
	}

	function setDescription ($desc) {
		global $rbac_edit_section_names ;
		$rbac_edit_section_names[$this->name] = $desc ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
