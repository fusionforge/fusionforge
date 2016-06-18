<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
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

global $gfwww;
require_once $gfwww.'search/include/SearchManager.class.php';
global $gfplugins;
require_once $gfplugins.'externalsearch/common/ExternalSearchEngine.class.php';

forge_define_config_item('engines_file','externalsearch',
			 '$core/source_path/plugins/externalsearch/etc/engines.json'
	) ;

class ExternalSearchPlugin extends Plugin {
	function __construct() {
		parent::__construct();
		$this->name = 'externalsearch';
		$this->text = _('External Search');
		$this->pkg_desc =
_("This plugin adds a new search engine to your FusionForge site. It allows
your users to search your FusionForge site through external search engines
which have indexed it. You can define search engines you want to use in
the configuration file.");
		$this->hooks[] = 'search_engines';
	}

	function CallHook($hookname, & $searchManager) {
		global $gfconfig;
		switch($hookname) {
			case 'search_engines':
				$externalSearchEngines = json_decode (file_get_contents(forge_get_config ('engines', 'externalsearch'))) ;
				foreach($externalSearchEngines AS $name => $url) {
					$type = SEARCH__TYPE_IS_EXTERNAL.'_'.$name;
					$searchManager->addSearchEngine(
						$type,
						new ExternalSearchEngine($type, $name, str_replace('%web_host%',forge_get_config('web_host'),$url)
							));
				}
				break;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
