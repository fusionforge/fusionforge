<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
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

class ForumSearchQuery extends SearchQuery {

	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;

	/**
	 * forum id
	 *
	 * @var int $groupId
	 */
	var $forumId;

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param int $forumId forum id
	 */
	function ForumSearchQuery($words, $offset, $isExact, $groupId, $forumId) {
		$this->groupId = $groupId;
		$this->forumId = $forumId;

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
			$words = $this->getFormattedWords();


			if(count($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname FROM forum, users, to_tsquery($1) AS q, forum_idx as fi WHERE forum.group_forum_id = $2 AND forum.posted_by = users.user_id AND fi.msg_id = forum.msg_id AND vectors @@ q ',
							 array ($words,
								$this->forumId)) ;
				$phraseOp = $this->getOperator();
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT forum.msg_id, subject, forum.post_date, users.realname FROM forum, users WHERE forum.group_forum_id = $1 AND forum.posted_by = users.user_id ',
							 array ($this->forumId)) ;
			}

			if(count($this->phrases)) {
				$qpa = db_construct_qpa ($qpa,
							 'AND ((') ;
				$qpa = $this->addMatchCondition($qpa, 'forum.body');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'forum.subject');
				$qpa = db_construct_qpa ($qpa,
							 ')) ') ;
			}
			if(count($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY rank(vectors, q) DESC') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY post_date DESC') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT forum.msg_id, forum.subject, forum.post_date, users.realname FROM forum,users WHERE users.user_id=forum.posted_by AND ((') ;
			$qpa = $this->addIlikeCondition ($qpa, 'forum.body') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'forum.subject') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) AND forum.group_forum_id=$1 GROUP BY msg_id, subject, post_date, realname',
						 array ($this->forumId)) ;
		}
		return $qpa ;
	}

	/**
	 * getSearchByIdQuery - get the sql query built to get the search results when we are looking for an int
	 *
	 * @return array query+params array
	 */
	function getSearchByIdQuery() {
		$qpa = db_construct_qpa () ;
		$qpa = db_construct_qpa ($qpa,
					 'SELECT msg_id FROM forum WHERE msg_id=$1 AND group_forum_id=$2',
					 array ($this->searchId,
						$this->forumId)) ;

		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
