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
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_use_fti;
		if ($sys_use_fti) {
			$words = $this->getFormattedWords();
			if(count($this->words)) {
				$tsquery0 = "headline(forum.subject, q) AS subject";
				$tsquery = ", to_tsquery('".$words."') AS q, forum_idx as fi";
				$tsmatch = "vectors @@ q";
				$rankCol = "";
				$tsjoin = 'AND fi.msg_id = forum.msg_id';
				$orderBy = "ORDER BY rank(vectors, q) DESC";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery0 = "subject";
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "ORDER BY post_date DESC";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$bodyCond = $this->getMatchCond('forum.body', $this->phrases);
				$subjectCond = $this->getMatchCond('forum.subject', $this->phrases);
				$phraseCond = $phraseOp.' (('.$bodyCond.') OR ('.$subjectCond.'))';
			}
			$sql = "SELECT forum.msg_id, $tsquery0, forum.post_date, users.realname
				FROM forum, users $tsquery
				WHERE
				forum.group_forum_id =".$this->forumId."
				AND forum.posted_by = users.user_id
				$tsjoin AND ($tsmatch $phraseCond)
				$orderBy";
		} else {
			$sql = 'SELECT forum.msg_id, forum.subject, forum.post_date, users.realname '
				. 'FROM forum,users '
				. 'WHERE users.user_id=forum.posted_by '
				. 'AND (('.$this->getIlikeCondition('forum.body', $this->words).') '
				. 'OR ('.$this->getIlikeCondition('forum.subject', $this->words).')) '
				. 'AND forum.group_forum_id=\''.$this->forumId.'\' '
				. 'GROUP BY msg_id, subject, post_date, realname';
		}
		return $sql;
	}
	
	/**
	 * getSearchByIdQuery - get the sql query built to get the search results when we are looking for an int
	 *
	 * @return string sql query to execute
	 */	
	function getSearchByIdQuery() {
		$sql = 'SELECT msg_id '
			. 'FROM forum '
			. 'WHERE msg_id=\''.$this->searchId.'\' '
			. 'AND group_forum_id=\''.$this->forumId.'\'';

		return $sql;
	}
}

?>
