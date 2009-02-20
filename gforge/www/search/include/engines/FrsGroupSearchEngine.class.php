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

class FrsGroupSearchEngine extends GroupSearchEngine {
	
	function FrsGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_FRS, 'FrsHtmlSearchRenderer', _('This project\'s releases'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesFRS()) {
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
