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
require_once $gfcommon.'search/PeopleSearchQuery.class.php';

class PeopleHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param bool $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function __construct($words, $offset, $isExact) {

		$searchQuery = new PeopleSearchQuery($words, $offset, $isExact);

		parent::__construct(SEARCH__TYPE_IS_PEOPLE, $words, $isExact, $searchQuery);

		$this->tableHeaders = array(
			_('User Name'),
			_('Real Name')
		);
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		$GLOBALS['HTML']->header(array('title'=>_('People Search')));
		parent::writeHeader();
	}

	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());

		$return = '';
		$i = 0;
		foreach ($result as $row) {
			$i++;
			$return .= '<tr>'.
				'<td style="width: 40%"><a href="'.util_make_url_u($row['user_name']).'">'.html_image('ic/msg.png', 10, 12).' '.$row['user_name'].'</a></td>'.
				'<td style="width: 60%">'.$row['realname'].'</td>'.
				'</tr>';
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
