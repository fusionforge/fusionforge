<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: GroupSearchEngine.class.php 6506 2008-05-27 20:56:57Z aljeux $
 */

require_once $gfwww.'search/include/engines/SearchEngine.class.php';

class GroupSearchEngine extends GFSearchEngine {
	var $Group;
	
	function GroupSearchEngine($type, $rendererClassName, $label) {
		$this->GFSearchEngine($type, $rendererClassName, $label);
	}
	
	function isAvailable($parameters) {
		if(isset($parameters[SEARCH__PARAMETER_GROUP_ID]) && $parameters[SEARCH__PARAMETER_GROUP_ID]) {
			$Group =& group_get_object($parameters[SEARCH__PARAMETER_GROUP_ID]);
			if($Group && is_object($Group) && !$Group->isError()) {
				$this->Group =& $Group;
				return true;
			}
		}
		return false;
	}
	
	function & getSearchRenderer($words, $offset, $exact, $parameters) {
		$this->includeSearchRenderer();
		$rendererClassName = $this->rendererClassName;
		$renderer = new $rendererClassName($words, $offset, $exact, $parameters[SEARCH__PARAMETER_GROUP_ID]);
		return $renderer;
	}
}

?>
