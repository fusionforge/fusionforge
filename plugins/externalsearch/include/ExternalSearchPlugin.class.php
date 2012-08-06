<?php

/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 */

global $gfwww;
require_once $gfwww.'search/include/SearchManager.class.php';
global $gfplugins;
require_once $gfplugins.'externalsearch/include/ExternalSearchEngine.class.php';

forge_define_config_item('engines_file','externalsearch',
			 '$core/source_path/plugins/externalsearch/etc/engines.json'
	) ;

class ExternalSearchPlugin extends Plugin {
	function ExternalSearchPlugin() {
		$this->Plugin();
		$this->name = 'externalsearch';
		$this->text = 'External Search';

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

?>
