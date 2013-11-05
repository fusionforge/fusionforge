<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 * Copyright 2012, Franck Villaume - TrivialDev
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
		$this->writeHeader();
		$this->writeBody();
		$this->writeFooter();
	}

	/**
	 * writeBody - write the Body of the output
	 */
	function writeBody() {
		if (!$this->words) {
			echo '<p class="error">'._('Error') . _(': ') . _('Please enter a term to search for').'</p>';
		} elseif (!forge_get_config('use_fti') && (strlen($this->words) < 3)) {
			echo '<p class="error">'._('Error') . _(': ') . _('Search must be at least three characters').'</p>';
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
			$html .= '<p class="error">'._('Error: search query too short').'</p>';
		}

		// This is quite complex but the goal is to extract all the
		// registered plugins to the hook 'search_engines' and call
		// them.
		$pluginManager = plugin_manager_get_object();
		$searchManager = getSearchManager();
		$engines = $searchManager->getAvailableSearchEngines();

		if (isset($pluginManager->hooks_to_plugins['full_search_engines'])) {
			$p_list = $pluginManager->hooks_to_plugins['full_search_engines'];
			foreach ($p_list as $p_name) {
				$p_obj = $pluginManager->GetPluginObject($p_name);
				$name = $p_obj->text;
				reset($engines);
				foreach($engines as $e) {
					if ($e->type == $p_name) {
						$renderer = $e->getSearchRenderer($this->words,
							$this->offset, $this->isExact, $this->groupId);
						$html .= $this->getPartResult($renderer, 'short_'.$p_name,
													  sprintf(_("%s Search Results"), $name));
					}
				}
			}
		}

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
	* @param object $renderer
	* @param int $section
	* @param string $title
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
            $result .= '<p>'.sprintf(_('No matches found for “%s”'), $this->words);
            $result .= _(' - ');
            $result .= _('No sections available (check your permissions)').'</p>';
		} else {
			$result .= '<p>'.sprintf(_('No matches found for “%s”'), htmlspecialchars($this->words)).'</p>';
		}
		return $result;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
