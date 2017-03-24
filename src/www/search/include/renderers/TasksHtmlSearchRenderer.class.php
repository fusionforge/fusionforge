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
require_once $gfcommon.'search/TasksSearchQuery.class.php';

class TasksHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	/**
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array|string $sections array of all sections to search in (array of strings)
	 */
	function __construct($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
		$userIsGroupMember = $this->isGroupMember($groupId);

		$searchQuery = new TasksSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);

		parent::__construct(SEARCH__TYPE_IS_TASKS, $words, $isExact, $searchQuery, $groupId, 'pm');

		$this->tableHeaders = array(
			'&nbsp;',
			_('#'),
			_('Summary'),
			_('Start Date'),
			_('End Date'),
			_('Created by'),
			_('Completed')
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
		$lastProjectName = null;

		foreach ($result as $row) {
			//section changed
			$currentProjectName = $row['project_name'];
			if ($lastProjectName != $currentProjectName) {
				$return .= '<tr><td colspan="7">'.$currentProjectName.'</td></tr>';
				$lastProjectName = $currentProjectName;
				$rowColor = 0;
			}
			$return .= '<tr>'
						. ' <td style="width: 5%">&nbsp;</td>'
						. ' <td>'.$row['project_task_id'].'</td>'
						. ' <td>'
							. '<a href="'.util_make_url ('/pm/task.php?func=detailtask&amp;project_task_id=' . $row['project_task_id'].'&amp;group_id='.$this->groupId . '&amp;group_project_id='.$row['group_project_id']).'">'
							. html_image('ic/msg.png', 10, 12).' '
							. $row['summary'].'</a></td>'
						. ' <td style="width: 15%">'.relative_date($row['start_date']).'</td>'
						. ' <td style="width: 15%">'.relative_date($row['end_date']).'</td>'
						. ' <td style="width: 15%">'.$row['realname'].'</td>'
						. ' <td style="width: 8%">'.$row['percent_complete'].' %</td></tr>';
			$rowColor ++;
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
		$userIsGroupMember = TasksHtmlSearchRenderer::isGroupMember($groupId);

		return TasksSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}
