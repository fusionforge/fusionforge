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

class DocsGroupSearchEngine extends GroupSearchEngine {
	
	function DocsGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_DOCS, 'DocsHtmlSearchRenderer', _('This project\'s documents'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesDocman()) {
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
