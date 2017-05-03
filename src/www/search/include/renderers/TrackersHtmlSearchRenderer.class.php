<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once $gfwww.'search/include/renderers/HtmlGroupSearchRenderer.class.php';
require_once $gfcommon.'search/TrackersSearchQuery.class.php';

class TrackersHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	/**
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param bool $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array|string $sections array of all sections to search in (array of strings)
	 */
	function __construct($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
		$userIsGroupMember = $this->isGroupMember($groupId);

		$searchQuery = new TrackersSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);

		parent::__construct(SEARCH__TYPE_IS_TRACKERS, $words, $isExact, $searchQuery, $groupId, 'tracker');

		$this->tableHeaders = array(
			_('Tracker'),
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
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());

		$return = '';
		$rowColor = 0;
		$lastTracker = null;

		foreach ($result as $row) {
			//section changed
			$currentTracker = $row['name'];
			if ($lastTracker != $currentTracker) {
				$return .= '<tr><td colspan="5">'.util_make_link('/tracker/?atid='.$row['group_artifact_id'].'&group_id='.$this->groupId.'&func=browse',$currentTracker).'</td></tr>';
				$lastTracker = $currentTracker;
				$rowColor = 0;
			}
			$return .= '<tr>'
						. '<td style="width: 5%">&nbsp;</td>'
						. '<td>'.$row['artifact_id'].'</td>'
						. '<td>'.util_make_link('/tracker/?func=detail&group_id='.$this->groupId.'&aid='.$row['artifact_id'].'&atid='.$row['group_artifact_id'],
									html_image('ic/tracker20g.png').' '.$row['summary'])
							.'</td>'
						. '<td style="width: 15%">'.$row['realname'].'</td>'
						. '<td style="width: 15%">'.relative_date($row['open_date']).'</td></tr>';
			$rowColor++;
		}
		return $return;
	}

	/**
	 * getSections - get the array of possible sections to search in
	 *
	 * @param int $groupId group id
  	 * @return array sections
	 */
	static function getSections($groupId) {
		$userIsGroupMember = TrackersHtmlSearchRenderer::isGroupMember($groupId);

		return TrackersSearchQuery::getSections($groupId, $userIsGroupMember);
	}

	/**
	 * redirectToResult - redirect the user directly to the result when there is only one matching result
	 */
	function redirectToResult() {
		$result = $this->searchQuery->getData(1)[0];

		session_redirect('/tracker/?group_id='.$this->groupId.'&atid='.$result['group_artifact_id'].'&func=detail&aid='.$result['artifact_id']);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
