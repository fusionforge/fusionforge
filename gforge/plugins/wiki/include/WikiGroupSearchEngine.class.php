<?php
/**
 * Wiki Search Engine for GForge
 *
 * Copyright 2006 (c) Alain Peyrat
 *
 * @version $Id: GroupSearchEngine.class,v 1.2 2004/12/12 23:34:46 gsmet Exp $
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

class WikiGroupSearchEngine extends GroupSearchEngine {
	
	function WikiGroupSearchEngine() {
		global $Language;
		$this->GroupSearchEngine(SEARCH__TYPE_IS_WIKI, 'WikiHtmlSearchRenderer', 
			$Language->getText('plugin_wiki','wiki'));
	}
	
	function isAvailable($parameters) {
		if (parent::isAvailable($parameters)) {
			if ($this->Group->usesPlugin('wiki')) {
				return true;
			}
		}
		return false;
	}
}

?>
