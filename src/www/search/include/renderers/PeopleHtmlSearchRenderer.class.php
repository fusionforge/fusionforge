<?php
/**
 * Search Engine
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet / Open Wide
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once $gfwww.'search/include/renderers/HtmlSearchRenderer.class.php';
require_once $gfcommon.'search/PeopleSearchQuery.class.php';

class PeopleHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function PeopleHtmlSearchRenderer($words, $offset, $isExact) {
		
		$searchQuery = new PeopleSearchQuery($words, $offset, $isExact);
		
		$this->HtmlSearchRenderer(SEARCH__TYPE_IS_PEOPLE, $words, $isExact, $searchQuery);
		
		$this->tableHeaders = array(
			_('User name'),
			_('Real name')
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
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();
		
		$return = '';
		for($i = 0; $i < $rowsCount; $i++) {
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td width="40%"><a href="'.util_make_url_u (db_result($result, $i, 'user_name'),db_result($result, $i, 'user_id')).'">'.html_image('ic/msg.png', '10', '12').' '.db_result($result, $i, 'user_name').'</a></td>'.
				'<td width="60%">'.db_result($result, $i, 'realname').'</td>'.
				'</tr>';
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
