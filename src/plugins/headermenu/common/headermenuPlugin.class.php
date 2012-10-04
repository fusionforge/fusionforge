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

	var $pageid;

	function __construct() {
		$this->Plugin() ;
		$this->name = 'headermenu' ;
		$this->text = 'headermenu' ;
		$this->_addHook('headermenu');
		$this->_addHook('site_admin_option_hook');
		$this->_addHook('outermenu');
	}

	function CallHook ($hookname, &$params) {
		switch ($hookname) {
			case 'headermenu': {
				$this->getHeaderLink();
				break;
			}
			case 'outermenu': {
				$this->getOuterLink($params);
				break;
			}
			case 'site_admin_option_hook': {
				echo '<li>'.$this->getAdminOptionLink().'</li>';
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
		$availableLinks = $this->getAvailableLinks('headermenu');
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
	 * getOuterLink - update the links before generate the tab.
	 *
	 * @param 	array	hook params array
	 * @return	bool	true...
	 */
	function getOuterLink($params) {
		$availableLinks = $this->getAvailableLinks('outermenu');
		foreach ($availableLinks as $link) {
			if ($link['is_enable']) {
				switch ($link['linktype']) {
					case 'url': {
						$params['DIRS'][] = $link['url'];
						$params['TITLES'][] = $link['name'];
						$params['TOOLTIPS'][] = $link['description'];
						break;
					}
					case 'htmlcode': {
						$params['DIRS'][] = '/plugins/'.$this->name.'/?type=pageview&pageid='.$link['id_headermenu'];
						$params['TITLES'][] = $link['name'];
						$params['TOOLTIPS'][] = $link['description'];
						break;
					}
				}
			}
		}
		return true;
	}

	/**
	 * getAvailableLinks - get all the links from the db of certain kind
	 *
	 * @param	string	the type of menu links search in db
	 * @return	array	the available links
	 */
	function getAvailableLinks($linkmenu) {
		$links = db_query_params('select * FROM plugin_headermenu where linkmenu = $1', array($linkmenu));
		$availableLinks = array();
		while ($arr = db_fetch_array($links)) {
			$availableLinks[] = $arr;
		}
		return $availableLinks;
	}

	/**
	 * getAllAvailableLinks - get all the links from the db
	 *
	 * @return	array	the available links
	 */
	function getAllAvailableLinks() {
		$availableOuterLinks = $this->getAvailableLinks('outermenu');
		$availableHeaderLinks = $this->getAvailableLinks('headermenu');
		return array_merge($availableOuterLinks, $availableHeaderLinks);
	}

	/**
	 * addLink - add a new valid link
	 *
	 * @param	string	$url the url
	 * @param	string	$name the displayed name
	 * @param	string	$description a short description (to help administration)
	 * @param	string	$linkmenu linkmenu entry : headermenu or outermenu
	 * @param	string	$linktype
	 * @param	string	$htmlcode
	 * @return	bool	success or not
	 */
	function addLink($url, $name, $description, $linkmenu, $linktype = 'url', $htmlcode = '') {
		$res = db_query_params('insert into plugin_headermenu (url, name, description, is_enable, linkmenu, linktype, htmlcode)
					values ($1, $2, $3, $4, $5, $6, $7)',
					array(
						$url,
						$name,
						$description,
						1,
						$linkmenu,
						$linktype,
						$htmlcode
					));
		if (!$res)
			return false;

		return true;
	}

	/**
	 * deleteLink - delete a link
	 *
	 * @param	int	$idLink the link id
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

	/**
	 * updateLink - update a valid link
	 *
	 * @param	int	$idLink the link id to be updated
	 * @param	string	$url the url
	 * @param	string	$name the displayed name
	 * @param	string	$description a short description (to help administration)
	 * @param	string	$linkmenu linkmenu entry : headermenu or outermenu
	 * @param	string	$linktype : url or htmlcode, default is url
	 * @param	string	$htmlcode
	 * @return	bool	success or not
	 */
	function updateLink($idLink, $url, $name, $description, $linkmenu, $linktype = 'url', $htmlcode ='') {
		$res = db_query_params('update plugin_headermenu set url = $1, name = $2, description = $3, linkmenu = $4, linktype = $5, htmlcode = $6
					where id_headermenu = $7',
				array($url, $name, $description, $linkmenu, $linktype, $htmlcode, $idLink));
		if ($res) {
			return true;
		}
		return false;
	}

	/**
	 * pageView - display a static html code
	 *
	 * @param	int	the page id
	 * @return	string	the html code
	 */
	function pageView($pageid) {
		$link = $this->getLink($pageid);
		return $link['htmlcode'];
	}

	/**
	 * getHeader - initialize header and js
	 *
	 * @param	string	type : user, project, globaladmin (aka group)
	 * @return	bool	success or not
	 */
	function getHeader($type) {
		$returned = false;
		switch ($type) {
			case 'globaladmin': {
				session_require_global_perm('forge_admin');
				global $gfwww;
				require_once($gfwww.'admin/admin_utils.php');
				html_use_jquery();
				use_javascript('scripts/HeaderMenuController.js');
				use_javascript('/js/sortable.js');
				site_admin_header(array('title'=>_('Site Global Menu Admin'), 'toptab' => ''));
				$returned = true;
				break;
			}
			case 'pageview': {
				$link = $this->getLink($this->pageid);
				site_header(array('title'=> $link['name'], 'toptab' => '/plugins/headermenu/?pageview&pageid='.$this->pageid));
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
		return _('Get the ability to set new links next to the login menu (headermenu) or in the main menu (outermenu).');
	}
}
