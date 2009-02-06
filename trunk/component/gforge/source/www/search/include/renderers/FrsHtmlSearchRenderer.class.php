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
 
require_once $gfwww.'search/include/renderers/HtmlGroupSearchRenderer.class.php';
require_once $gfcommon.'search/FrsSearchQuery.class.php';
			  
class FrsHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
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
	function FrsHtmlSearchRenderer($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
		$userIsGroupMember = $this->isGroupMember($groupId);
		
		$searchQuery = new FrsSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_FRS, $words, $isExact, $searchQuery, $groupId, 'frs');
		
		$this->tableHeaders = array(
			'&nbsp;',
			_('Release name'),
			_('Posted by'),
			_('Post date'),
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
		$rowColor = 0;
		$lastPackage = null;
		
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentPackage = db_result($result, $i, 'package_name');
			if ($lastPackage != $currentPackage) {
				$return .= '<tr><td colspan="4">'.$currentPackage.'</td></tr>';
				$lastPackage = $currentPackage;
				$rowColor = 0;
			}
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowColor) .'>'
				. '<td width="5%">&nbsp;</td>'
				. '<td>'.util_make_link ('/frs/shownotes.php?release_id='.db_result($result, $i, 'release_id'),db_result($result, $i, 'release_name')).'</td>'
				. '<td width="15%">'.db_result($result, $i, 'realname').'</td>'
				. '<td width="15%">'.date($dateFormat,db_result($result,$i, 'release_date')).'</td></tr>';
			$rowColor ++;
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
	
		return FrsSearchQuery::getSections($groupId, $userIsGroupMember);	
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
