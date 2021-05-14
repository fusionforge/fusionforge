<?php

/**
 * extsubprojPlugin Class
 *
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

class extsubprojPlugin extends Plugin {
	function __construct($id=0) {
		parent::__construct($id);
		$this->name = "extsubproj";
		$this->text = _("External SubProjects"); // To show in the tabs, use...
		$this->pkg_desc =
_("This plugin contains the extsubproj subsystem of FusionForge. It allows each
FusionForge project to have its own extsubproj, and gives some
control over it to the project's administrator.");
		/*
		$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		$this->_addHook("usermenu");
		$this->_addHook("groupmenu");	// To put into the project tabs

		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //
		*/
		$this->_addHook("groupisactivecheckbox"); // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost"); //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook('site_admin_option_hook');  // to provide a link to the site wide administrative pages of plugin
		$this->_addHook('widget_instance'); // creates widgets when requested
		$this->_addHook('widgets'); // declares which widgets are provided by the plugin
	}

	function site_admin_option_hook(&$params) {
		// Use this to provide a link to the site wide administrative pages for your plugin
		echo '<li>'.$this->getAdminOptionLink().'</li>';
	}

	function project_admin_plugins(&$params) {
		$group_id = $params['group_id'];
		$group = group_get_object($group_id);
		if ($group->usesPlugin($this->name)) {
			echo '<p>';
			echo $this->getProjectAdminLink($group_id);
			echo '</p>';
		}
	}

	/**
	* getAdminOptionLink - return the admin link url
	*
	* @return	string	html url
	* @access	public
	*/
	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/?type=globaladmin&pluginname='.$this->name,_('External subprojects admin'), array('title'=>_('Configure the External subprojects plugin')));
	}
	/**
	* getProjectAdminLink - return the project admin link url
	*
	* @return	string	html url
	* @access	public
	*/
	function getProjectAdminLink($group_id) {
		return util_make_link('/plugins/'.$this->name.'/?group_id='.$group_id.'&type=admin&pluginname='.$this->name, _('External subprojects admin'), array('title'=>_('Configure the External subprojects plugin')));
	}
	function getProjectAdminAddExtSubProjAction($group_id) {
		return '/plugins/'.$this->name.'/?group_id='.$group_id.'&type=admin&pluginname='.$this->name.'&action=addExtSubProj';
	}
	function getProjectAdminDelExtSubProjAction($group_id, $url) {
		return '/plugins/'.$this->name.'/?group_id='.$group_id.'&type=admin&pluginname='.$this->name.'&action=delExtSubProj&url='.urlencode($url);
	}
	/**
	* getHeader - initialize header and js
	* @param	string	type : user, project (aka group)
	* @param       array   params
	* @return	bool	success or not
	*/
	function getHeader($type, $params=NULL) {
		global $gfplugins;
		$returned = false;
		switch ($type) {
			case 'globaladmin': {
				session_require_global_perm('forge_admin');
				global $gfwww;
				require_once($gfwww.'admin/admin_utils.php');
				site_admin_header(array('title'=>_('Site Global External subprojects Admin'), 'toptab'=>''));
				$returned = true;
				break;
			}
			case 'admin':
			default: {
				site_project_header($params);
				$returned = true;
				break;
			}
		}
		return $returned;
	}

	/**
	* getFooter - display footer
	*/
	function getFooter($type) {
		global $gfplugins;
		$returned = false;
		switch ($type) {
			case 'globaladmin': {
				session_require_global_perm('forge_admin');
				site_admin_footer();
				break;
			}
			case 'admin':
			default: {
				site_project_footer();
				break;
			}
		}
		return $returned;
	}
	/**
	* redirect - encapsulate session_redirect to handle correctly the redirection URL
	*
	* @param	string	usually http_referer from $_SERVER
	* @param	string	type of feedback : error, warning, feedback
	* @param	string	the message of feedback
	* @access	public
	*/
	function redirect($http_referer, $type, $message) {
		switch ($type) {
			case 'warning_msg':
			case 'error_msg':
			case 'feedback': {
				break;
			}
			default: {
				$type = 'error_msg';
			}
		}
		$url = util_find_relative_referer($http_referer);
		if (strpos($url,'?')) {
			session_redirect($url.'&'.$type.'='.urlencode($message));
		}
		session_redirect($url.'?'.$type.'='.urlencode($message));
	}

	/**
	* getGlobalAdminView - display the global configuration view
	*
	* @return	boolean	True
	* @access	public
	*/
	function getGlobalAdminView() {
		global $gfplugins;
		include $gfplugins.$this->name.'/view/admin/viewGlobalConfiguration.php';
		return true;
	}

	/**
	 * getProjectAdminView - display the project admin view
	 * @return boolean
	 */
	function getProjectAdminView() {
		global $gfplugins;
		include $gfplugins.$this->name.'/view/admin/viewProjectConfiguration.php';
		return true;
	}

	function getSubProjects($project_id) {
		$res = db_query_params('SELECT sub_project_url from plugin_extsubproj_subprojects where project_id=$1', array($project_id));
		if (!$res) {
			return false;
		}
		$returnArr = array();
		while ($row = db_fetch_array($res)) {
			$returnArr[] = $row['sub_project_url'];
		}
		return $returnArr;
	}

	function addExtSubProj($project_id, $url) {
		// verify URL
		if(!util_check_url($url)) {
			return false;
		}
		// check if not already in the existing subprojects (even for another project)
		// TODO first check with HTTP then check with HTTPS
		$res = db_query_params('SELECT count(*) from plugin_extsubproj_subprojects where sub_project_url=$1', array($url));
		if ($res && db_result($res, 0, 'count') == '0') {
			$res = db_query_params('INSERT INTO plugin_extsubproj_subprojects (project_id, sub_project_url) VALUES ($1, $2)',
				array($project_id, $url));
			if (!$res) {
				return false;
			}
			return true;
		}
	}

	function delExtSubProj($project_id, $url) {
		// TODO verify URL
		// check if not already in the existing subprojects (even for another project)
		// TODO first check with HTTP then check with HTTPS
		$res = db_query_params('SELECT count(*) from plugin_extsubproj_subprojects where sub_project_url=$1', array($url));
		if ($res && db_result($res, 0, 'count') > '0') {
			$res = db_query_params('DELETE FROM plugin_extsubproj_subprojects WHERE project_id=$1 AND sub_project_url=$2',
				array($project_id, $url));
			if (!$res) {
				return false;
			}
			return true;
		}
	}

	/**
	* widgets - 'widgets' hook handler
	* @param array $params
	* @return boolean
	*/
	function widgets($params) {
		global $gfcommon;
		require_once $gfcommon.'/widget/WidgetLayoutManager.class.php';
		if ($params['owner_type'] == WidgetLayoutManager::OWNER_TYPE_GROUP) {
			$group = group_get_object($GLOBALS['group_id']);
			if ($group->usesPlugin($this->name)) {
				$params['fusionforge_widgets'][] = 'plugin_extsubproj_project_subprojects';
			}
		}
		return true;
	}

	/**
	 * Process the 'widget_instance' hook to create instances of the widgets
	 * @param array $params
	 */
	function widget_instance($params) {
		global $gfplugins, $gfcommon;
		//$user = UserManager::instance()->getCurrentUser();
		require_once $gfcommon.'/widget/WidgetLayoutManager.class.php';
		if ($params['widget'] == 'plugin_extsubproj_project_subprojects') {
			require_once $gfplugins.$this->name.'/include/extsubproj_Widget_SubProjects.class.php';
			$params['instance'] = new extsubproj_Widget_SubProjects(WidgetLayoutManager::OWNER_TYPE_GROUP, $this);
		}
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
