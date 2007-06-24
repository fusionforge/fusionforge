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
require_once('common/search/PeopleSearchQuery.class.php');

class PeopleHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function PeopleHtmlSearchRenderer($words, $offset, $isExact) {
		
		$searchQuery = new PeopleSearchQuery($words, $offset, $isExact);
		
		$this->HtmlSearchRenderer(SEARCH__TYPE_IS_PEOPLE, $words, $isExact, $searchQuery);
		
		$this->tableHeaders = array(
			_('User name'),
			_('Real name')
		);
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		$GLOBALS['HTML']->header(array('title'=>_('Search')));
		parent::writeHeader();
	}
	
	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();
		
		$return = '';
		for($i = 0; $i < $rowsCount; $i++) {
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td width="40%"><a href="'.$GLOBALS['sys_urlprefix'].'/users/'.db_result($result, $i, 'user_name').'/">'.html_image('ic/msg.png', '10', '12', array('border'=>'0')).' '.db_result($result, $i, 'user_name').'</a></td>'.
				'<td width="60%">'.db_result($result, $i, 'realname').'</td>'.
				'</tr>';
		}
		return $return;
	}
	
	/**
	 * redirectToResult - redirect the user  directly to the result when there is only one matching result
	 */
	function redirectToResult() {
		header('Location: /users/'.$this->getResultId('user_name').'/');
		exit();
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
