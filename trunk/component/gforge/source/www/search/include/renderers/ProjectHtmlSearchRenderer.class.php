<?php

/**
 * GForge Search Engine
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2004 (c) Guillaume Smet / Open Wide
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once $gfwww.'search/include/renderers/HtmlSearchRenderer.class.php';
require_once $gfcommon.'search/ProjectSearchQuery.class.php';

class ProjectHtmlSearchRenderer extends HtmlSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function ProjectHtmlSearchRenderer($words, $offset, $isExact) {
		
		$searchQuery = new ProjectSearchQuery($words, $offset, $isExact);
		
		$this->HtmlSearchRenderer(SEARCH__TYPE_IS_SOFTWARE, $words, $isExact, $searchQuery);
		
		$this->tableHeaders = array(
			_('Group Name'),
			_('Description')
		);
	}

	/**
	 * writeHeader - write the header of the output
	 */
	function writeHeader() {
		$GLOBALS['HTML']->header(array('title'=>_('Search'), 'pagename'=>'search'));
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
			if (db_result($result, $i, 'type') == 2) {
				$what = 'foundry';
			} else {
				$what = 'projects';
			}		
			$return .= '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i).'>'
				.'<td width="30%"><a href="'.util_make_url('/'.$what.'/'.db_result($result, $i, 'unix_group_name').'/').'">'
				.html_image('ic/msg.png', '10', '12', array('border'=>'0'))
				.' '.$this->highlightTargetWords(db_result($result, $i, 'group_name')).'</a></td>'
				.'<td width="70%">'.$this->highlightTargetWords(db_result($result, $i, 'short_description')).'</td></tr>';
		}
		
		return $return;
	}

	/**
	 * redirectToResult - redirect the user  directly to the result when there is only one matching result
	 */
	function redirectToResult() {
		global $sys_use_fti;
		
		$project_name = $this->getResultId('unix_group_name');
		$project_id = $this->getResultId('group_id');
		
		if ($sys_use_fti) {
			// If FTI is being used, the project name returned by the query will be "<b>projectname</b>", so
			// we remove the HTML code (otherwise we'd get an error)
			$project_name = str_replace('<b>', '', $project_name);
			$project_name = str_replace('</b>', '', $project_name);
		}
		
		if ($this->getResultId('type') == 2) {
			header('Location: /foundry/'.$project_name.'/');
		} else {
			header('Location: '.util_make_url_g($project_name,$project_id));
		}
		exit();
	}
	
}

?>
