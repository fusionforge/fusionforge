<?php
/**
 * WikiPlugin Class
 * Wiki Search Engine for Fusionforge
 *
 * Copyright 2006 (c) Alain Peyrat
 *
 * This file is part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';

class WikiGroupSearchEngine extends GroupSearchEngine {
	
	function WikiGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_WIKI,
					 'WikiHtmlSearchRenderer', 
					 _('Wiki'));
	}
	
	function isAvailable($parameters) {
		if (parent::isAvailable($parameters)) {
			if ($this->Group->usesPlugin('wiki')) {
				return true;
			}
		}
		return false;
	}
}

?>
