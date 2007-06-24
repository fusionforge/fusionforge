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

require_once('common/search/SearchQuery.class.php');

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
?>
