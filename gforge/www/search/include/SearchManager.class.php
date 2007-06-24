<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');
require_once('www/search/include/engines/DocsGroupSearchEngine.class.php');
require_once('www/search/include/engines/ForumsGroupSearchEngine.class.php');
require_once('www/search/include/engines/FrsGroupSearchEngine.class.php');
require_once('www/search/include/engines/NewsGroupSearchEngine.class.php');
require_once('www/search/include/engines/TasksGroupSearchEngine.class.php');
require_once('www/search/include/engines/TrackersGroupSearchEngine.class.php');

require_once('www/search/include/engines/ArtifactSearchEngine.class.php');
require_once('www/search/include/engines/ForumSearchEngine.class.php');

function & getSearchManager() {
	if(!isset($GLOBALS['OBJ_SEARCH_MANAGER'])) {
		$GLOBALS['OBJ_SEARCH_MANAGER'] =& new SearchManager();
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
		global $Language;
		
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
		
		// Global search engine
		$this->addSearchEngine(
			SEARCH__TYPE_IS_SOFTWARE,
			new SearchEngine(SEARCH__TYPE_IS_SOFTWARE, 'ProjectHtmlSearchRenderer', _('Software/Group'))
		);
		$this->addSearchEngine(
			SEARCH__TYPE_IS_PEOPLE,
			new SearchEngine(SEARCH__TYPE_IS_PEOPLE, 'PeopleHtmlSearchRenderer', _('People'))
		);
		if ($GLOBALS['sys_use_people']) {
			$this->addSearchEngine(
				SEARCH__TYPE_IS_SKILL,
				new SearchEngine(SEARCH__TYPE_IS_SKILL, 'SkillHtmlSearchRenderer', _('Skill'))
			);
		}
		
		// Rss search engines
		$this->addSearchEngine(
			SEARCH__TYPE_IS_SOFTWARE,
			new SearchEngine(SEARCH__TYPE_IS_SOFTWARE, 'ProjectRssSearchRenderer', _('Software/Group')),
			SEARCH__OUTPUT_RSS
		);
		
		plugin_hook_by_reference('search_engines', $this);
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
