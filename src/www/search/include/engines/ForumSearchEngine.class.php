<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
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

class ForumSearchEngine extends GroupSearchEngine {
	
	function ForumSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_FORUM, 'ForumHtmlSearchRenderer', _('This forum'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters) && isset($parameters[SEARCH__PARAMETER_FORUM_ID]) && $parameters[SEARCH__PARAMETER_FORUM_ID]) {
			return true;
		}
		return false;
	}
	
	function getSearchRenderer($words, $offset, $exact, $parameters) {
		$this->includeSearchRenderer();
		$rendererClassName = $this->rendererClassName;
		$renderer = new $rendererClassName($words, $offset, $exact, $parameters[SEARCH__PARAMETER_GROUP_ID], $parameters[SEARCH__PARAMETER_FORUM_ID]);
		return $renderer;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
