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
require_once('common/search/TrackersSearchQuery.class.php');
	
class TrackersHtmlSearchRenderer extends HtmlGroupSearchRenderer {

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
	function TrackersHtmlSearchRenderer($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
		$userIsGroupMember = $this->isGroupMember($groupId);
			
		$searchQuery = new TrackersSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_TRACKERS, $words, $isExact, $searchQuery, $groupId, 'tracker');
		
		$this->tableHeaders = array(
			'&nbsp;',
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
		$dateFormat = _('Y-m-d H:i');
			
		$return = '';
		$rowColor = 0;
		$lastTracker = null;
		
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentTracker = db_result($result, $i, 'name');
			if ($lastTracker != $currentTracker) {
				$return .= '<tr><td colspan="5">'.$currentTracker.'</td></tr>';
				$lastTracker = $currentTracker;
				$rowColor = 0;
			}
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowColor) .'>'
						. '<td width="5%">&nbsp;</td>'
						. '<td>'.db_result($result, $i, 'artifact_id').'</td>'
						. '<td>'
							. '<a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?func=detail&group_id='.$this->groupId.'&aid='.db_result($result, $i, 'artifact_id')
							. '&atid='.db_result($result, $i, 'group_artifact_id').'">'
							. html_image('ic/msg.png', '10', '12', array('border'=>'0')).' '.db_result($result, $i, 'summary')
							. '</a></td>'		
						. '<td width="15%">'.db_result($result, $i, 'realname').'</td>'
						. '<td width="15%">'.date($dateFormat, db_result($result, $i, 'open_date')).'</td></tr>';
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
		
		return TrackersSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
