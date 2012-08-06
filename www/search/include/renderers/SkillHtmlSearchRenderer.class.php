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
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function SkillHtmlSearchRenderer($words, $offset, $isExact) {

		$searchQuery = new SkillSearchQuery($words, $offset, $isExact);

		$this->HtmlSearchRenderer(SEARCH__TYPE_IS_SKILL, $words, $isExact, $searchQuery);

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
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();

		$monthArray = array();
		for($i = 1; $i <= 12; $i++) {
			array_push($monthArray,date('M', mktime(0, 0, 0, $i, 10, 1980)));
		}

		$return = '';

		for($i = 0; $i < $rowsCount; $i++) {
			$start = db_result($result, $i, 'start');
			$startYear = substr($start, 0, 4);
			$startMonth = substr($start, 4, 2);

			$finish = db_result($result, $i, 'finish');
			$finishYear = substr($finish, 0, 4);
			$finishMonth = substr($finish, 4, 2);

			$return .= '
			<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i).'>
				<td>'.util_make_link_u (db_result($result, $i, 'user_name'),db_result($result, $i, 'user_id'),db_result($result, $i, 'realname')).'</td>
				<td>'.db_result($result, $i, 'type_name').'</td>
				<td>'.db_result($result, $i, 'title').'</td>
				<td>'.db_result($result, $i, 'keywords').'</td>
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
		session_redirect('/users/'.$this->getResultId('user_name').'/');
	}
}

?>
