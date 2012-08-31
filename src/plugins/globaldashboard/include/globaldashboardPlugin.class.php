<?php

/**
 * globaldashboardPlugin Class
 *
 * Copyright 2011, Sabri LABBENE - Institut Télécom
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class globaldashboardPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id);
		$this->name = "globaldashboard";
		$this->text = "Global Dashboard"; // Text to show in the tabs

		$this->_addHook("usermenu"); // Shows in the tabs an entry for the plugin.
		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //

		$this->_addHook('widget_instance'); // creates widgets when requested
		$this->_addHook('widgets'); // declares which widgets are provided by the plugin
	}

	function usermenu() {
		global $G_SESSION,$HTML;
		$text = $this->text; // this is what shows in the tab
		if ($G_SESSION->usesPlugin("globaldashboard")) {
			$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
			echo $HTML->PrintSubMenu (array ($text),
			array ('/plugins/globaldashboard/index.php' . $param ), array());	
		}
	}
	
	function widgets(&$params) {
		require_once('common/widget/WidgetLayoutManager.class.php');
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_USER) {
			$params['fusionforge_widgets'][] = 'plugin_globalDashboard_MyProjects';
			$params['fusionforge_widgets'][] = 'plugin_globalDashboard_MyArtifacts';
		}
		return true;
	}
	
	/**
	 * Process the 'widget_instance' hook to create instances of the widgets
	 * @param array $params
	 */
	function widget_instance($params) {
		global $gfplugins;
		//$user = UserManager::instance()->getCurrentUser();
		require_once('common/widget/WidgetLayoutManager.class.php');
		if ($params['widget'] == 'plugin_globalDashboard_MyProjects') {
			require_once $gfplugins.$this->name.'/include/globalDashboard_Widget_MyProjects.php';
			$params['instance'] = new globalDashboard_Widget_MyProjects(WidgetLayoutManager::OWNER_TYPE_USER, $this);
		} 
		if ($params['widget'] == 'plugin_globalDashboard_MyArtifacts') {
			require_once $gfplugins.$this->name.'/include/globalDashboard_Widget_MyArtifacts.php';
			$params['instance'] = new globalDashboard_Widget_MyArtifacts(WidgetLayoutManager::OWNER_TYPE_USER, $this);
		}
	}
	
	// TODO: move this to its corresponding widget.
	function getMyArtifacts() {
		return array('Artifact 1', 'Artifact 2');
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
