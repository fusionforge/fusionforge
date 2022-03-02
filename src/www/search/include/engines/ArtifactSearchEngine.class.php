<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Guillaume Smet
 * Copyright 2022, Franck Villaume - TrivialDev
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

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeHtml.class.php';

class ArtifactSearchEngine extends GroupSearchEngine {
	var $ath;

	function __construct() {
		$this->type = SEARCH__TYPE_IS_ARTIFACT;
		$this->rendererClassName = 'ArtifactHtmlSearchRenderer';
	}

	function getLabel($parameters) {
		return $this->ath->getName();
	}

	function isAvailable($parameters) {
		if(parent::isAvailable($parameters) && isset($parameters[SEARCH__PARAMETER_ARTIFACT_ID]) && $parameters[SEARCH__PARAMETER_ARTIFACT_ID]) {
			$lath = new ArtifactTypeHtml($this->Group, $parameters[SEARCH__PARAMETER_ARTIFACT_ID]);
			if($lath && is_object($lath) && !$lath->isError()) {
				$this->ath =& $lath;
				return true;
			}
		}
		return false;
	}

	function getSearchRenderer($words, $offset, $exact, $parameters) {
		$this->includeSearchRenderer();
		$lrendererClassName = $this->rendererClassName;
		return new $lrendererClassName($words, $offset, $exact, $parameters[SEARCH__PARAMETER_GROUP_ID], $parameters[SEARCH__PARAMETER_ARTIFACT_ID]);
	}
}
