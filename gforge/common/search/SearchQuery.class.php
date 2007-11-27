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

class SearchQuery extends Error {
	/**
	 * the operator between each part of the query. Can be AND or OR.
	 *
	 * @var string $operator
	 */
	var $operator;	
	/**
	 * Number of rows per page
	 *
	 * @var int $rowsPerPage
	 */
	var $rowsPerPage;
	/**
	 * Number of rows we will display on the page
	 *
	 * @var int $rowsCount
	 */
	var $rowsCount = 0;
	/**
	 * Number of rows returned by the query
	 *
	 * @var int $rowsTotalCount
	 */
	var $rowsTotalCount = 0;
	/**
	 * Offset
	 *
	 * @var int $offset
	 */
	var $offset = 0;
	/**
	 * Result handle
	 *
	 * @var resource $result
	 */
	var $result;
	/**
	 * When search by id is enabled, the id to search for
	 *
	 * @var int $searchId
	 */
	var $searchId = false;
	/**
	 * if we want to search for all the words or if only one is sufficient
	 *
	 * @var boolean $isExact
	 */
	 var $isExact = false;
	/**
	 * sections to search in
	 *
	 * @var array $sections
	 */	
	var $sections = SEARCH__ALL_SECTIONS;

	var $words;

	var $phrases;

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one is sufficient
	 * @param int $rowsPerPage number of rows per page
	 */
	function SearchQuery($words, $offset, $isExact, $rowsPerPage = SEARCH__DEFAULT_ROWS_PER_PAGE) {
		$this->cleanSearchWords($words);
		
		$this->rowsPerPage = $rowsPerPage;
		$this->offset = $offset;
		$this->isExact = $isExact;
		$this->operator = $this->getOperator();
	}
	
	/**
	 * cleanSearchWords - clean the words we are searching for
	 *
	 * @param string $words words we are searching for
	 */
	function cleanSearchWords($words) {
		$words = trim($words);
		if(!$words) {
			$this->setError(_('Error: criteria not specified'));
			return;
		}
		if(is_numeric($words) && $this->implementsSearchById()) {
			$this->searchId = (int) $words;
		} else {
			$words = htmlspecialchars($words);
			$words = strtr($words, array('%' => '', '_' => ''));
			$words = preg_replace("/[ \t]+/", ' ', $words);
			if(strlen($words) < 3) {
				$this->setError(_('Error: search query too short'));
				return;
			}
			$this->words = array();
			$this->phrases = array();
			$phrase = '';
			$inQuote = false;
			foreach(explode(' ', quotemeta($words)) as $word) {
				if($inQuote) {
					if(substr($word, -3) == "\\\\'") {
						$word = substr($word, 0, -3);
						$inQuote = false;
						$phrase .= ' '.$word;
						$this->phrases[] = $phrase;
					} else {
						$phrase .= ' '.$word;
					}
				} else {
					if(substr($word, 0, 3) == "\\\\'") {
						$word = substr($word, 3);
						$inQuote = true;
						if(substr($word, -3) == "\\\\'") {
							// This is a special case where the phrase is just one word
							$word = substr($word, 0, -3);
							$inQuote = false;
							$this->phrases[] = $word;
						} else {
							$phrase = $word;
						}
					} else {
						$this->words[] = $word;
					}
				}

			}
		}
	}
	
	/**
	 * executeQuery - execute the SQL query to get the results
	 */ 
	function executeQuery() {
		global $sys_use_fti;
		if($this->searchId) {
			$query = $this->getSearchByIdQuery();
		} else {
			$query = $this->getQuery();
		}

		if ($sys_use_fti) {
			db_query("select set_curcfg('default')");
		}
		$this->result = db_query(
			$query,
			$this->rowsPerPage + 1,
			$this->offset,
			SYS_DB_SEARCH
		);

		$this->rowsTotalCount = db_numrows($this->result);
		$this->rowsCount = min($this->rowsPerPage, $this->rowsTotalCount);
	}
	
	/**
	 * getQuery - returns the sql query built to get the search results
	 * This is an abstract method. It _MUST_ be implemented in children classes.
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		return;
	}

	/**
	 * getIlikeCondition - build the ILIKE condition of the SQL query for a given field name
	 *
	 * @param string $fieldName name of the field in the ILIKE condition
	 * @return string the condition
	 */
	function getIlikeCondition($fieldName) {
		global $sys_database_type;

		$wordArgs = array_merge($this->words, str_replace(' ', "\\\s+",$this->phrases));
		if ( $sys_database_type == "mysql" ) {
			return $fieldName." LIKE '%" . implode("%' ".$this->operator." ".$fieldName." ILIKE '%", $wordArgs) ."%'";
		} else {
			return $fieldName." ILIKE '%" . implode("%' ".$this->operator." ".$fieldName." ILIKE '%", $wordArgs) ."%'";
		}
	}

	function getMatchCond($fieldName, $arr) {
		if(!count($arr)) {
			$result = 'TRUE';
		} else {
			$regexs = str_replace(' ', "\\\s+",$arr);
			$result = $fieldName." ~* '" . implode("' ".$this->operator." ".$fieldName." ~* '", $regexs) ."'";
		}
		return $result;
	}
	
	/**
	 * getOperator - get the operator we have to use in ILIKE condition
	 *
	 * @return string AND if it is an exact search, OR otherwise
	 */
	function getOperator() {
		if($this->isExact) {
			return 'AND';
		} else {
			return 'OR';
		}
	}
	
	/**
	 * implementsSearchById - check if the current object implements the search by id feature by having a getSearchByIdQuery method
	 *
	 * @return boolean true if our object implements search by id, false otherwise.
	 */
	function implementsSearchById() {
		return method_exists($this, 'getSearchByIdQuery');
	}
	
	/**
	 * getResult - returns the result set
	 *
	 * @return resource result set
	 */
	function & getResult() {
		return $this->result;
	}
	
	/**
	 * getRowsCount - returns number of rows for the current page
	 *
	 * @return int rows count for the current page
	 */
	function getRowsCount() {
		return $this->rowsCount;
	}
	
	/**
	 * getRowsTotalCount - returns total number of rows
	 *
	 * @return int rows count
	 */
	function getRowsTotalCount() {
		return $this->rowsTotalCount;
	}
	
	/**
	 * getOffset - returns the offset
	 *
	 * @return int offset
	 */
	function getOffset() {
		return $this->offset;
	}
	
	/**
	 * getRowsPerPage - returns number of rows per page
	 *
	 * @return int number of rows per page
	 */
	function getRowsPerPage() {
		return $this->rowsPerPage;
	}
	
	/**
	 * getWords - returns the array containing words we are searching for
	 *
	 * @return array words we are searching for
	 */
	function getWords() {
		return $this->words;
	}
	
	/**
	 * setSections - set the sections list
	 *
	 * @param $sections mixed array of sections or SEARCH__ALL_SECTIONS
	 */
	function setSections($sections) {
		if(is_array($sections)) {
			//make a comma separated string from the sections array
			foreach($sections as $key => $section) 
				$sections[$key] = '\''.$section.'\'';
			$this->sections = implode(', ', $sections);
		} else {
			$this->sections = $sections;
		}
	}

	/**
	 * getFormattedWords - get words formatted in order to be used in the FTI stored procedures
	 *
	 * @return string words we are searching for, separated by a pipe
	 */	
	function getFormattedWords() {
		if ($this->isExact) {
			$words = implode('&', $this->words);
		} else {
			$words = implode('|', $this->words);
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
