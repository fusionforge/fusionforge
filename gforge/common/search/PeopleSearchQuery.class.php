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

class PeopleSearchQuery extends SearchQuery {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function PeopleSearchQuery($words, $offset, $isExact) {	
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
				$tsquery0 = ", user_name, headline(realname, q) as realname ";
				$words = $this->getFormattedWords();
				$tsquery = ", to_tsquery('$words') AS q, users_idx ";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = 'AND users_idx.user_id = users.user_id';
				$orderBy = "ORDER BY rank(vectors, q) DESC, user_name";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = ", user_name, realname ";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "ORDER BY user_name";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$phraseCond .= $phraseOp.'('
					. ' ('.$this->getMatchCond('user_name', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('realname', $this->phrases).'))';
			}
			$sql = 'SELECT users.user_id '.$tsquery0
				. 'FROM users '.$tsquery
				. 'WHERE (status=\'A\') '
				. $tsjoin
				. " AND ($tsmatch $phraseCond) "
				. $orderBy;
		} else {
			$sql = 'SELECT user_name,user_id,realname ' 
				. 'FROM users ' 
				. 'WHERE (('.$this->getIlikeCondition('user_name', $this->words).') ' 
				. 'OR ('.$this->getIlikeCondition('realname', $this->words).')) ' 
				. 'AND (status=\'A\') ' 
				. 'ORDER BY user_name';
		}
		return $sql;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
