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
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array $sections array of all sections to search in (array of strings)
	 *
	 */
	function TasksHtmlSearchRenderer($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
		$userIsGroupMember = $this->isGroupMember($groupId);
		
		$searchQuery = new TasksSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);

		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_TASKS, $words, $isExact, $searchQuery, $groupId, 'pm');
		
		$this->tableHeaders = array(
			'&nbsp;',
			_('#'),
			_('Summary'),
			_('Start Date'),
			_('End Date'),
			_('Created By'),
			_('Completed')
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
		$lastProjectName = null;
		
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentProjectName = db_result($result, $i, 'project_name');
			if ($lastProjectName != $currentProjectName) {
				$return .= '<tr><td colspan="7">'.$currentProjectName.'</td></tr>';
				$lastProjectName = $currentProjectName;
				$rowColor = 0;
			}
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowColor) .'>'
						. ' <td width="5%">&nbsp;</td>'
						. ' <td>'.db_result($result, $i, 'project_task_id').'</td>'
						. ' <td>'
							. '<a href="'.util_make_url ('/pm/task.php?func=detailtask&amp;project_task_id=' . db_result($result, $i, 'project_task_id').'&amp;group_id='.$this->groupId . '&amp;group_project_id='.db_result($result, $i, 'group_project_id')).'">'
							. html_image('ic/msg.png', '10', '12').' '
							. db_result($result, $i, 'summary').'</a></td>'
						. ' <td width="15%">'.date($dateFormat, db_result($result, $i, 'start_date')).'</td>'
						. ' <td width="15%">'.date($dateFormat, db_result($result, $i, 'end_date')).'</td>'
						. ' <td width="15%">'.db_result($result, $i, 'realname').'</td>'
						. ' <td width="8%">'.db_result($result, $i, 'percent_complete').' %</td></tr>';
			$rowColor ++;
		}
		return $return;
	}
		
	/**
	 * getSections - get the array of possible sections to search in
	 * 
  	 * @return array sections
	 */		
	static function getSections($groupId) {
		$userIsGroupMember = TasksHtmlSearchRenderer::isGroupMember($groupId);
		
		return TasksSearchQuery::getSections($groupId, $userIsGroupMember);		
	}
}

?>
