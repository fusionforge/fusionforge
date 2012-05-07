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
		$this->name = "headermenu" ;
		$this->text = "headermenu" ;
		$this->_addHook('headermenu');
		$this->_addHook('site_admin_option_hook');
	}

	function CallHook ($hookname, &$params) {
		switch ($hookname) {
			case "headermenu": {
				$this->getHeaderLink();
				break;
			}
			case "site_admin_option_hook": {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
				$returned = true;
				break;
			}
		}
	}

	function getAdminOptionLink() {
		return util_make_link('/plugins/'.$this->name.'/?type=globaladmin', _('Global HeaderMenu admin'), array('class' => 'tabtitle', 'title' => _('Direct link to global configuration of this plugin')));
	}

	function getHeaderLink() {
		$availableLinks = $this->getAvailableLinks();
		foreach ($availableLinks as $link) {
			$ahref = '<a href="'.$link['url'].'">'.$link['name'].'</a>';

			$template = isset($params['template']) ?  $params['template'] : ' | {menu}';
			echo str_replace('{menu}', $ahref, $template);
		}
		return true;
	}

	function getAvailableLinks() {
		$links = db_query_params('SELECT * FROM plugin_headermenu', array());
		$availableLinks = array();
		while ($arr = db_fetch_array($links)) {
			$availableLinks[] = $arr;
		}
		return $availableLinks;
	}

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
	 * getHeader - initialize header and js
	 * @param	string	type : user, project (aka group)
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

	function getPluginDescription() {
		return _('Get the ability to set new links next to the login menu.');
	}
}
?>
