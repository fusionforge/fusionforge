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
require_once('common/search/ArtifactSearchQuery.class.php');

class ArtifactHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
	/**
	 * artifact id
	 *
	 * @var int $artifactId
	 */
	var $artifactId;
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param int $artifactId artifact id
	 */
	function ArtifactHtmlSearchRenderer($words, $offset, $isExact, $groupId, $artifactId) {
		$this->groupId = $groupId;
		$this->artifactId = $artifactId;
		
		$searchQuery = new ArtifactSearchQuery($words, $offset, $isExact, $groupId, $artifactId);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_ARTIFACT, $words, $isExact, $searchQuery, $groupId, 'tracker');
		
		$this->tableHeaders = array(
			_('#'),
			_('Summary'),
			_('Submitted by'),
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
		$groupId = $this->groupId;
		$dateFormat = $GLOBALS['sys_datefmt'];
		
		$return = '';
		for($i = 0; $i < $rowsCount; $i++) {
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'
				.'<td>'.db_result($result, $i, 'artifact_id').'</td>'
				.'<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?group_id='.$groupId.'&amp;atid='
				. db_result($result, $i, 'group_artifact_id') 
				. '&amp;func=detail&aid='
				. db_result($result, $i, 'artifact_id').'"> '
				. html_image('ic/msg.png', '10', '12', array('border'=>'0'))
				. ' '.db_result($result, $i, 'summary').'</a></td>'
				. '<td>'.db_result($result, $i, 'realname')."</td>"
				. '<td>'.date($dateFormat, db_result($result, $i, 'open_date')).'</td></tr>';
		}
		return $return;
	}
	
	/**
	 * getPreviousResultsUrl - get the url to go to see the previous results
	 *
	 * @return string url to previous results page
	 */
	function getPreviousResultsUrl() {
		return parent::getPreviousResultsUrl().'&amp;atid='.$this->artifactId;
	}
	
	/**
	 * getNextResultsUrl - get the url to go to see the next results
	 *
	 * @return string url to next results page
	 */
	function getNextResultsUrl() {
		return parent::getNextResultsUrl().'&amp;atid='.$this->artifactId;
	}
	
	/**
	 * redirectToResult - redirect the user  directly to the result when there is only one matching result
	 */
	function redirectToResult() {
		header('Location: /tracker/?group_id='.$this->groupId.'&atid='.$this->artifactId.'&func=detail&aid='.$this->getResultId('artifact_id'));
		exit();
	}
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
