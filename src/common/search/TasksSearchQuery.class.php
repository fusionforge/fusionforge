<?php
/**
 * FusionForge search engine
 *
 * Copyright 2004, Dominik Haas
 * Copyright 2009, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

class TasksSearchQuery extends SearchQuery {

	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;

	/**
	 * flag if non public items are returned
	 *
	 * @var bool $showNonPublic
	 */
	var $showNonPublic;

	/**
	 * @param	string	$words		words we are searching for
	 * @param	int	$offset		offset
	 * @param	bool	$isExact	if we want to search for all the words or if only one matching the query is sufficient
	 * @param	int	$groupId	group id
	 * @param	string 	$sections	sections to search in
	 * @param	bool	$showNonPublic	flag if private sections are searched too
	 */
	function __construct($words, $offset, $isExact, $groupId, $sections = SEARCH__ALL_SECTIONS, $showNonPublic = false) {
		$this->groupId = $groupId;
		$this->showNonPublic = $showNonPublic;

		parent::__construct($words, $offset, $isExact);

		$this->setSections($sections);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return	array	query+params array
	 */
	function getQuery() {
		$words = $this->getFTIwords();

		if (count($this->phrases)) {
			$qpa = db_construct_qpa(false, 'SELECT x.* FROM (SELECT project_task.project_task_id, project_task.group_project_id, project_task.summary, project_task.percent_complete, project_task.start_date, project_task.end_date, users.realname, project_group_list.project_name, project_task.summary||$1||project_task.details||$1||coalesce(ff_string_agg(project_messages.body), $1) as full_string_agg, project_task_idx.vectors FROM project_task LEFT OUTER JOIN project_messages USING (project_task_id), users, project_group_list, project_task_idx WHERE users.user_id = project_task.created_by AND project_task.group_project_id = project_group_list.group_project_id AND project_group_list.group_id = $2 ',
							array($this->field_separator, $this->groupId));

			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa($qpa, 'AND project_group_list.group_project_id = ANY ($1) ',
								array(db_int_array_to_any_clause ($this->sections)));
			}

			$qpa = db_construct_qpa($qpa, ' AND project_task.project_task_id = project_task_idx.project_task_id AND vectors @@ to_tsquery($1) GROUP BY project_task.project_task_id, project_task.group_project_id, project_task.summary, project_task.percent_complete, project_task.start_date, project_task.end_date, users.realname, project_group_list.project_name, project_task.details, vectors) AS x WHERE ',
							array($words));
			$qpa = $this->addMatchCondition($qpa, 'full_string_agg');
			$qpa = db_construct_qpa($qpa, ' ORDER BY ts_rank(vectors, to_tsquery($1)) DESC',
						array($words));
		} else {
			$qpa = db_construct_qpa(false, 'SELECT project_task.project_task_id, project_task.group_project_id, project_task.summary, project_task.percent_complete, project_task.start_date, project_task.end_date, users.realname, project_group_list.project_name, project_task_idx.vectors FROM project_task, users, project_group_list, project_task_idx WHERE users.user_id = project_task.created_by AND project_task.group_project_id = project_group_list.group_project_id AND project_group_list.group_id = $1 ',
							array($this->groupId));

			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa($qpa, 'AND project_group_list.group_project_id = ANY ($1) ',
								array(db_int_array_to_any_clause ($this->sections)));
			}

			$qpa = db_construct_qpa($qpa, 'AND project_task.project_task_id = project_task_idx.project_task_id AND vectors @@ to_tsquery($1) ORDER BY ts_rank(vectors, to_tsquery($1)) DESC',
							array($words));
		}

		return $qpa ;
	}

	/**
	 * getSections - returns the list of available subprojects
	 *
	 * @param	int	$groupId	group id
	 * @param	bool	$showNonPublic	if we should consider non public sections
	 * @return	array
	 */
	static function getSections($groupId, $showNonPublic = false) {
		$sql = 'SELECT group_project_id, project_name FROM project_group_list WHERE group_id=$1' ;
		$sql .= ' ORDER BY project_name';

		$sections = array();
		$res = db_query_params($sql, array($groupId));
		while($data = db_fetch_array($res)) {
			if (forge_check_perm('pm',$data['group_project_id'],'read')) {
				$sections[$data['group_project_id']] = $data['project_name'];
			}
		}
		return $sections;
	}

	function isRowVisible($row) {
		return forge_check_perm('pm', $row['group_project_id'], 'read');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
