<?php
/**
 * GForge Plugin FCKeditor Plugin Class
 *
 * Copyright 2005 (c) Daniel A. Pérez <daniel@gforgegroup.com> , <danielperez.arg@gmail.com>
 *
 * This file is part of GForge-plugin-fckeditor
 *
 * GForge-plugin-fckeditor is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-fckeditor is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-fckeditor; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 *
 */
 
/**
 * The fckeditorPlugin class. It implements the Hooks for the presentation
 *  of the text editor whenever needed
 *
 */

class fckeditorPlugin extends Plugin {
	function fckeditorPlugin () {
		$this->Plugin() ;
		$this->name = "fckeditor" ;
		$this->text = "FCKeditor";
		$this->hooks[] = "groupisactivecheckbox";
		$this->hooks[] = "groupisactivecheckboxpost";
		$this->hooks[] = "text_editor"; // shows the editor
	}

	/**
	* The function to be called for a Hook
	*
	* @param    String  $hookname  The name of the hookname that has been happened
	* @param    String  $params    The params of the Hook
	*
	*/
	function CallHook ($hookname, $params) {
		global $group_id, $sys_default_domain ;

		if (file_exists ("/usr/share/fckeditor/fckeditor.php")) {
			$use_system_fckeditor = true ;
			require_once("/usr/share/fckeditor/fckeditor.php");
		} else {
			$use_system_fckeditor = false ;
			require_once($GLOBALS['sys_plugins_path']."fckeditor/www/fckeditor.php");
		}

		if ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_fckeditorplugin" value="1" ';
			// Checked or Unchecked?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo "><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "groupisactivecheckboxpost") {
			$group = &group_get_object($group_id);
			if ( getStringFromRequest('use_fckeditorplugin') == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "text_editor") {
			$group_id=$params['group']; // get the project id
			$project = &group_get_object($group_id);
			if ( (!$project) || (!is_object($project)) || ($project->isError()) || (!$project->isProject()) ) {
				return false;
			}
			if ( $project->usesPlugin ( $this->name ) ) { // only if the plugin is activated for the project show the fckeditor box
				if (strtoupper(getStringFromServer('HTTPS')) == 'ON') {
					$http = "https://";
				} else {
					$http = "http://";
				}
				if ($params['name']) {
					$oFCKeditor = new FCKeditor($params['name']) ;
				} else {
					$oFCKeditor = new FCKeditor('body') ;
				}
				if ($use_system_fckeditor) {
					$oFCKeditor->BasePath = $http . $sys_default_domain  . '/fckeditor/';
					$oFCKeditor->Config['CustomConfigurationsPath'] = "/plugins/fckeditor/config.js"  ;
				} else {
					$oFCKeditor->BasePath = $http . $sys_default_domain  . '/plugins/' . $this->name . '/';
				}
				$oFCKeditor->Value = $params['body']; // this is the initial text that will be displayed (if any)
				$oFCKeditor->Width = $params['width'];
				$oFCKeditor->Height = $params['height'];
				$oFCKeditor->ToolbarSet = "GForge";
				$oFCKeditor->Create() ;
				$GLOBALS['editor_was_set_up'] = true;
			} else {
				return false;
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
