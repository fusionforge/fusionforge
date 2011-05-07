<?php
/**
 * FusionForge Plugin FCKeditor Plugin Class
 *
 * Copyright 2005 (c) Daniel A. Pérez <daniel@gforgegroup.com> , <danielperez.arg@gmail.com>
 * Copyright 2011, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge-plugin-fckeditor
 *
 * FusionForge-plugin-fckeditor is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * FusionForge-plugin-fckeditor is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge-plugin-fckeditor; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
 
/**
 * The fckeditorPlugin class. It implements the Hooks for the presentation
 *  of the text editor whenever needed
 */

class fckeditorPlugin extends Plugin {
	function fckeditorPlugin () {
		$this->Plugin();
		$this->name = "fckeditor" ;
		$this->text = _("HTML editor");
		$this->_addHook("groupisactivecheckbox");
		$this->_addHook("groupisactivecheckboxpost");
		$this->_addHook('userisactivecheckbox');
		$this->_addHook('userisactivecheckboxpost');
		$this->_addHook("text_editor"); // shows the editor
	}

	/**
	* The function to be called for a Hook
	*
	* @param		String	$hookname  The name of the hookname that has been happened
	* @param		String	$params    The params of the Hook
	*
	*/
	function CallHook($hookname, &$params) {
		global $group_id;

		if (file_exists ("/usr/share/fckeditor/fckeditor.php")) {
			$use_system_fckeditor = true;
			require_once("/usr/share/fckeditor/fckeditor.php");
		} else {
			$use_system_fckeditor = false;
			require_once $GLOBALS['gfplugins'].'fckeditor/www/fckeditor.php';
		}

		if ($hookname == "text_editor") {
			$display = 0;
			if (isset($params['group'])) {
				$group_id=$params['group']; // get the project id
				$project = &group_get_object($group_id);
				if ( (!$project) || (!is_object($project)) || ($project->isError()) || (!$project->isProject()) ) {
					return false;
				}
				if ( $project->usesPlugin ( $this->name ) ) { // only if the plugin is activated for the project show the fckeditor box
					$display = 1;
				}
			} else if (isset($params['user_id'])) {
				$userid = $params['user_id'];
				$user = user_get_object($userid);
				if ($user->usesPlugin($this->name)) {
					$display = 1;
				}
			}
			if ($display) {
				$name = isset($params['name'])? $params['name'] : 'body';
				$oFCKeditor = new FCKeditor($name) ;
				if ($use_system_fckeditor) {
					$oFCKeditor->BasePath = util_make_uri('/fckeditor/');
					$oFCKeditor->Config['CustomConfigurationsPath'] = "/plugins/fckeditor/fckconfig.js"  ;
				} else {
					$oFCKeditor->BasePath = util_make_uri('/plugins/' . $this->name . '/');
				}
				$oFCKeditor->Value = $params['body'];
				if (isset($params['width'])) $oFCKeditor->Width = $params['width'];
				$oFCKeditor->Height = $params['height'];
				$oFCKeditor->ToolbarSet = isset($params['toolbar']) ? $params['toolbar']: 'FusionForge';
				$h = '<input type="hidden" name="_'.$name.'_content_type" value="html" />'."\n";
				$h .= $oFCKeditor->CreateHtml() ;

				// If content is present, return the html code in content.
				if (isset($params['content'])) {
					$params['content'] = $h;
				} else {
					$GLOBALS['editor_was_set_up'] = true;
					echo $h;
				}
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
?>
