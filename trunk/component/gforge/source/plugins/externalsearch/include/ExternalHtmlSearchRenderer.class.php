<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: ExternalHtmlSearchRenderer.class.php 6506 2008-05-27 20:56:57Z aljeux $
 */

require_once $gfwww.'search/include/renderers/SearchRenderer.class.php';

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
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 */
	function ExternalHtmlSearchRenderer($type, $name, $url, $words) {
		$this->name = $name;
		$this->url = $url;
		$this->SearchRenderer($type, $words, true, false);
	}

	function flush() {
		header('Location: '.$this->url.urlencode($this->query['words']));
		exit();
	}
}

?>
