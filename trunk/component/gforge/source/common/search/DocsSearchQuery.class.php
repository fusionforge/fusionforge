<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
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
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
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
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param array $sections sections to search in
	 * @param boolean $showNonPublic flag if private sections are searched too
	 */
	function DocsSearchQuery($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {	
		$this->groupId = $groupId;
		$this->showNonPublic = $showNonPublic;
		
		$this->SearchQuery($words, $offset, $isExact);
		
		$this->setSections($sections);
	}

	/**
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_use_fti;
		if ($sys_use_fti) {
			return $this->getFTIQuery();
		} else {
			$sql = 'SELECT doc_data.docid, doc_data.title, doc_data.description, doc_groups.groupname'
				.' FROM doc_data, doc_groups'
				.' WHERE doc_data.doc_group = doc_groups.doc_group'
				.' AND doc_data.group_id ='.$this->groupId;
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sql .= ' AND doc_groups.doc_group IN ('.$this->sections.') ';
			}
			if ($this->showNonPublic) {
				$sql .= ' AND doc_data.stateid IN (1, 4, 5)';
			} else {
				$sql .= ' AND doc_data.stateid = 1';
			}
			$sql .= ' AND (('.$this->getIlikeCondition('title', $this->words).')' 
				.' OR ('.$this->getIlikeCondition('description', $this->words).'))'
				.' ORDER BY doc_groups.groupname, doc_data.docid';
		}
		return $sql;
	}
	
	function getFTIQuery() {
		if ($this->showNonPublic) {
			$nonPublic = "1, 4, 5";
		} else {
			$nonPublic = "1";
		}
		if ($this->sections != SEARCH__ALL_SECTIONS) {
			$sections = "AND doc_groups.doc_group IN ($this->sections)";
		} else {
			$sections = '';
		}
		$words = $this->getFormattedWords();
		$group_id=$this->groupId;

		if(count($this->words)) {
			$tsquery0 = "headline(doc_data.title, q) AS title, headline(doc_data.description, q) AS description";
			$tsquery = ", doc_data_idx, to_tsquery('".$words()."') q";
			$tsmatch = "vectors @@ q";
			$rankCol = "";
			$tsjoin = 'AND doc_data.docid = doc_data_idx.docid  ';
			$orderBy = "ORDER BY rank(vectors, q) DESC, groupname ASC";
			$phraseOp = $this->getOperator();
		} else {
			$tsquery0 = "title, description";
			$tsquery = "";
			$tsmatch = "";
			$tsjoin = "";
			$rankCol = "";
			$orderBy = "ORDER BY groupname";
			$phraseOp = "";
		}

		$phraseCond = '';
		if(count($this->phrases)) {
			$titleCond = $this->getMatchCond('title', $this->phrases);
			$descCond = $this->getMatchCond('description', $this->phrases);
			$phraseCond = $phraseOp.' (('.$titleCond.') OR ('.$descCond.'))';
		}
		
		$sql="SELECT doc_data.docid, $tsquery0, doc_groups.groupname
			FROM doc_data, doc_groups $tsquery
			WHERE doc_data.doc_group = doc_groups.doc_group
			$tsjoin AND ($tsmatch $phraseCond )
			AND doc_data.group_id = '$group_id'
			$sections
			AND doc_data.stateid IN ($nonPublic)
			$orderBy";
		return $sql;
	}

	/**
	 * getSections - returns the list of available doc groups
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	function getSections($groupId, $showNonPublic=false) {
		$sql = 'SELECT doc_groups.doc_group, doc_groups.groupname FROM doc_groups, doc_data'
			.' WHERE doc_groups.doc_group = doc_data.doc_group AND doc_groups.group_id = '.$groupId;
		if ($showNonPublic) {
			$sql .= ' AND doc_data.stateid IN (1, 4, 5)';
		} else {
			$sql .= ' AND doc_data.stateid = 1';
		}
		$sql .= ' ORDER BY groupname';
		
		$sections = array();
		$res = db_query($sql);
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
