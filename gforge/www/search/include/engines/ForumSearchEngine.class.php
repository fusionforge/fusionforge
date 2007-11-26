<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

class ForumSearchEngine extends GroupSearchEngine {
	
	function ForumSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_FORUM, 'ForumHtmlSearchRenderer', _('This forum'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters) && isset($parameters[SEARCH__PARAMETER_FORUM_ID]) && $parameters[SEARCH__PARAMETER_FORUM_ID]) {
			return true;
		}
		return false;
	}
	
	function & getSearchRenderer($words, $offset, $exact, $parameters) {
		$this->includeSearchRenderer();
		$rendererClassName = $this->rendererClassName;
		$renderer = new $rendererClassName($words, $offset, $exact, $parameters[SEARCH__PARAMETER_GROUP_ID], $parameters[SEARCH__PARAMETER_FORUM_ID]);
		return $renderer;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
