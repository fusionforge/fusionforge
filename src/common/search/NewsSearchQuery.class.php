<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, French Ministry of National Education
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
	function __construct($words, $offset, $isExact, $groupId) {
		$this->groupId = $groupId;

		parent::__construct($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {

		$qpa = db_construct_qpa() ;

		if (forge_get_config('use_fti')) {
			$group_id=$this->groupId;

			$words = $this->getFTIwords();
			$qpa = db_construct_qpa($qpa,
						 'SELECT x.* FROM (SELECT ts_headline(news_bytes.summary, q) as summary, news_bytes.post_date, news_bytes.forum_id, users.realname, summary||$1||details AS full_string_agg, news_bytes_idx.vectors FROM news_bytes, users, to_tsquery($2) AS q, news_bytes_idx WHERE (news_bytes.group_id=$3 AND news_bytes.is_approved <> 4 AND news_bytes_idx.id = news_bytes.id AND news_bytes.submitted_by=users.user_id) AND vectors @@ q) AS x ',
						 array ($this->field_separator,
							$words,
							$group_id)) ;
			if (count ($this->phrases)) {
				$qpa = db_construct_qpa($qpa,
							 'WHERE ');
				$qpa = $this->addMatchCondition ($qpa, 'full_string_agg') ;
			}
			$qpa = db_construct_qpa($qpa,
						 ' ORDER BY ts_rank(vectors, to_tsquery($1)) DESC, post_date DESC',
						 array($words)) ;
		} else {
			$qpa = db_construct_qpa($qpa,
						 'SELECT x.* FROM (SELECT news_bytes.summary, news_bytes.post_date, news_bytes.forum_id, users.realname, summary||$1||details AS full_string_agg FROM news_bytes, users WHERE group_id=$2 AND is_approved <> 4 AND news_bytes.submitted_by = users.user_id) AS x WHERE ',
						 array ($this->field_separator,
							$this->groupId)) ;
			$qpa = $this->addIlikeCondition ($qpa, 'full_string_agg') ;
			$qpa = db_construct_qpa($qpa,
						 ' ORDER BY post_date DESC') ;
		}
		return $qpa ;
	}

	/**
	 * getSections - returns the list of available forums
	 *
	 * @param int $groupId group id
	 * @param bool $showNonPublic if we should consider non public sections
	 * @return array
	 */
	static function getSections($groupId, $showNonPublic=false) {

		// Select survey of the project
		$sql = 'SELECT group_forum_id, forum_name FROM forum_group_list WHERE group_id = $1 AND ';
		$sql .= 'group_forum_id IN (SELECT forum_id FROM news_bytes) ORDER BY forum_name';

		$sections = array();
		$res = db_query_params ($sql,
					array ($groupId));
		while($data = db_fetch_array($res)) {
			if (forge_check_perm('forum',$data['group_forum_id'],'read')) {
				$sections[$data['group_forum_id']] = $data['forum_name'];
			}
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
