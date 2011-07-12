<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 */

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';

class ExternalSearchEngine extends GroupSearchEngine {

	/**
	* name of the external site
	*
	* @var string $name
	*/
	var $name;

	/**
	* url of the external site
	*
	* @var string $url
	*/
	var $url;

	function ExternalSearchEngine($type, $name, $url) {
		$this->name = $name;
		$this->url = $url;

		$this->GroupSearchEngine($type, 'ExternalHtmlSearchRenderer', $name);
	}

	function isAvailable($parameters) {
		return true;
	}

	function getSearchRenderer($words, $offset, $exact, $parameters) {
		require_once $gfplugins.'externalsearch/include/ExternalHtmlSearchRenderer.class.php';
		$renderer = new ExternalHtmlSearchRenderer($type, $this->name, $this->url, $words);
		return $renderer;
	}
}

?>
