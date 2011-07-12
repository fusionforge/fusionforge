<?php
/**
 * Copyright (c) STMicroelectronics, 2004-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */
require_once('preplugins.php');


class ForumMLPlugin extends Plugin {

	function __construct($id=0) {
		parent::__construct($id);
		$this->name = "forumml" ;
		$this->text = "ForumML" ; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user�s personal part of the plugin
		$this->_addHook("usermenu") ;
		$this->_addHook("groupisactivecheckbox") ; // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost") ; //
		$this->_addHook("userisactivecheckbox") ; // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost") ; //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook('browse_archives','forumml_browse_archives',false);
		$this->_addHook('cssfile','cssFile',false);
		$this->_addHook('javascript_file',                   'jsFile',                            false);
		$this->_addHook('search_type', 'search_type', false);
		$this->_addHook('layout_searchbox_options', 'forumml_searchbox_option', false);
		$this->_addHook('layout_searchbox_hiddenInputs', 'forumml_searchbox_hiddenInput', false);
		$this->_addHook('plugins_powered_search', 'forumml_search', false);
		$this->_addHook('cssfile');
		$this->_addHook('search_engines');
		$this->_addHook('full_search_engines');
		// Set ForumML plugin scope to 'Projects' wide
		//$this->setScope(Plugin::SCOPE_PROJECT);
		$this->allowedForProject = array();
	}

	function CallHook ($hookname, &$params) {
		global $use_mailmanplugin,$G_SESSION,$HTML,$gfcommon,$gfwww,$gfplugins;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("mailman")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we�re calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						array ('/plugins/mailman/index.php' . $param ));
			}
		} elseif ($hookname =='cssfile') {
			echo '<link rel="stylesheet" type="text/css" href="/plugins/forumml/themes/default/css/style.css" />';
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="CHECKBOX" name="use_forummlplugin" value="1" ';
			// CHECKED OR UNCHECKED?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "CHECKED";
			}
			echo "><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";

		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_mailmanplugin = getStringFromRequest('use_forummlplugin');
			if ( $use_mailmanplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == 'search_engines') {
			require_once('ForumMLSearchEngine.class.php');
			// FIXME: when the hook is called, the group_id is not set.
			// So I use the global variable instead.
			$request =& HTTPRequest::instance();
			$group_id = (int) $request->get('group_id');
			if ($group_id) {
				$group =& group_get_object($group_id);
				if ($group->usesPlugin('forumml')) {
					if (isset($params['object'])) {
						$searchManager = $params['object'];
					} else {
						$searchManager = $params;
					}
					$searchManager->addSearchEngine(
							SEARCH__TYPE_IS_LIST,
							new ForumMLSearchEngine(SEARCH__TYPE_IS_LIST,
								'ForumMLHtmlSearchRenderer',
								_("This project's mailing lists"), $group_id)
							);
				}
			}
		}  elseif ($hookname == "browse_archives") {
			$this->forumml_browse_archives($params);
		} elseif ($hookname == "cssfile") {
			$this->cssFile($params);
		} elseif ($hookname == "javascript_file") {
			$this->jsFile($params);
		} elseif ($hookname == "search_type") {
			$this->search_type($params);
		} elseif ($hookname == "layout_searchbox_options") {
			$this->forumml_searchbox_option($params);
		} elseif ($hookname == "layout_searchbox_hiddenInputs") {
			$this->forumml_searchbox_hiddenInput($params);
		} elseif ($hookname == "plugins_powered_search") {
			$this->forumml_search($params);

		}
	}

	function &getPluginInfo() {
		if (!is_a($this->pluginInfo, 'ForumMLPluginInfo')) {
			require_once('ForumMLPluginInfo.class.php');
			$this->pluginInfo =& new ForumMLPluginInfo($this);
		}
		return $this->pluginInfo;
	}

	/**
	 * Return true if current project has the right to use this plugin.
	 */
	function isAllowed($group_id=false) {
		if(!$group_id) {
			$request =& HTTPRequest::instance();
			$group_id = (int) $request->get('group_id');
		}
		if(!isset($this->allowedForProject[$group_id])) {
			$pM =& PluginManager::instance();
			$this->allowedForProject[$group_id] = $pM->isPluginAllowedForProject($this, $group_id);
		}
		return $this->allowedForProject[$group_id];
	}

	function forumml_searchbox_option($params) {
		$request =& HTTPRequest::instance();
		$group_id = (int) $request->get('group_id');
		if(isset($_REQUEST['list']) && isset($group_id)) {
			$params['option_html'] .= "\t<OPTION value=\"mail\"".( $params['type_of_search'] == "mail" ? " SELECTED" : "" ).">"._('This List')."</OPTION>\n";
		}
	}

	function forumml_searchbox_hiddenInput($params) {
		if(isset($_REQUEST['list'])) {
			$params['input_html'] .= "\t<INPUT TYPE=\"HIDDEN\" VALUE=\"". $_REQUEST['list'] ."\" NAME=\"list\">\n";
		}
	}

	function forumml_browse_archives($params) {
		if ($this->isAllowed()) {
			$request =& HTTPRequest::instance();
			$group_id = (int) $request->get('group_id');
			$params['html'] = '<A href="/plugins/forumml/message.php?group_id='.$group_id.'&list='.$params['group_list_id'].'"> '._('Archives').'</A>';
		}
	}

	function cssFile($params) {
		$request =& HTTPRequest::instance();
		if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0) {
			echo '<link rel="stylesheet" type="text/css" href="'.$this->getThemePath().'/css/style.css" />'."\n";
		}
	}

	function jsFile($params) {
		//$request =& HTTPRequest::instance();
		if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0) {
			//echo '<link rel="stylesheet" type="text/css" href="'.$this->getThemePath().'/css/style.css" />'."\n";
			echo '<script type="text/javascript" src="'.$this->getPluginPath().'/scripts/forumml.js"></script>'."\n";
		}
	}

	function forumml_search($params) {
		if($params['type_of_search'] == 'mail') {
			$params['plugins_powered_search'] = true;
		}
	}

	function search_type($params) {
		if(isset($params['type_of_search']) && $params['type_of_search'] == 'mail') {
			$request =& HTTPRequest::instance();
			$group_id = (int) $request->get('group_id');
			$list = (int) $request->get('list');
			util_return_to('/plugins/forumml/message.php?group_id='.$group_id.'&list='.$list.'&search='.urlencode($params['words']));
		}
	}

}

?>
