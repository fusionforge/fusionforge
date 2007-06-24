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

class SearchEngine {
	var $type;
	var $rendererClassName;
	var $label;
	
	function SearchEngine($type, $rendererClassName, $label) {
		$this->type = $type;
		$this->rendererClassName = $rendererClassName;
		$this->label = $label;
	}
	
	function getType() {
		return $this->type;
	}
	
	function getLabel($parameters) {
		return $this->label;
	}
	
	function isAvailable($parameters) {
		return true;
	}
	
	function includeSearchRenderer() {
		require_once('www/search/include/renderers/'.$this->rendererClassName.'.class.php');
	}
	
	function & getSearchRenderer($words, $offset, $exact) {
		$this->includeSearchRenderer();
		$rendererClassName = $this->rendererClassName;
		$renderer = new $rendererClassName($words, $offset, $exact);
		return $renderer;
	}
}

?>
