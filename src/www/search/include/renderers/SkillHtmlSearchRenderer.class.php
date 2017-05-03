<?php
/**
 * Search Engine
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet / Open Wide
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

require_once $gfwww.'search/include/renderers/HtmlSearchRenderer.class.php';
require_once $gfcommon.'search/SkillSearchQuery.class.php';

class SkillHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param bool $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function __construct($words, $offset, $isExact) {

		$searchQuery = new SkillSearchQuery($words, $offset, $isExact);

		parent::__construct(SEARCH__TYPE_IS_SKILL, $words, $isExact, $searchQuery);

		$this->tableHeaders = array(
			_('Name'),
			_('Type'),
			_('Title'),
			_('Keywords'),
			_('From'),
			_('To')
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
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());
		$rowsCount = count($result);

		$monthArray = array();
		for($i = 1; $i <= 12; $i++) {
			array_push($monthArray,date('M', mktime(0, 0, 0, $i, 10, 1980)));
		}

		$return = '';

		$i = 0;
		foreach ($result as $row) {
			$start = $row['start'];
			$startYear = substr($start, 0, 4);
			$startMonth = substr($start, 4, 2);

			$finish = $row['finish'];
			$finishYear = substr($finish, 0, 4);
			$finishMonth = substr($finish, 4, 2);

			$return .= '
			<tr>
				<td>'.util_make_link_u ($row['user_name'],$row['user_id'],$row['realname']).'</td>
				<td>'.$row['type_name'].'</td>
				<td>'.$row['title'].'</td>
				<td>'.$row['keywords'].'</td>
				<td>'.$monthArray[$startMonth - 1].' '.$startYear.'</td>
				<td>'.$monthArray[$finishMonth - 1].' '.$finishYear.'</td>
			<tr>';
		}

		return $return;
	}

	/**
	 * redirectToResult - redirect the user  directly to the result when there is only one matching result
	 */
	function redirectToResult() {
		$result = $this->searchQuery->getData(1)[0];
		$user_name = $result['user_name'];

		session_redirect('/users/'.$user_name.'/');
	}
}
