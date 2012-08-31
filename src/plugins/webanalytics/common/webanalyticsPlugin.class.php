<?php

/**
 * webanalyticsPlugin Class
 *
 * Copyright 2012 Franck Villaume - TrivialDev
 * http://fusionforge.org
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

class webanalyticsPlugin extends Plugin {

	function __construct() {
		$this->Plugin() ;
		$this->name = "webanalytics" ;
		$this->text = "webanalytics" ;
		$this->_addHook('webanalytics_url');
		$this->_addHook('webanalytics_admin');
		$this->_addHook('site_admin_option_hook');
	}

	function CallHook ($hookname, &$params) {
		switch ($hookname) {
			case "webanalytics_url": {
				echo $this->getWebAnalyticsCodes();
				$returned = true;
				break;
			}
			case "webanalytics_admin":
			case "site_admin_option_hook": {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
				$returned = true;
				break;
			}
		}
		return $returned;
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/?type=globaladmin', _('Global WebAnalytics admin'), array('class' => 'tabtitle', 'title' => _('Direct link to global configuration of this plugin.')));
	}

	function getWebAnalyticsCodes() {
		$codesFromDb = db_query_params('select code FROM plugin_webanalytics where is_enable = $1', array(1));
		$codesString = '';
		while ($arr = db_fetch_array($codesFromDb)) {
			$codesString .= $arr['code'];
		}
		return $codesString;
	}

	/**
	 * getAvailableLinks - get all the links from the db
	 *
	 * @return	array	the available links
	 */
	function getAvailableLinks() {
		$links = db_query_params('select * FROM plugin_webanalytics', array());
		$availableLinks = array();
		while ($arr = db_fetch_array($links)) {
			$availableLinks[] = $arr;
		}
		return $availableLinks;
	}

	/**
	 * addLink - add a new valid link
	 *
	 * @param	string	the url
	 * @param	string	an informative name
	 * @return	bool	success or not
	 */
	function addLink($url, $name) {
		if (!empty($url)) {
			$res = db_query_params('insert into plugin_webanalytics (url, name, code, is_enable)
					values ($1, $2, $3, $4)',
					array(
						htmlspecialchars($url),
						$name,
						$url,
						1,
					));
			if (!$res)
				return false;

			return true;
		}
		return false;
	}

	/**
	 * deleteLink - delete a link
	 *
	 * @param	int	the link id
	 * @return	bool	success or not
	 */
	function deleteLink($idLink) {
		$res = db_query_params('delete from plugin_webanalytics where id_webanalytics = $1', array($idLink));
		if ($res) {
			return true;
		}
		return false;
	}

	function updateLinkStatus($idLink, $linkStatus) {
		$res = db_query_params('update plugin_webanalytics set is_enable = $1 where id_webanalytics = $2', array($linkStatus, $idLink));
		if ($res) {
			return true;
		}
		return false;
	}

	function getLink($idLink) {
		$res = db_query_params('select * from plugin_webanalytics where id_webanalytics = $1', array($idLink));
		if (db_numrows($res) == 1) {
			return db_fetch_array($res);
		}
		return false;
	}

	function updateLink($idLink, $url, $name) {
		$res = db_query_params('update plugin_webanalytics set url = $1, name = $2, code = $3 where id_webanalytics = $4',
				array(htmlspecialchars($url), $name, $url, $idLink));
		if ($res) {
			return true;
		}
		return false;
	}

	/**
	 * getHeader - initialize header and js
	 *
	 * @param	string	type : user, project, globaladmin (aka group)
	 * @return	bool	success or not
	 */
	function getHeader($type) {
		global $gfplugins;
		$returned = false;
		switch ($type) {
			case 'globaladmin': {
				session_require_global_perm('forge_admin');
				global $gfwww;
				require_once($gfwww.'admin/admin_utils.php');
				use_javascript('/js/sortable.js');
				site_admin_header(array('title'=>_('Site Global Webanalytics Admin'), 'toptab' => ''));
				$returned = true;
				break;
			}
		}
		return $returned;
	}

	/**
	 * getGlobalAdminView - display the Global Admin View
	 *
	 * @return	bool	true
	 */
	function getGlobalAdminView() {
		global $gfplugins;
		$user = session_get_user();
		include $gfplugins.$this->name.'/view/admin/viewGlobalConfiguration.php';
		return true;
	}

	/**
	 * getPluginDescription - display the description of this plugin in pluginman admin page
	 *
	 * @return	string	the description
	 */
	function getPluginDescription() {
		return _('Get the ability to configure specific URL for web analytics tool such as Piwik or Google Analytics.');
	}
}
?>
