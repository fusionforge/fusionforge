<?php

/**
 * GForge Search Engine
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2004 (c) Guillaume Smet / Open Wide
 *
 * http://gforge.org
 *
 * @version $Id$
 */
 
require_once('www/search/include/renderers/HtmlSearchRenderer.class.php');

class HtmlGroupSearchRenderer extends HtmlSearchRenderer {
	
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
	function isGroupMember($groupId) {
		$Group =& group_get_object($groupId);
		if($Group && is_object($Group) && !$Group->isError() && session_loggedin()) {
			$perm =& $Group->getPermission(session_get_user());
			if($perm && is_object($perm) && $perm->isMember()) {
				return true;
			}
		}
		return false;		
	}
}
?>
