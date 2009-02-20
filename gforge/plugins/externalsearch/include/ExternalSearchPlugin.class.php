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
				require_once $gfconfig.'plugins/externalsearch/config.php';
				foreach($externalSearchEngines AS $name => $url) {
					$type = SEARCH__TYPE_IS_EXTERNAL.'_'.$name;
					$searchManager->addSearchEngine(
						$type,
						new ExternalSearchEngine($type, $name, $url)
					);
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
