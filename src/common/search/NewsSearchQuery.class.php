<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
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
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {


		$qpa = db_construct_qpa () ;

		if (forge_get_config('use_fti')) {
			$group_id=$this->groupId;

			if (count ($this->words)) {
				$words = $this->getFormattedWords();
				$qpa = db_construct_qpa ($qpa,
							 'SELECT ts_headline(news_bytes.summary, q) as summary, news_bytes.post_date, news_bytes.forum_id, users.realname FROM news_bytes, users, to_tsquery($1) AS q, news_bytes_idx WHERE (news_bytes.group_id=$2 AND news_bytes.is_approved <> 4 AND news_bytes_idx.id = news_bytes.id AND news_bytes.submitted_by=users.user_id) AND (vectors @@ q ',
							 array ($words,
								$group_id)) ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT summary, news_bytes.post_date, news_bytes.forum_id, users.realname FROM news_bytes, users WHERE (news_bytes.group_id=$1 AND news_bytes.is_approved <> 4 AND news_bytes.submitted_by=users.user_id) ',
							 array ($group_id)) ;
			}
			if (count ($this->phrases)) {
				if (count ($this->words)) {
					$qpa = db_construct_qpa ($qpa,
								 $this->getOperator()) ;
				}
				$qpa = db_construct_qpa ($qpa,
							 ' ((') ;
				$qpa = $this->addMatchCondition ($qpa, 'summary') ;
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition ($qpa, 'details') ;
				$qpa = db_construct_qpa ($qpa,
							 '))') ;
			}
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 ') ORDER BY ts_rank(vectors, q) DESC, post_date DESC') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 ') ORDER BY post_date DESC') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT news_bytes.summary, news_bytes.post_date, news_bytes.forum_id, users.realname FROM news_bytes, users WHERE group_id=$1 AND is_approved <> 4 AND news_bytes.submitted_by = users.user_id AND ((',
						 array ($this->groupId)) ;
			$qpa = $this->addIlikeCondition ($qpa, 'summary') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'details') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) ORDER BY post_date DESC') ;
		}
		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
