<?php
/**
 * WikiPlugin Class
 * Wiki Search Engine for Fusionforge
 *
 * Copyright 2004 (c) Dominik Haas, Gforge Team
 * Copyright 2006 (c) Alain Peyrat
 *
 * This file is part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'search/SearchQuery.class.php';

class WikiSearchQuery extends SearchQuery {

	/**
	* group id
	*
	* @var int $groupId
	*/
	var $groupId;

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 */
	function WikiSearchQuery($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;

		$this->SearchQuery($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {

		$pat = '_g'.$this->groupId.'_';
		$len = strlen($pat)+1;
		$words = addslashes(join('&', $this->words));
		$sql = "SELECT plugin_wiki_page.id AS id,
					substring(plugin_wiki_page.pagename from $len) AS pagename,
					plugin_wiki_page.hits AS hits,
					plugin_wiki_page.pagedata as pagedata,
					plugin_wiki_version.version AS version,
					plugin_wiki_version.mtime AS mtime,
					plugin_wiki_version.minor_edit AS minor_edit,
					plugin_wiki_version.content AS content,
					plugin_wiki_version.versiondata AS versiondata
				FROM plugin_wiki_nonempty, plugin_wiki_page, plugin_wiki_recent,
					plugin_wiki_version
				WHERE plugin_wiki_nonempty.id=plugin_wiki_page.id
					AND plugin_wiki_page.id=plugin_wiki_recent.id
					AND plugin_wiki_page.id=plugin_wiki_version.id
					AND latestversion=version
					AND substring(plugin_wiki_page.pagename from 0 for $len) = '$pat'
					AND (idxFTI @@ to_tsquery('$words'))
				ORDER BY ts_rank(idxFTI, to_tsquery('$words')) DESC";
		return $sql;
	}
}

?>
