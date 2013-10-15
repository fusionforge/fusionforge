<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

	var $words = array();

	var $phrases = array();

	// Something that's hopefully not going to end up in real data
	var $field_separator = ' ioM0Thu6_fieldseparator_kaeph9Ee ';

	/**
	 * Constructor
	 *
	 * @param	string	$words words we are searching for
	 * @param	int	$offset offset
	 * @param	boolean	$isExact if we want to search for all the words or if only one is sufficient
	 * @param	int	$rowsPerPage number of rows per page
	 */
	function __construct($words, $offset, $isExact, $rowsPerPage = SEARCH__DEFAULT_ROWS_PER_PAGE) {
		$this->cleanSearchWords($words);
		//We manual escap because every Query in Search escap parameters
		$words = addslashes($words);
		if (is_array ($this->words)){
			$this->words = array_map ('addslashes',$this->words);
		} else {
			$this->words = array();
		}
		if (is_array ($this->phrases)){
			$this->phrases = array_map ('addslashes',$this->phrases);
		} else {
			$this->phrases = array();
		}
		$this->rowsPerPage = $rowsPerPage;
		$this->offset = $offset;
		$this->isExact = $isExact;
		$this->operator = $this->getOperator();
	}

	/**
	 * cleanSearchWords - clean the words we are searching for
	 *
	 * @param	string	$words words we are searching for
	 */
	function cleanSearchWords($words) {
		$words = trim($words);
		if(!$words) {
			$this->setError(_('Error') . _(': ') . _('Please enter a term to search for'));
			return;
		}

		$words = preg_replace("/[ \t]+/", ' ', $words);
		if(strlen($words) < 3) {
			$this->setError(_('Error: search query too short'));
			return;
		}
		$words = htmlspecialchars($words);
		$words = strtr($words, array('%' => '\%', '_' => '\_'));
		$phrase = '';
		$inQuote = false;
		foreach(explode(' ', quotemeta($words)) as $word) {
			if($inQuote) {
				if(substr($word, -1) == "'") {
					$word = substr($word, 0, -1);
					$inQuote = false;
					$phrase .= ' '.$word;
					$this->phrases[] = $phrase;
				} else {
					$phrase .= ' '.$word;
				}
			} else {
				if(substr($word, 0, 1) == "'") {
					$word = substr($word, 1);
					$inQuote = true;
					if(substr($word, -1) == "'") {
						// This is a special case where the phrase is just one word
						$word = substr($word, 0, -1);
						$inQuote = false;
						$this->words[] = $word;
					} else {
						$phrase = $word;
					}
				} else {
					$this->words[] = $word;
				}
			}
		}
	}

	/**
	 * executeQuery - execute the SQL query to get the results
	 */
	function executeQuery() {

		$this->result = db_query_qpa (
			$this->getQuery(),
			$this->rowsPerPage + 1,
			$this->offset,
			'SYS_DB_SEARCH'
		);
		$this->rowsTotalCount = db_numrows($this->result);
		$this->rowsCount = min($this->rowsPerPage, $this->rowsTotalCount);
	}

	/**
	 * getQuery - returns the query built to get the search results
	 * This is an abstract method. It _MUST_ be implemented in children classes.
	 *
	 * @return array query+params array
	 */
	function getQuery() {
		return;
	}

	function addMatchCondition($qpa, $fieldName) {

		if(!count($this->phrases)) {
			$qpa = db_construct_qpa ($qpa, 'TRUE') ;
			return $qpa;
		}

		$regexs = array();
		foreach ($this->phrases as $p) {
			$regexs[] = strtolower (preg_replace ("/\s+/", "\s+", $p));
		}

		for ($i = 0; $i < count ($regexs); $i++) {
			if ($i > 0) {
				$qpa = db_construct_qpa ($qpa,
							 $this->operator) ;
			}
			$qpa = db_construct_qpa ($qpa,
						 $fieldName.' ~* $1',
							 array ($regexs[$i])) ;
		}
		return $qpa;
	}

	function addIlikeCondition($qpa, $fieldName) {
		$wordArgs = array_map ('strtolower',
				       array_merge($this->words, $this->phrases));

		for ($i = 0; $i < count ($wordArgs); $i++) {
			if ($i > 0) {
				$qpa = db_construct_qpa ($qpa,
							 $this->operator) ;
			}
			$qpa = db_construct_qpa ($qpa,
						 'lower ('.$fieldName.') LIKE $1',
						 array ('%'.$wordArgs[$i].'%')) ;
		}
		return $qpa ;
	}

	/**
	 * getOperator - get the operator we have to use in ILIKE condition
	 *
	 * @return string AND if it is an exact search, OR otherwise
	 */
	function getOperator() {
		if($this->isExact) {
			return ' AND ';
		} else {
			return ' OR ';
		}
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
	 * getPhrases - returns the array containing phrases we are searching for
	 *
	 * @return array phrases we are searching for
	 */
	function getPhrases() {
		return $this->phrases;
	}

	/**
	 * setSections - set the sections list
	 *
	 * @param $sections mixed array of sections or SEARCH__ALL_SECTIONS
	 */
	function setSections($sections) {
		if(is_array($sections)) {
			$this->sections = array_values($sections) ;
		} else {
			$this->sections = $sections;
		}
	}

	/**
	 * getFTIwords - get words formatted in order to be used in the FTI stored procedures
	 *
	 * @return string words we are searching for, separated by
	 */
	function getFTIwords() {
		$bits = $this->words;
		foreach ($this->phrases as $p) {
			$bits[] = '('.implode ('&', explode (' ', $p)).')';
		}
		if ($this->isExact) {
			$query = implode('&', $bits);
		} else {
			$query = implode('|', $bits);
		}
		return $query;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
