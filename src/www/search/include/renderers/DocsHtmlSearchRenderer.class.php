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
require_once $gfcommon.'search/DocsSearchQuery.class.php';
			  
class DocsHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
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
	function DocsHtmlSearchRenderer($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS) {
	
		$userIsGroupMember = $this->isGroupMember($groupId);
					
		$searchQuery = new DocsSearchQuery($words, $offset, $isExact, $groupId, $sections, $userIsGroupMember);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_DOCS, $words, $isExact, $searchQuery, $groupId, 'docman');
		
		$this->tableHeaders = array(
			'&nbsp;',
			_('#'),
			_('Title'),
			_('Description'),
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
		
		$lastDocGroup = null;
		
		$rowColor = 0;
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentDocGroup = db_result($result, $i, 'groupname');
			if ($lastDocGroup != $currentDocGroup) {
				$return .= '<tr><td colspan="4">'.$currentDocGroup.'</td></tr>';
				$lastDocGroup = $currentDocGroup;
				$rowColor = 0;
			}
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowColor) .'>'
				. '<td width="5%">&nbsp;</td>'
				. '<td>'.db_result($result, $i, 'docid').'</td>'
				. '<td><a href="'.util_make_url ('/docman/view.php/'.$this->groupId . '/'.db_result($result, $i, 'docid').'/'.db_result($result, $i, 'title')).'">'
				. html_image('ic/msg.png', '10', '12', array('border' => '0'))
				. ' '.db_result($result, $i, 'title').'</a></td>'
				. '<td>'.db_result($result, $i, 'description').'</td></tr>';
			$rowColor++;
		}
		return $return;
	}

	/**
	 * getSections - get the array of possible sections to search in
	 * 
  	 * @return array sections
	 */		
	static function getSections($groupId) {
		$userIsGroupMember = DocsHtmlSearchRenderer::isGroupMember($groupId);
		
		return DocsSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}

?>
