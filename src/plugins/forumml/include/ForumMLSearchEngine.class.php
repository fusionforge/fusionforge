<?php
/**
 * Wiki search plugin
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

require_once $GLOBALS['gfwww'].'search/include/engines/GroupSearchEngine.class.php';

class ForumMLSearchEngine extends GroupSearchEngine {

	/**
	* name of the external site
	*
	* @var string $name
	*/
	var $rendererClassName;
	var $groupId;

	function __construct($type, $rendererClassName, $label, $groupId) {
		$this->groupId = $groupId;
		$this->rendererClassName = $rendererClassName;

		parent::__construct($type, $rendererClassName, $label);
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
