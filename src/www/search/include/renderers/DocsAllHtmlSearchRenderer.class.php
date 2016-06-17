<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2013, French Ministry of National Education
 * Copyright 2013,2015, Franck Villaume - TrivialDev
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
require_once $gfcommon.'search/DocsSearchQuery.class.php';
require_once $gfcommon.'docman/Document.class.php';

class DocsAllHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * @param	string	$words words we are searching for
	 * @param	int	$offset offset
	 * @param	boolean	$isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param	array|string	$sections array of all sections to search in (array of strings)
	 */
	function __construct($words, $offset, $isExact, $sections = SEARCH__ALL_SECTIONS) {
		$groupIdValidArr = array();

		if (session_loggedin()) {
			$u =& session_get_user();
			$listGroups = $u->getGroups();
			if (count($listGroups) > 0) {
				foreach ($listGroups as $group) {
					if (forge_check_perm('docman', $group->getID(), 'read')) {
						$groupIdValidArr[] = $group->getID();
					}
				}
			}
		}
		$searchQuery = new DocsSearchQuery($words, $offset, $isExact, $groupIdValidArr, $sections);
		parent::__construct(SEARCH__TYPE_IS_ALLDOCS, $words, $isExact, $searchQuery);
		$this->tableHeaders = array(
			_('Project'),
			_('Directory'),
			'',
			_('Title'),
			_('Description')
		);
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		$GLOBALS['HTML']->header(array('title'=>_('Search for documents')));
		parent::writeHeader();
	}

	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		global $HTML;
		$result = $this->searchQuery->getData($this->searchQuery->getRowsPerPage(),$this->searchQuery->getOffset());
		$rowsCount = count($result);

		$return = '';

		$lastGroupID = null;
		$lastDocGroupID = null;

		$rowColor = 0;
		$i = 0;
		foreach ($result as $row) {
			$cells = array();
			$document = document_get_object($row['docid'], $row['group_id']);
			$currentDocGroup = $row['project_name'];
			if ($lastGroupID != $document->Group->getID()) {
				$cells[] = array(html_image('ic/home16b.png', 10, 12, array('border' => 0)).'<b>'.util_make_link('/docman/?group_id='.$document->Group->getID(),$currentDocGroup).'</b>', 'colspan' => 4);
				$lastGroupID = $document->Group->getID();
				$rowColor = 0;
				$return .= $HTML->multiTableRow(array(), $cells);
			}
			$cells = array();
			$cells[][] = '&nbsp;';
			if ($lastDocGroupID != $document->getDocGroupID()) {
				$cells[][] = html_image('ic/folder.png', 22, 22, array('border' => 0)).util_make_link('/docman/?group_id='.$document->Group->getID().'&view=listfile&dirid='.$document->getDocGroupID(),$row['groupname']);
				$lastDocGroupID = $document->getDocGroupID();
			} else {
				$cells[][] = '&nbsp';
			}
			if ($document->isURL()) {
				$cells[][] = util_make_link($document->getFileName(), html_image($document->getFileTypeImage(), 22, 22), array('title' => _('Visit this link')), true);
			} else {
				$cells[][] = util_make_link('/docman/view.php/'.$row['group_id'] . '/'.$row['docid'].'/'.$row['filename'], html_image($document->getFileTypeImage(), 22, 22), array('title' => _('View this document')));
			}
			$cells[][] = $row['title'];
			$cells[][] = $row['description'];
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($rowColor, true)), $cells);
			$rowColor++;
		}
		return $return;
	}
}
