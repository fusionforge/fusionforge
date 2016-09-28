<?php
/**
 * External search plugin
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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

	function __construct($type, $name, $url) {
		$this->name = $name;
		$this->url = $url;

		parent::__construct($type, 'ExternalHtmlSearchRenderer', $name);
	}

	function isAvailable($parameters) {
		return true;
	}

	function getSearchRenderer($words, $offset, $exact, $parameters) {
		require_once $gfplugins.'externalsearch/common/ExternalHtmlSearchRenderer.class.php';
		$renderer = new ExternalHtmlSearchRenderer($type, $this->name, $this->url, $words);
		return $renderer;
	}
}
