<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013,2015 Franck Villaume - TrivialDev
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
require_once $gfcommon.'search/DocsSearchQuery.class.php';
require_once $gfcommon.'docman/Document.class.php';

class DocsHtmlSearchRenderer extends HtmlGroupSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array|string $sections array of all sections to search in (array of strings)
	 *
	 */
	function DocsHtmlSearchRenderer($words, $offset, $isExact, $groupId, $sections = SEARCH__ALL_SECTIONS, $rowsPerPage = SEARCH__DEFAULT_ROWS_PER_PAGE, $options = array()) {

		$userIsGroupMember = $this->isGroupMember($groupId);

		$searchQuery = new DocsSearchQuery($words, $offset, $isExact, array($groupId), $sections, $userIsGroupMember, $rowsPerPage, $options);

		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_DOCS, $words, $isExact, $searchQuery, $groupId, 'docman');

		$this->tableHeaders = array(
			_('Directory'),
			_('&nbsp;'),
			_('Title'),
			_('Description'),
			_('Actions')
		);
	}

	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		if (!forge_check_perm('docman', $this->groupId, 'read')) {
			return '';
		}

		global $HTML;
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();

		$return = '';

		$lastDocGroup = null;

		$rowColor = 0;
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentDocGroup = db_result($result, $i, 'groupname');
			$groupObject = group_get_object($this->groupId);
			$document = new Document($groupObject, db_result($result, $i, 'docid'));
			if ($lastDocGroup != $currentDocGroup) {
				$return .= '<tr><td colspan="4">'.html_image('ic/folder.png', 22, 22, array('border' => '0')).util_make_link('/docman/?group_id='.$document->Group->getID().'&view=listfile&dirid='.$document->getDocGroupID(),$currentDocGroup).'</td></tr>';
				$lastDocGroup = $currentDocGroup;
				$rowColor = 0;
			}
			$cells = array();
			$cells[][] = '&nbsp;';
			$cells[][] = util_make_link('/docman/view.php/'.$document->Group->getID().'/'.$document->getID().'/'.urlencode($document->getFileName()), html_image($document->getFileTypeImage(), 22, 22));
			$cells[][] = db_result($result, $i, 'title');
			$cells[][] = db_result($result, $i, 'description');
			if (forge_check_perm('docman', $document->Group->getID(), 'approve')) {
				if (!$document->getLocked() && !$document->getReserved()) {
					$cells[][] = util_make_link('/docman/?group_id='.$document->Group->getID().'&view=listfile&dirid='.$document->getDocGroupID().'&filedetailid='.db_result($result, $i, 'docid'), html_image('docman/edit-file.png', 22, 22, array('alt' => _('Edit this document'))));
				} else {
					$cells[][] = '&nbsp;';
				}
			} else {
				$cells[][] = '&nbsp;';
			}
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($rowColor, true)), $cells);
			$rowColor++;
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
		$userIsGroupMember = DocsHtmlSearchRenderer::isGroupMember($groupId);

		return DocsSearchQuery::getSections($groupId, $userIsGroupMember);
	}
}
