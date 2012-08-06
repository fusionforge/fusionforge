<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Guillaume Smet
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class GFSearchEngine {
	var $type;
	var $rendererClassName;
	var $label;

	function GFSearchEngine($type, $rendererClassName, $label) {
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
		global $gfwww, $gfcommon;
		require_once $gfwww.'search/include/renderers/'.$this->rendererClassName.'.class.php';
	}

	function getSearchRenderer($words, $offset, $exact, $parameters) {
		$this->includeSearchRenderer();
		$rendererClassName = $this->rendererClassName;
		$renderer = new $rendererClassName($words, $offset, $exact);
		return $renderer;
	}
}

?>
