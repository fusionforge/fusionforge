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

forge_define_config_item('engines','externalsearch',
			 '{"Google":"http:\/\/www.google.com\/search?as_sitesearch=%web_host%&as_q=","AllTheWeb":"http:\/\/alltheweb.com\/search?advanced=1&dincl=%web_host%&q="}'
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
				$externalSearchEngines = json_decode (forge_get_config ('engines', 'externalsearch')) ;
				foreach($externalSearchEngines AS $name => $url) {
					$type = SEARCH__TYPE_IS_EXTERNAL.'_'.$name;
					$parsedurl = preg_replace ('%web_host%',
								   forge_get_config ('web_host',
										     $url)) ;
					$searchManager->addSearchEngine(
						$type,
						new ExternalSearchEngine($type, $name, $parsedurl)
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
