<?php

/**
 * headermenuPlugin Class
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

class headermenuPlugin extends Plugin {

	function __construct() {
		$this->Plugin() ;
		$this->name = 'headermenu' ;
		$this->text = 'headermenu' ;
		$this->_addHook('headermenu');
		$this->_addHook('site_admin_option_hook');
	}

	function CallHook ($hookname, &$params) {
		switch ($hookname) {
			case 'headermenu': {
				$this->getHeaderLink();
				break;
			}
			case 'site_admin_option_hook': {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
				$returned = true;
				break;
			}
		}
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/?type=globaladmin', _('Global HeaderMenu admin'), array('class' => 'tabtitle', 'title' => _('Direct link to global configuration of this plugin')));
	}

	/**
	 * getHeaderLink - generate the links following the template
	 *
	 * @return	bool	true...
	 */
	function getHeaderLink() {
		$availableLinks = $this->getAvailableLinks();
		foreach ($availableLinks as $link) {
			if ($link['is_enable']) {
				$ahref = '<a href="'.$link['url'].'">'.htmlspecialchars($link['name']).'</a>';
				$template = isset($params['template']) ?  $params['template'] : ' | {menu}';
				echo str_replace('{menu}', $ahref, $template);
			}
		}
		return true;
	}

	/**
	 * getAvailableLinks - get all the links from the db
	 *
	 * @return	array	the available links
	 */
	function getAvailableLinks() {
		$links = db_query_params('select * FROM plugin_headermenu', array());
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
	 * @param	string	the displayed name
	 * @param	string	a short description (to help administration)
	 * @return	bool	success or not
	 */
	function addLink($url, $name, $description) {
		if (!empty($url)) {
			$res = db_query_params('insert into plugin_headermenu (url, name, description, is_enable)
					values ($1, $2, $3, $4)',
					array(
						$url,
						$name,
						$description,
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
		$res = db_query_params('delete from plugin_headermenu where id_headermenu = $1', array($idLink));
		if ($res) {
			return true;
		}
		return false;
	}

	function updateLinkStatus($idLink, $linkStatus) {
		$res = db_query_params('update plugin_headermenu set is_enable = $1 where id_headermenu = $2', array($linkStatus, $idLink));
		if ($res) {
			return true;
		}
		return false;
	}

	function getLink($idLink) {
		$res = db_query_params('select * from plugin_headermenu where id_headermenu = $1', array($idLink));
		if (db_numrows($res) == 1) {
			return db_fetch_array($res);
		}
		return false;
	}

	function updateLink($idLink, $url, $name, $description) {
		$res = db_query_params('update plugin_headermenu set url = $1, name = $2, description = $3 where id_headermenu = $4',
				array($url, $name, $description, $idLink));
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
				site_admin_header(array('title'=>_('Site Global headerMenu Admin'), 'toptab' => ''));
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
		return _('Get the ability to set new links next to the login menu.');
	}
}
?>