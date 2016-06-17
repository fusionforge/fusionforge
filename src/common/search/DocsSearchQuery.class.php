<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
 * Copyright 2009, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, French Ministry of National Education
 * Copyright 2013,2015-2016, Franck Villaume - TrivialDev
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

require_once $gfcommon.'search/SearchQuery.class.php';

class DocsSearchQuery extends SearchQuery {

	/**
	* group id
	*
	* @var array $groupIdArr
	*/
	var $groupIdArr;

	/**
	* flag if non public items are returned
	*
	* @var boolean $showNonPublic
	*/
	var $showNonPublic;

	/**
	 * @param	string	$words		words we are searching for
	 * @param	int	$offset		offset
	 * @param	bool	$isExact	if we want to search for all the words or if only one matching the query is sufficient
	 * @param	array	$groupIdArr	array containing group ids
	 * @param	string	$sections	sections to search in
	 * @param	bool	$showNonPublic	flag if private sections are searched too
	 */
	function __construct($words, $offset, $isExact, $groupIdArr, $sections = SEARCH__ALL_SECTIONS, $showNonPublic = false, $rowsPerPage = SEARCH__DEFAULT_ROWS_PER_PAGE, $options = array()) {

		$this->groupIdArr = $groupIdArr;
		$this->showNonPublic = $showNonPublic;
		parent::__construct($words, $offset, $isExact, $rowsPerPage, $options);
		$this->setSections($sections);
	}

	/**
	 * addCommonQPA - add common sql commands to existing QPA
	 *
	 * @return	array	query+params array
	 */
	function addCommonQPA($qpa) {
		$options = $this->options;
		$sections = $this->sections;
		$params['groupIdArr'] = $this->groupIdArr;
		$params['options'] = $options;
		plugin_hook_by_reference('docmansearch_has_hierarchy', $params);
		if (count($params['groupIdArr'])) {
			$qpa = db_construct_qpa($qpa, ' AND docdata_vw.group_id = ANY ($1) ', array(db_int_array_to_any_clause($params['groupIdArr'])));
		}
		if ($sections != SEARCH__ALL_SECTIONS) {
			$qpa = db_construct_qpa($qpa, ' AND doc_groups.doc_group = ANY ($1)', array(db_int_array_to_any_clause($sections)));
		}
		if ($this->showNonPublic) {
			$qpa = db_construct_qpa($qpa, ' AND docdata_vw.stateid IN (1, 3, 4, 5)');
		} else {
			$qpa = db_construct_qpa($qpa, ' AND docdata_vw.stateid = 1 AND doc_groups.stateid = 1');
		}

		if (isset($options['date_begin']) && !isset($options['date_end'])) {
			$qpa = db_construct_qpa($qpa, ' AND docdata_vw.createdate >= $1', array($options['date_begin']));
		} elseif (!isset($options['date_begin']) && isset($options['date_end'])) {
			$qpa = db_construct_qpa($qpa, ' AND docdata_vw.createdate <= $1', array($options['date_end']));
		} elseif (isset($options['date_begin']) && isset($options['date_end'])) {
			$qpa = db_construct_qpa($qpa, ' AND docdata_vw.createdate between $1 and $2', array($options['date_begin'], $options['date_end']));
		}
		return $qpa;
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return	array	query+params array
	 */
	function getQuery() {
		$words = $this->getFTIwords();
		$options = $this->options;
		if (!isset($options['insideDocuments']) || !$options['insideDocuments']) {
			$qpa = db_construct_qpa(false, 'SELECT x.* FROM (SELECT docdata_vw.docid, docdata_vw.group_id AS group_id, docdata_vw.filename, ts_headline(docdata_vw.title, q) AS title, ts_headline(docdata_vw.description, q) AS description, doc_groups.groupname, docdata_vw.title||$1||description AS full_string_agg, doc_data_idx.vectors, groups.group_name as project_name FROM groups, docdata_vw, doc_groups, doc_data_idx, to_tsquery($2) AS q WHERE docdata_vw.doc_group = doc_groups.doc_group AND docdata_vw.group_id = groups.group_id AND docdata_vw.docid = doc_data_idx.docid AND (vectors @@ to_tsquery($2))',
					array ($this->field_separator, $words));
		} else {
			$qpa = db_construct_qpa(false, 'SELECT x.* FROM (SELECT docdata_vw.docid, docdata_vw.group_id AS group_id, ts_headline(docdata_vw.filename, q) AS filename, ts_headline(docdata_vw.title, q) AS title, ts_headline(docdata_vw.description, q) AS description, doc_groups.groupname, docdata_vw.title||$1||description||$1||filename AS full_string_agg, doc_data_words_idx.vectors, groups.group_name as project_name FROM groups, docdata_vw, doc_groups, doc_data_words_idx, to_tsquery($2) AS q WHERE docdata_vw.doc_group = doc_groups.doc_group AND docdata_vw.group_id = groups.group_id AND docdata_vw.docid = doc_data_words_idx.docid AND (vectors @@ to_tsquery($2))',
					array ($this->field_separator, $words));
		}
		$qpa = $this->addCommonQPA($qpa);
		$qpa = db_construct_qpa($qpa, ') AS x ') ;
		if (count($this->phrases)) {
			$qpa = db_construct_qpa($qpa, 'WHERE ') ;
			$qpa = $this->addMatchCondition($qpa, 'full_string_agg');
		}
		$qpa = db_construct_qpa($qpa, ' ORDER BY ts_rank(vectors, to_tsquery($1)) DESC, group_id ASC, groupname ASC, title ASC',
					 array($words));

		return $qpa;
	}

	/**
	 * getSections - returns the list of available doc groups
	 *
	 * @param	$groupId	int group id
	 * @param	$showNonPublic	boolean if we should consider non public sections
	 * @return	array
	 */
	static function getSections($groupId, $showNonPublic = false) {
		if (!forge_check_perm('docman',$groupId,'read')) {
			return array();
		}

		$sql = 'SELECT doc_groups.doc_group, doc_groups.groupname FROM doc_groups, doc_data'
			.' WHERE doc_groups.doc_group = doc_data.doc_group AND doc_groups.group_id = $1';
		if ($showNonPublic) {
			$sql .= ' AND doc_data.stateid IN (1, 3, 4, 5)';
		} else {
			$sql .= ' AND doc_data.stateid = 1  AND doc_groups.stateid = 1';
		}
		$sql .= ' ORDER BY groupname';
		$sections = array();
		$res = db_query_params($sql, array($groupId));
		while ($data = db_fetch_array($res)) {
			$sections[$data['doc_group']] = $data['groupname'];
		}
		return $sections;
	}

	function isRowVisible($row) {
		return forge_check_perm ('docman', $row['group_id'], 'read');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
