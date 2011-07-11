<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * Copyright 2011, Franck Villaume - Capgemini
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
require_once $gfcommon.'search/DocsAllSearchQuery.class.php';
require_once $gfcommon.'docman/Document.class.php';

class DocsAllHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param	string	$words words we are searching for
	 * @param	int	$offset offset
	 * @param	boolean	$isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param	array	$sections array of all sections to search in (array of strings)
	 *
	 */
	function DocsAllHtmlSearchRenderer($words, $offset, $isExact, $sections = SEARCH__ALL_SECTIONS) {
		$nbGroups 		= 0;
		$parametersValues 	= array();

		if (session_loggedin()) {
			$u =& session_get_user();
			$listGroups = $u->getGroups();
		}

		if (count($listGroups) > 0) {
			foreach ($listGroups as $group) {
				$userIsGroupMember = forge_check_perm('docman', $group->getID(), 'read');
				$parametersValues[$group->getID()]=$userIsGroupMember;
			}
		} else {
			$parametersValues = array();
		}

		$searchQuery = new DocsAllSearchQuery($words, $offset, $isExact , $sections, $parametersValues);
		$this->HtmlSearchRenderer(SEARCH__TYPE_IS_ALLDOCS, $words, $isExact, $searchQuery);
		$this->tableHeaders = array(
			'&nbsp;',
			_('Project Name'),
			_('Title'),
			_('Description'),
		);
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		$GLOBALS['HTML']->header(array('title'=>_('Search for documents'), 'pagename'=>'search'));
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

		$lastDocGroup = null;

		$rowColor = 0;
		for($i = 0; $i < $rowsCount; $i++) {
			//section changed
			$currentDocGroup = db_result($result, $i, 'project_name');
			$currentDocGroupObject = group_get_object_by_publicname($currentDocGroup);
			if ($lastDocGroup != $currentDocGroup) {
				$return .= '<tr><td colspan="4">'.html_image('ic/home16b.png', '10', '12', array('border' => '0')).'<b>'.util_make_link('/docman/?group_id='.$currentDocGroupObject->getID(),$currentDocGroup).'</b></td></tr>';
				$lastDocGroup = $currentDocGroup;
				$rowColor = 0;
			}
			$document = new Document($currentDocGroupObject, db_result($result, $i, 'docid'));
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($rowColor) .'>'
						. '<td width="5%">&nbsp;</td>'
						. '<td>'.html_image('ic/cfolder15.png', '10', '12', array('border' => '0')).util_make_link('/docman/?group_id='.$currentDocGroupObject->getID().'&amp;view=listfile&amp;dirid='.$document->getDocGroupID(),db_result($result, $i, 'groupname')).'</td>';
						if (db_result($result, $i, 'filetype') == 'URL') {
							$return .= '<td><a href="'.db_result($result, $i, 'filename').'">';
						} else {
							$return .= '<td><a href="'.util_make_url('/docman/view.php/'.db_result($result, $i, 'group_id') . '/'.db_result($result, $i, 'docid').'/'.db_result($result, $i, 'filename')).'">';
						}
			$return .= html_image('ic/msg.png', '10', '12', array('border' => '0'))
						. ' '.db_result($result, $i, 'title').'</a></td>'
						. '<td>'.db_result($result, $i, 'description').'</td></tr>';
			$rowColor++;
		}
		return $return;
	}
}

?>
