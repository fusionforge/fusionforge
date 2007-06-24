<?php
/**
 * GForge Search Engine
 *
 * Copyright 2005 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: GroupSearchEngine.class.php,v 1.2 2004/12/12 23:34:46 gsmet Exp $
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

class DocsGroupSearchEngine extends GroupSearchEngine {
	
	function DocsGroupSearchEngine() {
		global $Language;
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

?>
