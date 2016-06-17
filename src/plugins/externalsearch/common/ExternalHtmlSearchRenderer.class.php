<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 */

require_once $gfwww.'search/common/renderers/SearchRenderer.class.php';

class ExternalHtmlSearchRenderer extends SearchRenderer {

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

	/**
	 * @param string $words words we are searching for
	 */
	function __construct($type, $name, $url, $words) {
		$this->name = $name;
		$this->url = $url;
		parent::__construct($type, $words, true, false);
	}

	function flush() {
		header('Location: '.$this->url.urlencode($this->query['words']));
		exit();
	}
}
