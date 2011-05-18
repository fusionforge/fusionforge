<?php
/**
 * Search Engine
 *
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

class SearchRenderer extends Error {
	
	/**
	 * This is not the SQL query but elements from the HTTP query
	 *
	 * @var array $query
	 */
	var $query = array();

	/**
	 * This is the searchQuery. It's a SearchQuery instance.
	 *
	 * @var object $searchQuery
	 */
	var $searchQuery;

	/**
	 * Constructor
	 *
	 * @param string $typeOfSearch type of search
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function SearchRenderer($typeOfSearch, $words, $isExact, $searchQuery) {
		$this->query['typeOfSearch'] = $typeOfSearch;
		$this->query['isExact'] = $isExact;
		$this->query['words'] = $words;
		
		$this->searchQuery = $searchQuery;
	}

	/**
	 * flush - flush the output
	 * This is an abstract method. It _MUST_ be implemented in children classes.
	 */
	function flush() {}
	
}

?>
