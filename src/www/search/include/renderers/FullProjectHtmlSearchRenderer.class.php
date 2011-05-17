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

require_once $gfcommon.'include/pre.php';
require_once $gfwww.'search/include/renderers/HtmlGroupSearchRenderer.class.php';
require_once $gfwww.'search/include/renderers/ForumsHtmlSearchRenderer.class.php';
require_once $gfwww.'search/include/renderers/TrackersHtmlSearchRenderer.class.php';
require_once $gfwww.'search/include/renderers/TasksHtmlSearchRenderer.class.php';
require_once $gfwww.'search/include/renderers/DocsHtmlSearchRenderer.class.php';
require_once $gfwww.'search/include/renderers/FrsHtmlSearchRenderer.class.php';
require_once $gfwww.'search/include/renderers/NewsHtmlSearchRenderer.class.php';

class FullProjectHtmlSearchRenderer extends HtmlGroupSearchRenderer {
	
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
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 *
	 */
	function FullProjectHtmlSearchRenderer($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;
		$this->words = $words;
		$this->isExact = $isExact;
		
		$this->HtmlGroupSearchRenderer(SEARCH__TYPE_IS_ADVANCED, $words, $isExact, '', $groupId);
	}
	
	/**
	 * flush - overwrites the flush method from htmlrenderer
	 */
	function flush() {
		$this->writeBody();
		$this->writeFooter();
	}

	/**
	 * writeBody - write the Body of the output
	 */
	function writeBody() {
		$title = _('Entire project search');
		site_project_header(array('title' => $title, 'group' => $this->groupId, 'toptab' => ''));
		echo $this->getResult();
	}
	
	/**
	 * getResult - returns the Body of the output
	 * 
  	 * @return string result of all selected searches
	 */
	function getResult() {
		$html = '';

		$group = group_get_object($this->groupId);

		if ($group->usesForum()) {
			$forumsRenderer		= new ForumsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		}
		if ($group->usesTracker()) {
			$trackersRenderer	= new TrackersHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		}
		if ($group->usesPM()) {
			$tasksRenderer		= new TasksHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		}
		if ($group->usesDocman()) {
			$docsRenderer		= new DocsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		}
		if ($group->usesFRS()) {
			$frsRenderer 		= new FrsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		}
		if ($group->usesNews()) {
			$newsRenderer		= new NewsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		}

		$validLength = (strlen($this->words) >= 3);

		if (isset($trackersRenderer) && ($validLength || (is_numeric($this->words) && $trackersRenderer->searchQuery->implementsSearchById()))) {
			$html .= $this->getPartResult($trackersRenderer, 'short_tracker', _('Tracker Search Results'));
		}

		if (isset($forumsRenderer) && ($validLength || (is_numeric($this->words) && $forumsRenderer->searchQuery->implementsSearchById()))) {
			$html .= $this->getPartResult($forumsRenderer, 'short_forum', _('Forum Search Results'));
		}

		if (isset($tasksRenderer) && ($validLength || (is_numeric($this->words) && $tasksRenderer->searchQuery->implementsSearchById()))) {
			$html .= $this->getPartResult($tasksRenderer, 'short_pm', _('Task Search Results'));
		}

		if (isset($docsRenderer) && ($validLength || (is_numeric($this->words) && $docsRenderer->searchQuery->implementsSearchById()))) {
			$html .= $this->getPartResult($docsRenderer, 'short_docman', _('Documentation Search Results'));
		}

		if (isset($frsRenderer) && ($validLength || (is_numeric($this->words) && $frsRenderer->searchQuery->implementsSearchById()))) {
			$html .= $this->getPartResult($frsRenderer, 'short_files', _('Files Search Results'));
		}

		if (isset($newsRenderer) && ($validLength || (is_numeric($this->words) && $newsRenderer->searchQuery->implementsSearchById()))) {
			$html .= $this->getPartResult($newsRenderer, 'short_news', _('News Search Results'));
		}

		if (! $html && ! $validLength) {
			$html .= '<div class="error">'._('Error: search query too short').'</div>';
		}

		plugin_hook('full_search_engines', $this);
		$plugin = plugin_manager_get_object();
		$html .= $plugin->getReturnedValue('full_search_engines');

/*		
		$renderer = new ForumsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		$html .= $this->getPartResult($renderer, 'short_forum', _('Forum Search Results'));
		
		$renderer = new TrackersHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		$html .= $this->getPartResult($renderer,  'short_tracker', _('Tracker Search Results'));
		
		$renderer = new TasksHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		$html .= $this->getPartResult($renderer, 'short_pm', _('Task Search Results'));

		$renderer = new DocsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		$html .= $this->getPartResult($renderer, 'short_docman', _('Documentation Search Results'));

		$renderer = new FrsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		$html .= $this->getPartResult($renderer, 'short_files', _('Files Search Results'));
		
		$renderer = new NewsHtmlSearchRenderer($this->words, $this->offset, $this->isExact, $this->groupId);
		$html .= $this->getPartResult($renderer, 'short_news', _('News Search Results'));
*/

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
		
		if ($title === '')
			$title = $section;
		
		$result .= '<h2><a name="'.$section.'"></a>'.$title.'</h2>';
		
		if ($renderer->searchQuery->getRowsCount() > 0) {
			$result .= $GLOBALS['HTML']->listTabletop($renderer->tableHeaders);
			$result .= $renderer->getRows();			
			$result .= $GLOBALS['HTML']->listTableBottom();			
		} elseif(method_exists($renderer, 'getSections') && (count($renderer->getSections($this->groupId)) == 0)) {
			$result .= '<p>'.sprintf(_('No matches found for <em>%s</em> - No sections available (check your permissions)'), $this->words).'</p>';
		} else {
			$result .= '<p>'.sprintf(_('No matches found for <em>%s</em>'), $this->words).'</p>';
		}
		return $result;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
