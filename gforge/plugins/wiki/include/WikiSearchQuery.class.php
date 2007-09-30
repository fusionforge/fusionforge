<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * http://gforge.org
 *
 * @version $Id: NewsSearchQuery.class,v 1.2 2005/01/28 20:36:44 ruben Exp $
 */

require_once('common/search/SearchQuery.class.php');

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
		$sql = "SELECT plugin_wiki_page.id AS id, " .
					"substring(plugin_wiki_page.pagename from $len) AS pagename, " .
					"plugin_wiki_page.hits AS hits, " .
					"plugin_wiki_page.pagedata as pagedata, " .
					"plugin_wiki_version.version AS version, " .
					"plugin_wiki_version.mtime AS mtime, " .
					"plugin_wiki_version.minor_edit AS minor_edit, " .
					"plugin_wiki_version.content AS content, " .
					"plugin_wiki_version.versiondata AS versiondata " .
				"FROM plugin_wiki_nonempty, plugin_wiki_page, plugin_wiki_recent, " .
					"plugin_wiki_version " .
				"WHERE plugin_wiki_nonempty.id=plugin_wiki_page.id " .
					"AND plugin_wiki_page.id=plugin_wiki_recent.id " .
					"AND plugin_wiki_page.id=plugin_wiki_version.id " .
					"AND latestversion=version " .
					"AND substring(plugin_wiki_page.pagename from 0 for $len) = '$pat' " .
					"AND ((".$this->getIlikeCondition('pagename', $this->words).") " .
					"OR (".$this->getIlikeCondition('content', $this->words)."))";
//print "SQL: $sql\n";
		return $sql;
	}
}

?>
