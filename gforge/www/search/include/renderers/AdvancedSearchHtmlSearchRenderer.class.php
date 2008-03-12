<?php

/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('pre.php');
require_once('www/search/include/renderers/HtmlGroupSearchRenderer.class.php');

class AdvancedSearchHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;

	/**
	 * the words to search for
	 *
	 * @var string $words
	 */	
	var $words;

	/**
	 * flag to define whether the result must contain all words or only one of them
	 *
	 * @var boolean $isExact
	 */
	var $isExact;
	
	/**
	 * selected parents sections
	 *
	 * @var array $selectedParentSections
	 */
	var $selectedParentSections = array();

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 *
	 */
	function AdvancedSearchHtmlSearchRenderer($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;
		$this->words = $words;
		$this->isExact = $isExact;
		$searchQuery =& $this->searchQuery;
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_ADVANCED, $words, $isExact, $searchQuery, $groupId);
	}
	
	/**
	 * flush - overwrites the flush method from htmlrenderer
	 */
	function flush() {
		$this->writeHeader();
		$this->writeBody();
		$this->writeFooter();
	}

	/**
	 * writeHeader - write the header of the output
	 */		
	function writeHeader() {
		site_project_header(array('title' => _('Advanced search'), 'group' => $this->groupId, 'toptab'=>'none'));
		$sectionarray = $this->getSectionArray();
		$this->handleTransferInformation($sectionarray);
		
		$GLOBALS['HTML']->advancedSearchBox($sectionarray, $this->groupId, $this->words, $this->isExact);
	}
	
	/**
	 * writeBody - write the Body of the output
	 */
	function writeBody() {
		if (strlen($this->words) < 3) {
			echo '<br><h1>'._('Error: Under min length search').'<h1><br>';
		} else {
			echo $this->getResult();
		}
	}
	
	/**
	 * getResult - returns the Body of the output
	 * 
  	 * @return string result of all selected searches
	 */
	function getResult() {
		$html = '';
		
		if (in_array('short_forum', $this->selectedParentSections)) {
			$renderer = new ForumsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_forum'));
			$html .= $this->getPartResult($renderer, 'short_forum', _('Forum Search Results'));
		}
		
		if (in_array('short_tracker', $this->selectedParentSections)) {
			$renderer = new TrackersHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId,  $this->getSelectedChildSections('short_tracker'));
			$html .= $this->getPartResult($renderer,  'short_tracker', _('Tracker Search Results'));
		}
		
		if (in_array('short_pm', $this->selectedParentSections)) {
			$renderer = new TasksHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_pm'));
			$html .= $this->getPartResult($renderer, 'short_pm', _('Task Search Results'));
		}

		if (in_array('short_docman', $this->selectedParentSections)) {
			$renderer = new DocsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_docman'));
			$html .= $this->getPartResult($renderer, 'short_docman', _('Documentation Search Results'));
		}

		if (in_array('short_files', $this->selectedParentSections)) {
			$renderer = new FilesHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId, $this->getSelectedChildSections('short_files'));
			$html .= $this->getPartResult($renderer, 'short_files', _('Files Search Results'));
		}
		
		if (in_array('short_news', $this->selectedParentSections)) {
			$renderer = new NewsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
			$html .= $this->getPartResult($renderer, 'short_news', _('News Search Results'));
		}

		return $html.'<br />'; 
	}

	/**
	* getPartResult - returns the result of the given renderer 
	* 
  	* @return string result of the renderer
	*/			
	function getPartResult($renderer, $section, $title='') {
		$result = '';
		$renderer->searchQuery->executeQuery();
		$query = NULL;
		
		if ($title === '')
			$title = $section;
		
		$result .= '<h3><a name="'.$section.'"></a>'.$title.'</h3>';
		
		if ($renderer->searchQuery->getRowsCount() > 0) {
			$result .= $GLOBALS['HTML']->listTabletop($renderer->tableHeaders);
			$result .= $renderer->getRows();			
			$result .= $GLOBALS['HTML']->listTableBottom();			
		} elseif(method_exists($renderer, 'getSections') && (count($renderer->getSections($this->groupId)) == 0)) {
			$result .= '<p>'.sprintf(_('No matches found - No sections available (check your permissions)'), htmlspecialchars($query['words'])).'</p>';
		} else {
			$result .= '<p>'.sprintf(_('No matches found'), htmlspecialchars($query['words'])).'</p>';
		}
		return $result;
	}
	
	/**
	* getSectionArray - creates an array of sections in which the user can search
	* 
  	* @return array sections 
	*/		
	function getSectionArray() {
		$sections = array();
		$group =& group_get_object($this->groupId);
		
		if ($group->usesForum()) {
			require_once('ForumsHtmlSearchRenderer.class.php');
			$undersections = ForumsHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_forum'] = $undersections;
			}
		}
		
		if ($group->usesTracker()) {
			require_once('TrackersHtmlSearchRenderer.class.php');
			$undersections = TrackersHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_tracker'] = $undersections;
			}
		}

		if ($group->usesPM()) {
			require_once('TasksHtmlSearchRenderer.class.php');
			$undersections = TasksHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_pm'] = $undersections;
			}

		}

		if ($group->usesDocman()) {
			require_once('DocsHtmlSearchRenderer.class.php');
			$undersections = DocsHtmlSearchRenderer::getSections($this->groupId);	
			if (count($undersections) > 0) {
				$sections['short_docman'] = $undersections;
			}
		}
		
		if ($group->usesNews()) {
			require_once('NewsHtmlSearchRenderer.class.php');
			$sections['short_news'] = true;
		}
			
		if ($group->usesFRS()) {
			require_once('FrsHtmlSearchRenderer.class.php');
			$undersections = FrsHtmlSearchRenderer::getSections($this->groupId);
			if (count($undersections) > 0) {
				$sections['short_files'] = $undersections;
			}
		}

		return $sections;
	}
	
	/**
	* handleTransferInformation - marks parentsections if child is marked and processes cookie information
	* 
	*/		
	function handleTransferInformation(&$sectionarray) {
		//get through all sections
		//if a childsection is marked to search in, mark the parent too
		
		$postArray = _getPostArray();
		
		foreach($sectionarray as $key => $section) {
			if(is_array($section)) {
				foreach ($postArray as $postkey => $postvalue) {
					if (substr($postkey, 0, strlen($key)) == $key) {
						$this->selectedParentSections[] = $key;
						break;
					}
				}
			} elseif(isset($postArray[$key])) {
					$this->selectedParentSections[] = $key;
			}
		}

	}
	
	/**
	* getSelectedChildSections - gets all selected child sections from the given parentsection
	* 
	* @param string	$parentsection the parentsection
	* 
  	* @return array all child sections from the parent section the user wants to search in
	*/		
	function getSelectedChildSections($parentsection) {
		$sections = array();
		$postArray = _getPostArray();
		
		foreach($postArray as $key => $value) {
			if (substr($key, 0, strlen($parentsection)) == $parentsection) {
				if (strlen(substr($key, strlen($parentsection) , strlen($key))) > 0) {
					array_push($sections, urldecode(substr($key, strlen($parentsection), strlen($key))));
				}
			}
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
