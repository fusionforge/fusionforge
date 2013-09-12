<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, French Minitry of National Education
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'search/SearchQuery.class.php';

class DocsAllSearchQuery extends SearchQuery {

   /**
	* array flags if non public items are returned
	*
	* @var $parametersValues
	*/
	var $parametersValues;

	/**
	 * Constructor
	 *
	 * @param    string $words words we are searching for
	 * @param    int $offset offset
	 * @param    bool $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int|string $sections sections to search in
	 * @param    array $parametersValues
	 * @param    bool $showNonPublic flag if private sections are searched too
	 */
	function __construct($words, $offset, $isExact = true, $sections=SEARCH__ALL_SECTIONS, $parametersValues, $showNonPublic = false) {
		$this->parametersValues = $parametersValues;
		$this->showNonPublic = $showNonPublic;
		parent::__construct($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa,
					'SELECT doc_data.group_id, groups.group_name as project_name,doc_groups.groupname,
					doc_data.docid, doc_data.title, doc_data.description, doc_data.filetype,
					doc_data.filename, doc_groups.groupname
					FROM doc_data, doc_groups, groups
					WHERE doc_data.doc_group = doc_groups.doc_group
					AND doc_data.group_id = groups.group_id',
					array());
		if ($this->sections != SEARCH__ALL_SECTIONS) {
			$qpa = db_construct_qpa($qpa,'AND doc_groups.doc_group = ANY ($1) ',
						db_int_array_to_any_clause($this->sections));
		}
		if ($this->showNonPublic) {
			$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid IN (1, 4, 5)');
		} else {
			$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid = 1');
		}

		$qpa = db_construct_qpa($qpa, ' AND ((');
		$qpa = $this->addIlikeCondition($qpa, 'title', $this->words);
		$qpa = db_construct_qpa($qpa, ') OR (');
		$qpa = $this->addIlikeCondition($qpa, 'description', $this->words);
		$qpa = db_construct_qpa($qpa, ') OR (');
		$qpa = $this->addIlikeCondition($qpa, 'data_words', $this->words);
		$qpa = db_construct_qpa ($qpa, ')) ORDER BY groups.group_name, doc_groups.groupname, doc_data.title') ;

		return $qpa;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
