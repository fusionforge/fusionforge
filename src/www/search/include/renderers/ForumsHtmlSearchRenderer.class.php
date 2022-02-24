<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
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
require_once $gfcommon.'search/ForumsSearchQuery.class.php';

class ForumsHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	/**
	 * @param string       $words    words we are searching for
	 * @param int          $offset   offset
	 * @param bool         $isExact  if we want to search for all the words or if only one matching the query is sufficient
	 * @param int          $groupId  group id
	 * @param array|string|int $sections array of all sections to search in (array of strings)
	 */
	function __construct($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
		$userIsGroupMember = $this->isGroupMember($groupId);

		$searchQuery = new ForumsSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);

		parent::__construct(SEARCH__TYPE_IS_FORUMS, $words, $isExact, $searchQuery, $groupId, 'forums');

		$this->tableHeaders = array(
			'',
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
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());

		$return = '';
		$rowColor = 0;
		$lastForumName = null;

		foreach ($result as $row) {
			//section changed
			$currentForumName = $row['forum_name'];
			if ($lastForumName != $currentForumName) {
				$return .= '<tr><td colspan="4">'.$currentForumName.'</td></tr>';
				$lastForumName = $currentForumName;
				$rowColor = 0;
			}
			$return .= '<tr>'
						. '<td style="width: 5%"></td>'
						. '<td>'.util_make_link('/forum/message.php?msg_id='. $row['msg_id'], html_image('ic/msg.png').' '.$row['subject'])
						.'</td>'
						. '<td style="width: 15%">'.$row['realname'].'</td>'
						. '<td style="width: 15%">'.relative_date($row['post_date']).'</td></tr>';
			$rowColor ++;
		}
		return $return;
	}

	/**
	 * getSections - get the array of possible sections to search in
	 *
	 * @param  int $groupId
	 * @return array sections
	 */
	static function getSections($groupId) {
		$userIsGroupMember = ForumsHtmlSearchRenderer::isGroupMember($groupId);

		return ForumsSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}
