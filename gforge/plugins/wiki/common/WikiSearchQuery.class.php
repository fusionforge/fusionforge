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

require_once $GLOBALS['gfcommon'].'search/SearchQuery.class.php';

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
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {
		
		$pat = '_g'.$this->groupId.'_';
		$len = strlen($pat)+1;
		$words = addslashes(join('&', $this->words));

		$qpa = db_construct_qpa () ;
		$qpa = db_construct_qpa ($qpa,
					 'SELECT plugin_wiki_page.id AS id, 
					substring(plugin_wiki_page.pagename from $1) AS pagename,
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
					AND substring(plugin_wiki_page.pagename from 0 for $1) = $2
					AND (idxFTI @@ to_tsquery($3))
				ORDER BY rank(idxFTI, to_tsquery($3)) DESC',
					 array ($len,
						$pat,
						$words)) ;
		return $qpa ;
	}
}

?>
