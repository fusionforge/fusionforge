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

require_once('www/search/include/renderers/SearchRenderer.class.php');

class RssSearchRenderer extends SearchRenderer {
	
	/**
	 * callback function name used during the RSS export
	 *
	 * @var string $callbackFunction
	 */
	var $callbackFunction = '';

	/**
	 * Constructor
	 *
	 * @param string $typeOfSearch type of the search (Software, Forum, People and so on)
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param object $searchQuery SearchQuery instance
	 */
	function RssSearchRenderer($typeOfSearch, $words, $isExact, $searchQuery) {
		$this->SearchRenderer($typeOfSearch, $words, $isExact, $searchQuery);
	}

	/**
	 * flush - flush the RSS output
	 */
	function flush() {
		$searchQuery =& $this->searchQuery;

		header('Content-Type: text/plain');
		
		if($searchQuery->isError() || $this->isError()) {
			echo '<channel></channel>';
		} else {		
			$searchQuery->executeQuery();
			include_once('www/export/rss_utils.inc');
	
			rss_dump_project_result_set(
				$searchQuery->getResult(),
				'GForge Search Results',
				'GForge Search Results for "'.$this->query['words'].'"',
				$this->callbackFunction
			);
		}
		exit();
	}

}

?>
