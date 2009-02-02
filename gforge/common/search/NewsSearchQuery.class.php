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

class NewsSearchQuery extends SearchQuery {
	
	/**
	* group id
	*
	* @var int $groupId
	*/
	var $groupId;
	
	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 */
	function NewsSearchQuery($words, $offset, $isExact, $groupId) {	
		$this->groupId = $groupId;
		
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
			$group_id=$this->groupId;
			if(count($this->words)) {
				$tsquery0 = "headline(news_bytes.summary, q) as summary";
				$words = $this->getFormattedWords();
				$tsquery = ", to_tsquery('$words') AS q, news_bytes_idx";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = 'AND news_bytes_idx.id = news_bytes.id';
				$orderBy = "ORDER BY rank(vectors, q) DESC, post_date DESC";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "summary";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "ORDER BY post_date DESC";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$phraseCond .= $phraseOp.'('
					. ' ('.$this->getMatchCond('summary', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('details', $this->phrases).'))';
			}
			$sql = "SELECT $tsquery0,
				news_bytes.post_date,
				news_bytes.forum_id,
				users.realname
				FROM news_bytes, users $tsquery
				WHERE (news_bytes.group_id='$group_id' AND news_bytes.is_approved <> '4'
				$tsjoin
				AND news_bytes.submitted_by=users.user_id) AND
				($tsmatch $phraseCond)
				$orderBy";
		} else {
			$sql = 'SELECT news_bytes.summary, news_bytes.post_date, news_bytes.forum_id, users.realname'
				. ' FROM news_bytes, users'
				. ' WHERE (group_id='.$this->groupId.' AND is_approved <> \'4\' AND news_bytes.submitted_by = users.user_id' 
				. ' AND (('.$this->getIlikeCondition('summary', $this->words).')' 
				. ' OR ('.$this->getIlikeCondition('details', $this->words).')))'
				. ' ORDER BY post_date DESC';
		}
		return $sql;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
