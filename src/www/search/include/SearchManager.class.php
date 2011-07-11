<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Guillaume Smet
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

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';
require_once $gfwww.'search/include/engines/DocsGroupSearchEngine.class.php';
require_once $gfwww.'search/include/engines/ForumsGroupSearchEngine.class.php';
require_once $gfwww.'search/include/engines/FrsGroupSearchEngine.class.php';
require_once $gfwww.'search/include/engines/NewsGroupSearchEngine.class.php';
require_once $gfwww.'search/include/engines/TasksGroupSearchEngine.class.php';
require_once $gfwww.'search/include/engines/TrackersGroupSearchEngine.class.php';

require_once $gfwww.'search/include/engines/ArtifactSearchEngine.class.php';
require_once $gfwww.'search/include/engines/ForumSearchEngine.class.php';

function & getSearchManager() {
	if(!isset($GLOBALS['OBJ_SEARCH_MANAGER'])) {
		$GLOBALS['OBJ_SEARCH_MANAGER'] = new SearchManager();
	}
	return $GLOBALS['OBJ_SEARCH_MANAGER'];
}

class SearchManager {
	var $searchEngines = array();
	var $parameters = array();
	var $parametersValues = array();

	function SearchManager() {
		$this->loadSearchEngines();
		$this->loadParameters();
	}

	function setParametersValues($parametersValues) {
		for($i = 0, $max = count($this->parameters); $i < $max; $i++) {
			if(isset($parametersValues[$this->parameters[$i]])) {
				$this->parametersValues[$this->parameters[$i]] = (int) $parametersValues[$this->parameters[$i]];
			}
		}
	}

	function getParameters() {
		return $this->parametersValues;
	}

	function addSearchEngine($type, &$searchEngine, $format = SEARCH__OUTPUT_HTML) {
		$this->searchEngines[$format][$type] =& $searchEngine;
	}

	function addParameter($parameterName) {
		if(!in_array($parameterName, $this->parameters)) {
			$this->parameters[] = $parameterName;
		}
	}

	function & getAvailableSearchEngines($format = SEARCH__OUTPUT_HTML) {
		$availableSearchEngines = array();
		if(isset($this->searchEngines[$format])) {
			$searchEngines = $this->searchEngines[$format];
			foreach($this->searchEngines[$format] AS $type => $searchEngine) {
				if($searchEngine->isAvailable($this->parametersValues)) {
					$availableSearchEngines[] = $searchEngine;
				}
			}
		}
		return $availableSearchEngines;
	}

	function getSearchRenderer($typeOfSearch, $words, $offset, $exact, $format = SEARCH__OUTPUT_HTML) {
		if(isset($this->searchEngines[$format]) && isset($this->searchEngines[$format][$typeOfSearch])) {
			$searchEngine =& $this->searchEngines[$format][$typeOfSearch];
			if($searchEngine->isAvailable($this->parametersValues)) {
				return $searchEngine->getSearchRenderer($words, $offset, $exact, $this->parametersValues);
			}
		}
		return false;
	}

	function loadSearchEngines() {
		// Specific search engines
		$this->addSearchEngine(
			SEARCH__TYPE_IS_ARTIFACT,
			new ArtifactSearchEngine()
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_FORUM,
			new ForumSearchEngine()
		);

		// Project search engines
		$this->addSearchEngine(
			SEARCH__TYPE_IS_FULL_PROJECT,
			new GroupSearchEngine(SEARCH__TYPE_IS_FULL_PROJECT, 'FullProjectHtmlSearchRenderer', _('Search the entire project'))
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_TRACKERS,
			new TrackersGroupSearchEngine()
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_FORUMS,
			new ForumsGroupSearchEngine()
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_TASKS,
			new TasksGroupSearchEngine()
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_FRS,
			new FrsGroupSearchEngine()
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_DOCS,
			new DocsGroupSearchEngine()
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_NEWS,
			new NewsGroupSearchEngine()
		);

		# Hook to be able to load new search engine
		plugin_hook_by_reference('group_search_engines', $this);

		// Global search engine
		$this->addSearchEngine(
			SEARCH__TYPE_IS_SOFTWARE,
			new GFSearchEngine(SEARCH__TYPE_IS_SOFTWARE, 'ProjectHtmlSearchRenderer', _('Project'))
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_PEOPLE,
			new GFSearchEngine(SEARCH__TYPE_IS_PEOPLE, 'PeopleHtmlSearchRenderer', _('People'))
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_ALLDOCS,
			new GFSearchEngine(SEARCH__TYPE_IS_ALLDOCS, 'DocsAllHtmlSearchRenderer', _('Documents'))
		);
		if (forge_get_config('use_people')) {
			$this->addSearchEngine(
				SEARCH__TYPE_IS_SKILL,
				new GFSearchEngine(SEARCH__TYPE_IS_SKILL, 'SkillHtmlSearchRenderer', _('Skill'))
			);
		}

		// Rss search engines
		$this->addSearchEngine(
			SEARCH__TYPE_IS_SOFTWARE,
			new GFSearchEngine(SEARCH__TYPE_IS_SOFTWARE, 'ProjectRssSearchRenderer', _('Project')),
			SEARCH__OUTPUT_RSS
		);

		plugin_hook('search_engines', array('object' => $this));
	}

	function loadParameters() {
		$this->parameters = array(
			SEARCH__PARAMETER_GROUP_ID,
			SEARCH__PARAMETER_ARTIFACT_ID,
			SEARCH__PARAMETER_FORUM_ID,
			SEARCH__PARAMETER_GROUP_PROJECT_ID
		);
	}


}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
