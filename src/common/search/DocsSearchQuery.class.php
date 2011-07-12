<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
 * Copyright 2009, Roland Mas
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
	* @var int $groupId
	*/
	var $groupId;

	/**
	* flag if non public items are returned
	*
	* @var boolean $showNonPublic
	*/
	var $showNonPublic;

	/**
	 * Constructor
	 *
	 * @param	string	$words words we are searching for
	 * @param	int	$offset offset
	 * @param	boolean	$isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param	int	$groupId group id
	 * @param	array	$sections sections to search in
	 * @param	boolean	$showNonPublic flag if private sections are searched too
	 */
	function DocsSearchQuery($words, $offset, $isExact, $groupId, $sections = SEARCH__ALL_SECTIONS, $showNonPublic = false) {
		$this->groupId = $groupId;
		$this->showNonPublic = $showNonPublic;

		$this->SearchQuery($words, $offset, $isExact);

		$this->setSections($sections);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return	array	query+params array
	 */
	function getQuery() {
		if (forge_get_config('use_fti')) {
			return $this->getFTIQuery();
		} else {
			$qpa = db_construct_qpa();
			$qpa = db_construct_qpa($qpa,
						 'SELECT doc_data.docid, doc_data.title, doc_data.description, doc_data.filename, doc_groups.groupname FROM doc_data, doc_groups WHERE doc_data.doc_group = doc_groups.doc_group AND doc_data.group_id = $1',
						 array ($this->groupId));
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa($qpa, 'AND doc_groups.doc_group = ANY ($1) ',
							 db_int_array_to_any_clause($this->sections));
			}
			if ($this->showNonPublic) {
				$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid IN (1, 4, 5)');
			} else {
				$qpa = db_construct_qpa($qpa, ' AND doc_data.stateid = 1');
			}
			$qpa = db_construct_qpa($qpa, ' AND ((');
			$qpa = $this->addIlikeCondition($qpa, 'title');
			$qpa = db_construct_qpa($qpa, ') OR (');
			$qpa = $this->addIlikeCondition($qpa, 'description');
			$qpa = db_construct_qpa($qpa, ') OR (');
			$qpa = $this->addIlikeCondition($qpa, 'data_words', $this->words);
			$qpa = db_construct_qpa($qpa, ')) ORDER BY doc_groups.groupname, doc_data.docid');
		}
		return $qpa;
	}

	function getFTIQuery() {
		$words = $this->getFormattedWords();
		$group_id=$this->groupId;

		$qpa = db_construct_qpa () ;
		if(count($this->words)) {
			$qpa = db_construct_qpa($qpa,
						 'SELECT doc_data.docid, doc_data.filename, headline(doc_data.title, q) AS title, headline(doc_data.description, q) AS description doc_groups.groupname FROM doc_data, doc_groups, doc_data_idx, to_tsquery($1) q',
						 array (implode (' ', $words)));
			$qpa = db_construct_qpa($qpa,
						 ' WHERE doc_data.doc_group = doc_groups.doc_group AND doc_data.docid = doc_data_idx.docid AND (vectors @@ q') ;
			if (count($this->phrases)) {
				$qpa = db_construct_qpa($qpa, $this->getOperator());
				$qpa = db_construct_qpa($qpa, '(');
				$qpa = $this->addMatchCondition($qpa, 'title');
				$qpa = db_construct_qpa($qpa, ') OR (');
				$qpa = $this->addMatchCondition($qpa, 'description');
				$qpa = db_construct_qpa($qpa, ') OR (');
				$qpa = $this->addIlikeCondition($qpa, 'data_words', $this->words);
				$qpa = db_construct_qpa($qpa, ')');
			}
			$qpa = db_construct_qpa($qpa,
						 ') AND doc_data.group_id = $1',
						 array ($group_id)) ;
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa($qpa,
							 ' AND doc_groups.doc_group = ANY ($1)',
							 db_int_array_to_any_clause ($this->sections));
			}
			if ($this->showNonPublic) {
				$qpa = db_construct_qpa($qpa,
							 ' AND doc_data.stateid IN (1, 4, 5)');
			} else {
				$qpa = db_construct_qpa($qpa,
							 ' AND doc_data.stateid = 1');
			}
			$qpa = db_construct_qpa($qpa,
						 ' ORDER BY rank(vectors, q) DESC, groupname ASC');
		} else {
			$qpa = db_construct_qpa($qpa,
						 'SELECT doc_data.docid, filename, title, description doc_groups.groupname FROM doc_data, doc_groups');
			$qpa = db_construct_qpa($qpa,
						 'WHERE doc_data.doc_group = doc_groups.doc_group');
			if (count($this->phrases)) {
				$qpa = db_construct_qpa($qpa,
							 $this->getOperator()) ;
				$qpa = db_construct_qpa($qpa,
							 '(');
				$qpa = $this->addMatchCondition($qpa, 'title');
				$qpa = db_construct_qpa($qpa,
							 ') OR (');
				$qpa = $this->addMatchCondition($qpa, 'description');
				$qpa = db_construct_qpa($qpa,
							 ')');
			}
			$qpa = db_construct_qpa($qpa,
						 ') AND doc_data.group_id = $1',
						 array($group_id));
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa($qpa,
							 'AND doc_groups.doc_group = ANY ($1) ',
							 db_int_array_to_any_clause($this->sections));
			}
			if ($this->showNonPublic) {
				$qpa = db_construct_qpa($qpa,
							 ' AND doc_data.stateid IN (1, 4, 5)');
			} else {
				$qpa = db_construct_qpa($qpa,
							 ' AND doc_data.stateid = 1');
			}
			$qpa = db_construct_qpa($qpa,
						 ' ORDER BY groupname');
		}
		return $qpa ;
	}

	/**
	 * getSections - returns the list of available doc groups
	 *
	 * @param	$groupId	int group id
	 * @param	$showNonPublic	boolean if we should consider non public sections
	 */
	static function getSections($groupId, $showNonPublic = false) {
		$sql = 'SELECT doc_groups.doc_group, doc_groups.groupname FROM doc_groups, doc_data'
			.' WHERE doc_groups.doc_group = doc_data.doc_group AND doc_groups.group_id = $1';
		if ($showNonPublic) {
			$sql .= ' AND doc_data.stateid IN (1, 4, 5) AND doc_groups.stateid = 1';
		} else {
			$sql .= ' AND doc_data.stateid = 1  AND doc_groups.stateid = 1';
		}
		$sql .= ' ORDER BY groupname';

		$sections = array();
		$res = db_query_params($sql,
					array($groupId));
		while($data = db_fetch_array($res)) {
			$sections[$data['doc_group']] = $data['groupname'];
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
