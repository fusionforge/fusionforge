<?php
/**
 * Search Engine
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet
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
require_once $gfcommon.'search/ArtifactSearchQuery.class.php';

class ArtifactHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
	/**
	 * artifact id
	 *
	 * @var int $artifactId
	 */
	var $artifactId;
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param int $artifactId artifact id
	 */
	function ArtifactHtmlSearchRenderer($words, $offset, $isExact, $groupId, $artifactId) {
		$this->groupId = $groupId;
		$this->artifactId = $artifactId;
		
		$searchQuery = new ArtifactSearchQuery($words, $offset, $isExact, $groupId, $artifactId);
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_ARTIFACT, $words, $isExact, $searchQuery, $groupId, 'tracker');
		
		$this->tableHeaders = array(
			_('#'),
			_('Summary'),
			_('Submitted by'),
			_('Date')
		);
	}
	
	function getFilteredRows() {
		$rowsCount = $this->searchQuery->getRowsCount();
		$result =& $this->searchQuery->getResult();

		$fields = array ('group_artifact_id',
				 'artifact_id',
				 'name',
				 'summary',
				 'realname',
				 'open_date');

		$fd = array();
		for($i = 0; $i < $rowsCount; $i++) {
			if (forge_check_perm('tracker',
					     db_result($result, $i, 'group_artifact_id'),
					     'read')) {
				$r = array();
				foreach ($fields as $f) {
					$r[$f] = db_result($result, $i, $f);
				}
				$fd[] = $r;
			}
		}
		return $fd;
	}

	/**
	 * getRows - get the html output for result rows
	 *
	 * @return string html output
	 */
	function getRows() {
		$fd = $this->getFilteredRows();

		$groupId = $this->groupId;
		$dateFormat = _('Y-m-d H:i');
		
		$return = '';
		foreach ($fd as $row) {
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'
				.'<td>'.$row['artifact_id'].'</td>'
				.'<td><a href="'.util_make_url ('/tracker/?group_id='.$groupId.'&amp;atid=' . $row['group_artifact_id'] . '&amp;func=detail&aid=' . $row['artifact_id']).'"> '
				. html_image('ic/tracker20g.png')
				. ' '.$row['summary'].'</a></td>'
				. '<td>'.$row['realname']."</td>"
				. '<td>'.date($dateFormat, $row['open_date']).'</td></tr>';
		}
		return $return;
	}
	
	/**
	 * getPreviousResultsUrl - get the url to go to see the previous results
	 *
	 * @return string url to previous results page
	 */
	function getPreviousResultsUrl() {
		return parent::getPreviousResultsUrl().'&amp;atid='.$this->artifactId;
	}
	
	/**
	 * getNextResultsUrl - get the url to go to see the next results
	 *
	 * @return string url to next results page
	 */
	function getNextResultsUrl() {
		return parent::getNextResultsUrl().'&amp;atid='.$this->artifactId;
	}
	
	/**
	 * redirectToResult - redirect the user  directly to the result when there is only one matching result
	 */
	function redirectToResult() {
        session_redirect('/tracker/?group_id='.$this->groupId.'&atid='.$this->artifactId.'&func=detail&aid='.$this->getResultId('artifact_id'));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
