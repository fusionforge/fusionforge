<?php
/**
 * ForumML Search Engine for FusionForge
 *
 * Copyright 2006 (c) Alain Peyrat
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

class ForumMLGroupSearchEngine extends GroupSearchEngine {

	function __construct() {
		parent::__construct(SEARCH__TYPE_IS_LIST,
					 'ForumMLHtmlSearchRenderer',
					 _('ForumML'));
	}

	function isAvailable($parameters) {
		if (parent::isAvailable($parameters)) {
			if ($this->Group->usesPlugin('forumml')) {
				return true;
			}
		}
		return false;
	}
}
