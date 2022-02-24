<?php
/**
 * Search Engine
 *
 * Copyright 2004, Guillaume Smet
 * Copyright 2022, Franck Villaume - TrivialDev
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

require_once $gfwww.'search/include/engines/SearchEngine.class.php';

class GroupSearchEngine extends GFSearchEngine {
	var $Group;

	function __construct($type, $rendererClassName, $label) {
		parent::__construct($type, $rendererClassName, $label);
	}

	function isAvailable($parameters) {
		if(isset($parameters[SEARCH__PARAMETER_GROUP_ID]) && $parameters[SEARCH__PARAMETER_GROUP_ID]) {
			$lgroup = group_get_object($parameters[SEARCH__PARAMETER_GROUP_ID]);
			if($lgroup && is_object($lgroup) && !$lgroup->isError()) {
				$this->Group =& $lgroup;
				return true;
			}
		}
		return false;
	}

	function getSearchRenderer($words, $offset, $exact, $parameters) {
		$this->includeSearchRenderer();
		$lrendererClassName = $this->rendererClassName;
		$renderer = new $lrendererClassName($words, $offset, $exact, $parameters[SEARCH__PARAMETER_GROUP_ID]);
		return $renderer;
	}
}
