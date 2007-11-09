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

require_once('www/search/include/renderers/HtmlGroupSearchRenderer.class.php');
require_once('common/search/ForumSearchQuery.class.php');

class ForumHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
	/**
	 * forum id
	 *
	 * @var int $groupId
	 */
	var $forumId;
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param int $forumId forum id
	 */
	function ForumHtmlSearchRenderer($words, $offset, $isExact, $groupId, $forumId) {
		$this->forumId = $forumId;
		
		$searchQuery = new ForumSearchQuery($words, $offset, $isExact, $groupId, $forumId);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_FORUM, $words, $isExact, $searchQuery, $groupId, 'forums');
		
		$this->tableHeaders = array(
			_('Thread'),
			_('Author'),
			_('Date')
		);
	}
	
	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();
		$dateFormat = _('Y-m-d H:i');
		
		$return = '';
		for($i = 0; $i < $rowsCount; $i++) {
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td width="50%"><a href="'.$GLOBALS['sys_urlprefix'].'/forum/message.php?msg_id='
				. db_result($result, $i, 'msg_id').'">'
				. html_image('ic/msg.png', '10', '12', array('border' => '0'))
				. ' '.db_result($result, $i, 'subject').'</a></td>'
				. '<td width="30%">'.db_result($result, $i, 'realname').'</td>'
				. '<td width="20%">'.date($dateFormat, db_result($result, $i, 'post_date')).'</td></tr>';
		}
		return $return;
	}

	/**
	 * getPreviousResultsUrl - get the url to go to see the previous results
	 *
	 * @return string url to previous results page
	 */
	function getPreviousResultsUrl() {
		return parent::getPreviousResultsUrl().'&amp;forum_id='.$this->forumId;
	}
	
	/**
	 * getNextResultsUrl - get the url to go to see the next results
	 *
	 * @return string url to next results page
	 */
	function getNextResultsUrl() {
		return parent::getNextResultsUrl().'&amp;forum_id='.$this->forumId;
	}

	/**
	 * redirectToResult - redirect the user  directly to the result when there is only one matching result
	 */
	function redirectToResult() {
		header('Location: /forum/message.php?msg_id='.$this->getResultId('msg_id'));
		exit();
	}
}

?>
