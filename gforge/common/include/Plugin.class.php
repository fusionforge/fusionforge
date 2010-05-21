<?php
/**
 * FusionForge plugin system
 *
 * Copyright 2002, Roland Mas
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
	 * GetInstallDir() - get installation dir for the plugin
	 *
	 * @return the directory where the plugin should be linked.
	 */
	function GetInstallDir () {
		if (isset($this->installdir) && $this->installdir)
			return $this->installdir;
		else
			return 'plugins/'.$this->name ;
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
	function CallHook ($hookname, $params) {
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
			$this->role->global_values[] = $this->name ;
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
