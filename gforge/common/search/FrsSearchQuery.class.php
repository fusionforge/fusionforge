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

class FrsSearchQuery extends SearchQuery {
	
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
	 */
	function FrsSearchQuery($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {	
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
			if(count($this->words)) {
				$tsquery0 = "headline(frs_package.name, q) AS package_name, headline(frs_release.name, q) as release_name";
				$tsquery = ", to_tsquery('".$this->getFormattedWords()."') AS q, frs_release_idx r, frs_file_idx f";
				$tsmatch = "(f.vectors @@ q OR r.vectors @@ q)";
				$rankCol = "";
				$tsjoin = 'AND r.release_id = frs_release.release_id AND f.file_id = frs_file.file_id';
				$orderBy = "ORDER BY frs_package.name, frs_release.name";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "frs_package.name as package_name, frs_release.name as release_name";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "ORDER BY frs_package.name, frs_release.name";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$phraseCond .= $phraseOp.'(('.$this->getMatchCond('frs_release.changes', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('frs_release.notes', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('frs_release.name', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('frs_file.filename', $this->phrases).'))';
			}
			$sql = 'SELECT '.$tsquery0.', frs_release.release_date, frs_release.release_id, users.realname'
				. ' FROM frs_file, frs_release, users, frs_package'.$tsquery
				. ' WHERE frs_release.released_by = users.user_id'
				. $tsjoin
				. ' AND frs_package.package_id = frs_release.package_id'
				. ' AND frs_file.release_id=frs_release.release_id'
				. ' AND frs_package.group_id='.$this->groupId;
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sections = $this->sections;
				$sql .= ' AND frs_package.package_id IN ('.$this->sections.') ';
			}
			if(!$this->showNonPublic) {
				$sql .= ' AND is_public=1';
			}

			$sql .= ' AND (  '.$tsmatch.' '.$phraseCond.') '.$orderBy;
		} else {
			$sql = 'SELECT frs_package.name as package_name, frs_release.name as release_name, frs_release.release_date, frs_release.release_id, users.realname'
				. ' FROM frs_file, frs_release, users, frs_package'
				. ' WHERE frs_release.released_by = users.user_id'
				. ' AND frs_package.package_id = frs_release.package_id'
				. ' AND frs_file.release_id=frs_release.release_id'
				. ' AND frs_package.group_id='.$this->groupId;
			
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sql .= ' AND frs_package.package_id IN ('.$this->sections.') ';
			}
			if(!$this->showNonPublic) {
				$sql .= ' AND is_public=1';
			}
	
			$sql .= ' AND (('.$this->getIlikeCondition('frs_release.changes', $this->words).')' 
				. ' OR ('.$this->getIlikeCondition('frs_release.notes', $this->words).')'
				. ' OR ('.$this->getIlikeCondition('frs_release.name', $this->words).')'
				. ' OR ('.$this->getIlikeCondition('frs_file.filename', $this->words).'))'
				. ' ORDER BY frs_package.name, frs_release.name';
		}
		return $sql;
	}
	
	/**
	 * getSections - returns the list of available forums
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	function getSections($groupId, $showNonPublic) {
		$sql = 'SELECT package_id, name FROM frs_package WHERE group_id = \''.$groupId.'\'';
		
		if(!$showNonPublic) {
			$sql .= ' AND is_public=1';
		}
		$sql .= ' ORDER BY name';
		
		$sections = array();
		$res = db_query($sql);
		while($data = db_fetch_array($res)) {
			$sections[$data['package_id']] = $data['name'];
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
