<?php
/**
 * GForge Search Engine
 *
 * Copyright 2005 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 */

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';

class NewsGroupSearchEngine extends GroupSearchEngine {
	
	function NewsGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_NEWS, 'NewsHtmlSearchRenderer', _('This project\'s news'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesNews()) {
				return true;
			}
		}
		return false;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
