<?php
/**
 * Wiki Search Engine for GForge
 *
 * Copyright 2006 (c) Alain Peyrat
 *
 * @version $Id: GroupSearchEngine.class,v 1.2 2004/12/12 23:34:46 gsmet Exp $
 */

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';

class ForumMLGroupSearchEngine extends GroupSearchEngine {

	function ForumMLGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_LIST,
					 'ForumMLHtmlSearchRenderer',
					 _('ForumML'));
	}

	function isAvailable($parameters) {
		if (parent::isAvailable($parameters)) {
			if ($this->Group->usesPlugin('forumml')) {
				return true;
			}
		}
		return false;
	}
}

?>
