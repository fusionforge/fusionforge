<?php
/**
 * WikiPlugin Class
 * Wiki Search Engine for Fusionforge
 *
 * Copyright 2006 (c) Alain Peyrat
 *
 * This file is part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfwww.'search/include/renderers/HtmlGroupSearchRenderer.class.php';
require_once 'WikiSearchQuery.class.php';

class WikiHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	var $groupId;

    /**
     * @param string $words words we are searching for
     * @param int $offset offset
     * @param bool $isExact if we want to search for all the words or if only one matching the query is sufficient
     * @param int $groupId group id
     */
	function __construct($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;

		$searchQuery = new WikiSearchQuery($words, $offset, $isExact, $groupId);

		// init the search renderer
		parent::__construct(SEARCH__TYPE_IS_WIKI, $words, $isExact,
			$searchQuery, $groupId, 'wiki');

		$this->tableHeaders = array(_('Page'),_('Author'), _('Date'));
	}

	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());

		$group = group_get_object($this->groupId);
		$group_name = $group->getUnixName();

		$return = '';
		$i = 0;
		foreach ($result as $row) {
			$data = unserialize($row['versiondata']);
			$page_name = preg_replace('/%2f/i', '/', rawurlencode($row['pagename']));
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'
				. '<td><a href="/wiki/g/'. $group_name.'/'. $page_name .'">'
				. html_image('ic/msg.png', '10', '12')
				. ' '.$row['pagename'].'</a></td>
				<td style="width: 15%">'.$data['author'].'</td>
				<td style="width: 15%">'.relative_date($row['mtime']).'</td></tr>';
		}
		return $return;
	}
}
