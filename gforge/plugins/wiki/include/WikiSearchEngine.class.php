<?php
/**
 * Wiki search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: ExternalSearchEngine.class 3933 2005-02-19 13:04:45Z gsmet $
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

class WikiSearchEngine extends SearchEngine {
	
	/**
	* name of the external site
	*
	* @var string $name
	*/
	var $rendererClassName;
	var $groupId;
	
	function WikiSearchEngine($type, $rendererClassName, $label, $groupId) {
		$this->groupId = $groupId;
		$this->rendererClassName = $rendererClassName;
		
		$this->SearchEngine($type, $rendererClassName, $label);
	}
	
	function isAvailable($parameters) {
		return true;
	}
	
	function & getSearchRenderer($words, $offset, $exact) {
		require_once($this->rendererClassName.'.class.php');
		$renderer = new $this->rendererClassName($words, $offset, $exact, 
			$this->groupId);
		return $renderer;
	}
}

?>
