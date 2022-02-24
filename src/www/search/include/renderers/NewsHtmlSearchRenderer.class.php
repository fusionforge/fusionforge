<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * Copyright 2013, French Ministry of National Education
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
require_once $gfcommon.'search/NewsSearchQuery.class.php';

class NewsHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	/**
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param bool $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 */
	function __construct($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;

		$searchQuery = new NewsSearchQuery($words, $offset, $isExact, $groupId);

		//init the searchrendererr
		parent::__construct(SEARCH__TYPE_IS_NEWS, $words, $isExact, $searchQuery, $groupId, 'news');

		$this->tableHeaders = array(
			_('Summary'),
			_('Submitted by'),
			_('Post Date'),
		);
	}

	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());
		$rowsCount = count($result);

		$return = '';
		foreach ($result as $row) {
			$return .= '<tr>'
				. '<td>'.util_make_link('/forum/forum.php?forum_id='. $row['forum_id'], html_image('ic/msg.png', 10, 12).' '.$row['summary'])
				. '</td>
				<td style="width: 15%">'.$row['realname'].'</td>
				<td style="width: 15%">'.relative_date($row['post_date']).'</td></tr>';
		}
		return $return;
	}

	/**
	 * getSections - get the array of possible sections to search in
	 *
	 * @param int $groupId
	 * @return array sections
	 */
	static function getSections($groupId) {
		$userIsGroupMember = NewsHtmlSearchRenderer::isGroupMember($groupId);

		return NewsSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
