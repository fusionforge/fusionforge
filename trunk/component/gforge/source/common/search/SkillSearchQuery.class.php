<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
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

class SkillSearchQuery extends SearchQuery {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function SkillSearchQuery($words, $offset, $isExact) {	
		$this->SearchQuery($words, $offset, $isExact);	
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
				$words = $this->getFormattedWords();
				$tsquery0 = "headline(skills_data.title, q) as title, headline(skills_data.keywords, q) as keywords ";
				$tsquery = ", to_tsquery('$words') AS q, skills_data_idx";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = 'AND skills_data.skills_data_id = skills_data_idx.skills_data_id ';
				$orderBy = "ORDER BY rank(vectors, q) DESC, finish DESC";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "title, keywords ";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "ORDER BY finish DESC";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$phraseCond .= $phraseOp.'('
					. ' ('.$this->getMatchCond('skills_data.title', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('skills_data.keywords', $this->phrases).'))';
			}
			$sql = 'SELECT skills_data.skills_data_id, skills_data.type, '
				. 'skills_data.start, skills_data.finish, '.$tsquery0
				. 'FROM skills_data, users, skills_data_types '
				. $tsquery
				. ' WHERE (vectors @@ q '.$phraseCond.') '
				. $tsjoin
				. 'AND (skills_data.user_id=users.user_id) '
				. 'AND (skills_data.type=skills_data_types.type_id) '
				. $orderBy;
		} else {
			$sql = 'SELECT * '
				. 'FROM skills_data, users, skills_data_types '
				. 'WHERE (('.$this->getIlikeCondition('skills_data.title', $this->words).') '
				. 'OR ('.$this->getIlikeCondition('skills_data.keywords', $this->words).')) '
				. 'AND (skills_data.user_id=users.user_id) '
				. 'AND (skills_data.type=skills_data_types.type_id) '
				. 'ORDER BY finish DESC';
		}
		return $sql;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
