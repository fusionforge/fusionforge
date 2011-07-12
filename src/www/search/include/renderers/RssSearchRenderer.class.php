<?php
/**
 * Search Engine
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet / Open Wide
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

require_once $gfwww.'search/include/renderers/SearchRenderer.class.php';

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
			include_once $GLOBALS['gfwww'].'export/rss_utils.inc';

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
