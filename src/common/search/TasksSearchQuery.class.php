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
	function TasksSearchQuery($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {
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
			if (count ($this->words)) {
				$words = $this->getFormattedWords();

				$qpa = db_construct_qpa ($qpa,
							 'SELECT project_task.project_task_id, project_task.percent_complete, ts_headline(project_task.summary, q) AS summary, project_task.start_date,project_task.end_date,users.firstname||$1||users.lastname AS realname, project_group_list.project_name, project_group_list.group_project_id FROM project_task, users, project_group_list, to_tsquery($2) AS q, project_task_idx WHERE project_task.created_by = users.user_id AND project_task.project_task_id = project_task_idx.project_task_id AND project_task.group_project_id = project_group_list.group_project_id AND project_group_list.group_id=$3 ',
							 array (' ',
								$words,
								$this->groupId)) ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT project_task.project_task_id, project_task.percent_complete, summary, project_task.start_date,project_task.end_date,users.firstname||$1||users.lastname AS realname, project_group_list.project_name, project_group_list.group_project_id  FROM project_task, users, project_group_list WHERE project_task.created_by = users.user_id AND project_task.group_project_id = project_group_list.group_project_id AND project_group_list.group_id = $2 ',
							 array (' ',
								$this->groupId)) ;
			}
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND project_group_list.group_project_id = ANY ($1) ',
							 db_int_array_to_any_clause ($this->sections)) ;
			}
			if (!$this->showNonPublic) {
				$qpa = db_construct_qpa ($qpa,
							 'AND project_group_list.is_public = 1 ') ;
			}
			if (count($this->phrases)) {
				if (count ($this->words)) {
					$qpa = db_construct_qpa ($qpa,
								 'AND (vectors @@ q AND (') ;
				} else {
					$qpa = db_construct_qpa ($qpa,
								 'AND ((') ;
				}
				$qpa = $this->addMatchCondition($qpa, 'summary');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'details');
				$qpa = db_construct_qpa ($qpa,
							 ')) ') ;
			}
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY project_group_list.project_name, ts_rank(vectors, q) DESC, project_task.project_task_id') ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY project_group_list.project_name, project_task.project_task_id') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT project_task.project_task_id, project_task.summary, project_task.percent_complete, project_task.start_date, project_task.end_date, users.firstname||$1||users.lastname AS realname, project_group_list.project_name, project_group_list.group_project_id FROM project_task, users, project_group_list WHERE project_task.created_by = users.user_id AND project_task.group_project_id = project_group_list.group_project_id AND project_group_list.group_id = $2 ',
						 array (' ',
							$this->groupId)) ;
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND project_group_list.group_project_id = ANY ($1) ',
							 db_int_array_to_any_clause ($this->sections)) ;
			}
			if (!$this->showNonPublic) {
				$qpa = db_construct_qpa ($qpa,
							 'AND project_group_list.is_public = 1 ') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 ' AND ((') ;
			$qpa = $this->addIlikeCondition ($qpa, 'summary') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'details') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) ORDER BY project_group_list.project_name, project_task.project_task_id') ;
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
		if (!$showNonPublic) {
			$sql .= ' AND is_public = 1';
		}
		$sql .= ' ORDER BY project_name';

		$sections = array();
		$res = db_query_params ($sql,
					array ($groupId));
		while($data = db_fetch_array($res)) {
			$sections[$data['group_project_id']] = $data['project_name'];
		}
		return $sections;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
