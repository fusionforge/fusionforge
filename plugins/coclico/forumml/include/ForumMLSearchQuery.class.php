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
global $gfcommon;
require_once $gfcommon.'search/SearchQuery.class.php';

class ForumMLSearchQuery extends SearchQuery {
	
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
	function ForumMLSearchQuery($words, $offset, $isExact, $groupId) {	
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
		$qpa = db_construct_qpa () ;
		$qpa = db_construct_qpa ($qpa,
					  'SELECT mh.id_message, mh.value as subject, m.id_list '.
                        ' FROM plugin_forumml_message m, plugin_forumml_messageheader mh'.
                        ' WHERE mh.id_header = $1'.
                        ' AND m.id_parent = 0'.
                        ' AND m.id_message = mh.id_message AND ',
					 array (4)) ;
	$qpa=$this->addIlikeCondition($qpa, 'mh.value');	
		return $qpa ;
	}
}

?>
