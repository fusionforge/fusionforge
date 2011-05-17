<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
 * Copyright 2009, Roland Mas
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

class ForumsSearchQuery extends SearchQuery {
	
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
	 * @param boolean $showNonPublic flag if private sections are searched too
	 */
	function ForumsSearchQuery($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {
		$this->groupId = $groupId;
		$this->showNonPublic = $showNonPublic;
		
		$this->SearchQuery($words, $offset, $isExact);
		
		$this->setSections($sections);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {


		$qpa = db_construct_qpa () ;

		if (forge_get_config('use_fti')) {
			$nonPublic = 'false';
			$sections = '';
			if ($this->showNonPublic) {
				$nonPublic = 'true';
			}
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sections = $this->sections;
			}

			$qpa = db_construct_qpa ($qpa,
						 'SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name FROM forum, users, forum_group_list, forum_idx, to_tsquery($1) as q ',
						 array ($this->getFormattedWords())) ;
			$qpa = db_construct_qpa ($qpa,
						 'WHERE users.user_id = forum.posted_by AND vectors @@ q AND forum.msg_id = forum_idx.msg_id AND forum_group_list.group_forum_id = forum.group_forum_id AND forum_group_list.is_public <> 9 AND forum.group_forum_id IN (SELECT group_forum_id FROM forum_group_list WHERE group_id = $1) ',
						 array ($this->groupId));
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND forum_group_list.group_forum_id = ANY ($1) ',
							 array (db_int_array_to_any_clause ($this->sections))) ;
			}
			if (!$this->showNonPublic) {
				$qpa = db_construct_qpa ($qpa,
							 'AND forum_group_list.is_public = 1 ') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 'ORDER BY forum_group_list.forum_name ASC, forum.msg_id ASC, rank(vectors, q) DESC') ;
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT forum.msg_id, forum.subject, forum.post_date, users.realname, forum_group_list.forum_name FROM forum, users, forum_group_list WHERE users.user_id = forum.posted_by AND forum_group_list.group_forum_id = forum.group_forum_id AND forum_group_list.is_public <> 9 AND forum.group_forum_id IN (SELECT group_forum_id FROM forum_group_list WHERE group_id = $1) ',
						 array ($this->groupId)) ;
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND forum_group_list.group_forum_id = ANY ($1) ',
							 array (db_int_array_to_any_clause ($this->sections))) ;
			}
			if (!$this->showNonPublic) {
				$qpa = db_construct_qpa ($qpa,
							 'AND forum_group_list.is_public = 1 ') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 'AND ((') ;
			$qpa = $this->addIlikeCondition ($qpa, 'forum.body') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa,'forum.subject') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) ORDER BY forum_group_list.forum_name, forum.msg_id') ;
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
					 'SELECT msg_id FROM forum, forum_group_list WHERE msg_id=$1 AND forum_group_list.group_forum_id=forum.group_forum_id AND group_forum_id=$2',
					 array ($this->searchId,
						$this->forumId)) ;
		if (!$this->showNonPublic) {
			$qpa = db_construct_qpa ($qpa,
						 ' AND forum_group_list.is_public=1') ;
		}

		return $qpa;
	}
	
	/**
	 * getSections - returns the list of available forums
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	static function getSections($groupId, $showNonPublic=false) {
		$sql = 'SELECT group_forum_id, forum_name FROM forum_group_list WHERE group_id = $1 AND is_public <> 9';
		if (!$showNonPublic) {
			$sql .= ' AND is_public = 1';
		}
		$sql .= ' ORDER BY forum_name';
		
		$sections = array();
		$res = db_query_params ($sql,
					array ($groupId));
		while($data = db_fetch_array($res)) {
			$sections[$data['group_forum_id']] = $data['forum_name'];
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
