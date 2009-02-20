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

class ForumsGroupSearchEngine extends GroupSearchEngine {
	
	function ForumsGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_FORUMS, 'ForumsHtmlSearchRenderer', _('This project\'s forums'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesForum()) {
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
