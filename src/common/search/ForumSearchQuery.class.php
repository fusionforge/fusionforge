<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
	 * @param	string	$words		words we are searching for
	 * @param	int	$offset		offset
	 * @param	bool	$isExact	if we want to search for all the words or if only one matching the query is sufficient
	 * @param	int	$groupId	group id
	 * @param	int	$forumId	forum id
	 */
	function __construct($words, $offset, $isExact, $groupId, $forumId) {
		$this->groupId = $groupId;
		$this->forumId = $forumId;

		parent::__construct($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return	array	query+params array
	 */
	function getQuery() {
		$words = $this->getFTIwords();
		$qpa = db_construct_qpa(false, 'SELECT x.* FROM (SELECT forum.group_forum_id, forum.msg_id, ts_headline(forum.subject, $1::tsquery) AS subject, forum.post_date, users.realname, forum.subject||$2||forum.body as full_string_agg, forum_idx.vectors FROM forum, users, to_tsquery($1) AS q, forum_idx WHERE forum.group_forum_id = $3 AND forum.posted_by = users.user_id AND forum_idx.msg_id = forum.msg_id GROUP BY forum.group_forum_id, forum.msg_id, subject, body, post_date, realname, forum_idx.vectors) AS x WHERE vectors @@ $1::tsquery ',
						array($words, $this->field_separator, $this->forumId));
		$phraseOp = $this->getOperator();

		if(count($this->phrases)) {
			$qpa = db_construct_qpa($qpa, 'AND (');
			$qpa = $this->addMatchCondition($qpa, 'full_string_agg');
			$qpa = db_construct_qpa($qpa, ') ');
		}
		$qpa = db_construct_qpa($qpa, 'ORDER BY ts_rank(vectors, $1) DESC', array($words));
		return $qpa;
	}

	function isRowVisible($row) {
		return forge_check_perm('forum', $row['group_forum_id'], 'read');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
