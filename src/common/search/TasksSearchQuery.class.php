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
	function __construct($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {
		$this->groupId = $groupId;
		$this->showNonPublic = $showNonPublic;

		parent::__construct($words, $offset, $isExact);

		$this->setSections($sections);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {
		$qpa = db_construct_qpa () ;

		$qpa = db_construct_qpa ($qpa,
					 'SELECT x.* FROM (SELECT y.group_project_id, y.project_task_id, y.summary, y.percent_complete, y.start_date, y.end_date, users.realname, project_group_list.project_name, y.full_string_agg',
					 array());
		if (forge_get_config('use_fti')) {
			$words = $this->getFTIwords();
			$qpa = db_construct_qpa ($qpa,
						 ', y.full_vector_agg',
						 array());
		}
		$qpa = db_construct_qpa ($qpa,
					 ' FROM (SELECT project_task.project_task_id, project_task.summary, project_task.percent_complete, project_task.start_date, project_task.end_date, project_task.created_by, project_task.group_project_id, project_task.summary||$1||project_task.details||$1||coalesce(ff_string_agg(project_messages.body), $1) as full_string_agg',
					 array($this->field_separator));
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 ', project_task_idx.vectors || coalesce(ff_tsvector_agg(project_messages_idx.vectors), to_tsvector($1)) AS full_vector_agg',
						 array(''));
		}
		$qpa = db_construct_qpa ($qpa,
					 ' FROM project_task LEFT OUTER JOIN project_messages USING (project_task_id)',
					 array());
		
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 ' LEFT OUTER JOIN project_messages_idx ON (project_messages.project_message_id = project_messages_idx.id) JOIN project_task_idx ON (project_task.project_task_id = project_task_idx.project_task_id)',
						 array());
		}
		$qpa = db_construct_qpa ($qpa,
					 ' GROUP BY project_task.project_task_id, project_task.summary, project_task.details, project_task.percent_complete, project_task.start_date, project_task.end_date, project_task.created_by, project_task.group_project_id',
					 array());
		
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 ', project_task_idx.vectors',
						 array());
		}
		$qpa = db_construct_qpa ($qpa,
					 ') AS y, users, project_group_list',
					 array());
		
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 ', project_task_idx',
						 array());
		}
		$qpa = db_construct_qpa ($qpa,
					 ' WHERE y.created_by = users.user_id AND y.group_project_id = project_group_list.group_project_id AND project_group_list.group_id = $1',
					 array($this->groupId));
		if ($this->sections != SEARCH__ALL_SECTIONS) {
			$qpa = db_construct_qpa ($qpa,
						 ' AND y.group_project_id = ANY ($1)',
						 array (db_int_array_to_any_clause ($this->sections))) ;
		}
		if (!$this->showNonPublic) {
			$qpa = db_construct_qpa ($qpa,
						 ' AND project_group_list.is_public = 1') ;
		}
		$qpa = db_construct_qpa ($qpa,
					 ' GROUP BY y.group_project_id, y.project_task_id, y.summary, y.percent_complete, y.start_date, y.end_date, users.realname, project_group_list.project_name, y.full_string_agg',
					 array());
		
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 ', y.full_vector_agg',
						 array());
		}
		$qpa = db_construct_qpa ($qpa,
					 ') AS x WHERE ',
					 array());
		
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 'full_vector_agg @@ to_tsquery($1) ',
						 array($words));
			if (count($this->phrases)) {
				$qpa = db_construct_qpa ($qpa,
							 'AND (') ;
				$qpa = $this->addMatchCondition ($qpa, 'x.full_string_agg') ;
				$qpa = db_construct_qpa ($qpa,
							 ') ') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 'ORDER BY ts_rank(full_vector_agg, to_tsquery($1)) DESC',
						 array($words)) ;
			
		} else {
			$qpa = $this->addIlikeCondition ($qpa, 'x.full_string_agg') ;
			$qpa = db_construct_qpa ($qpa,
						 ' ORDER BY x.project_name, x.project_task_id') ;
		}

		return $qpa ;
	}

	/**
	 * getSections - returns the list of available subprojects
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	static function getSections($groupId, $showNonPublic=false) {
		$sql = 'SELECT group_project_id, project_name FROM project_group_list WHERE group_id=$1' ;
		$sql .= ' ORDER BY project_name';

		$sections = array();
		$res = db_query_params ($sql,
					array ($groupId));
		while($data = db_fetch_array($res)) {
			if (forge_check_perm('pm',$data['group_project_id'],'read')) {
				$sections[$data['group_project_id']] = $data['project_name'];
			}
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
