<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * http://gforge.org
 *
 * @version $Id$
 */
 
require_once('www/search/include/renderers/HtmlGroupSearchRenderer.class.php');
require_once('common/search/DocsSearchQuery.class.php');
			  
class DocsHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array $sections array of all sections to search in (array of strings)
	 *
	 */
	function DocsHtmlSearchRenderer($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
	
		$userIsGroupMember = $this->isGroupMember($groupId);
					
		$searchQuery = new DocsSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_DOCS, $words, $isExact, $searchQuery, $groupId, 'docman');
		
		$this->tableHeaders = array(
			'&nbsp;',
			_('#'),
			_('Title'),
			_('Description'),
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
		$dateFormat = $GLOBALS['sys_datefmt'];
		
		$return = '';
		
		$lastDocGroup = null;
		
		$rowColor = 0;
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentDocGroup = db_result($result, $i, 'groupname');
			if ($lastDocGroup != $currentDocGroup) {
				$return .= '<tr><td colspan="4">'.$currentDocGroup.'</td></tr>';
				$lastDocGroup = $currentDocGroup;
				$rowColor = 0;
			}
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowColor) .'>'
				. '<td width="5%">&nbsp;</td>'
				. '<td>'.db_result($result, $i, 'docid').'</td>'
				. '<td><a href="'.$GLOBALS['sys_urlprefix'].'/docman/view.php/'.$this->groupId
				. '/'.db_result($result, $i, 'docid').'/'.db_result($result, $i, 'title').'">'
				. html_image('ic/msg.png', '10', '12', array('border' => '0'))
				. ' '.db_result($result, $i, 'title').'</a></td>'
				. '<td>'.db_result($result, $i, 'description').'</td></tr>';
			$rowColor++;
		}
		return $return;
	}

	/**
	 * getSections - get the array of possible sections to search in
	 * 
  	 * @return array sections
	 */		
	function getSections($groupId) {
		$userIsGroupMember = $this->isGroupMember($groupId);
		
		return DocsSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}

?>
