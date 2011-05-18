<?php
/**
 * Search Engine
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet / Open Wide
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

require_once $gfwww.'search/include/renderers/HtmlSearchRenderer.class.php';

class HtmlGroupSearchRenderer extends HtmlSearchRenderer {
	
	/** TODO: Find what for is $offset, looks like it's not used, added to remove warning 
	*/
	var $offset;
	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;
	
	/**
	 * selected top tab
	 * @var string $topTab
	 */
	var $topTab;

	/**
	 * Constructor
	 *
	 * @param string $typeOfSearch type of the search (Software, Forum, People and so on)
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param object $searchQuery SearchQuery instance
	 * @param int $groupId group id
	 */
	function HtmlGroupSearchRenderer($typeOfSearch, $words, $isExact, $searchQuery, $groupId, $topTab = '') {
		$this->HtmlSearchRenderer($typeOfSearch, $words, $isExact, $searchQuery);
		$this->groupId = $groupId;
		$this->topTab = $topTab;
	}
	
	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		site_project_header(array('title' => _('Search'), 'group' => $this->groupId, 'toptab' => $this->topTab));
		parent::writeHeader();
	}
	
	/**
	 * getPreviousResultsUrl - get the url to go to see the previous results
	 *
	 * @return string url to previous results page
	 */
	function getPreviousResultsUrl() {
		return parent::getPreviousResultsUrl().'&amp;group_id='.$this->groupId;
	}
	
	/**
	 * getNextResultsUrl - get the url to go to see the next results
	 *
	 * @return string url to next results page
	 */
	function getNextResultsUrl() {
		return parent::getNextResultsUrl().'&amp;group_id='.$this->groupId;
	}

	/**
	 * isGroupMember - returns if the logged in user is member of the current group
	 *
	 * @param int $groupId group id
	 */
	static function isGroupMember($groupId) {
		$Group = group_get_object($groupId);
		if($Group && is_object($Group) && !$Group->isError() && session_loggedin()) {
			$perm =& $Group->getPermission ();
			if($perm && is_object($perm) && $perm->isMember()) {
				return true;
			}
		}
		return false;		
	}
}
?>
