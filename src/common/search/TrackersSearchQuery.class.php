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

class TrackersSearchQuery extends SearchQuery {

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

		if (forge_get_config('use_fti')) {
			$words = $this->getFTIwords();

			if (count($this->phrases)) {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.summary||$1||artifact.details||$1||coalesce(ff_string_agg(artifact_message.body), $1) as full_string_agg, artifact_idx.vectors FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list, artifact_idx WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = artifact_group_list.group_artifact_id AND artifact_group_list.group_id = $2 ',
							 array ($this->field_separator, $this->groupId)) ;

				if ($this->sections != SEARCH__ALL_SECTIONS) {
					$qpa = db_construct_qpa ($qpa,
								 'AND artifact_group_list.group_artifact_id = ANY ($1) ',
								 array (db_int_array_to_any_clause ($this->sections))) ;
				}

				$qpa = db_construct_qpa ($qpa,
							 ' AND artifact.artifact_id = artifact_idx.artifact_id AND vectors @@ to_tsquery($1) GROUP BY artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.details, vectors) AS x WHERE ',
							 array ($words)) ;
				$qpa = $this->addMatchCondition ($qpa, 'full_string_agg') ;
				$qpa = db_construct_qpa ($qpa,
						 ' ORDER BY ts_rank(vectors, to_tsquery($1)) DESC',
						 array($words)) ;
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_idx.vectors FROM artifact, users, artifact_group_list, artifact_idx WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = artifact_group_list.group_artifact_id AND artifact_group_list.group_id = $1 ',
							 array ($this->groupId)) ;
				
				if ($this->sections != SEARCH__ALL_SECTIONS) {
					$qpa = db_construct_qpa ($qpa,
								 'AND artifact_group_list.group_artifact_id = ANY ($1) ',
								 array (db_int_array_to_any_clause ($this->sections))) ;
				}

				$qpa = db_construct_qpa ($qpa,
							 'AND artifact.artifact_id = artifact_idx.artifact_id AND vectors @@ to_tsquery($1) ORDER BY ts_rank(vectors, to_tsquery($1)) DESC',
							 array ($words)) ;
			}

		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.summary||$1||artifact.details||$1||coalesce(ff_string_agg(artifact_message.body), $1) as full_string_agg FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = artifact_group_list.group_artifact_id AND artifact_group_list.group_id = $2 ',
						 array ($this->field_separator, $this->groupId)) ;

			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND artifact_group_list.group_artifact_id = ANY ($1) ',
							 array (db_int_array_to_any_clause ($this->sections))) ;
			}

			$qpa = db_construct_qpa ($qpa,
						 'GROUP BY artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.details) AS x WHERE ',
						 array ()) ;
			$qpa = $this->addIlikeCondition ($qpa, 'full_string_agg') ;
			$qpa = db_construct_qpa ($qpa,
						 ' ORDER BY artifact_id') ;
		}

		return $qpa ;
	}

	/**
	 * getSections - returns the list of available trackers
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	static function getSections($groupId, $showNonPublic=false) {
		$sql = 'SELECT group_artifact_id, name FROM artifact_group_list WHERE group_id = $1';
		$sql .= ' ORDER BY name';

		$res = db_query_params ($sql,
					array ($groupId));
		$sections = array();
		while($data = db_fetch_array($res)) {
			if (forge_check_perm('tracker',$data['group_artifact_id'],'read')) {
				$sections[$data['group_artifact_id']] = $data['name'];
			}
		}
		return $sections;
	}

	function getSearchByIdQuery() {
		$qpa = db_construct_qpa () ;
		$qpa = db_construct_qpa ($qpa,
					 'SELECT DISTINCT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list WHERE users.user_id = artifact.submitted_by AND artifact_group_list.group_artifact_id = artifact.group_artifact_id AND artifact_group_list.group_id = $1 ',
					 array ($this->groupId)) ;
		if ($this->sections != SEARCH__ALL_SECTIONS) {
			$qpa = db_construct_qpa ($qpa,
						 'AND artifact_group_list.group_artifact_id = ANY ($1) ',
						 array( db_int_array_to_any_clause ($this->sections))) ;
		}
		$qpa = db_construct_qpa ($qpa,
					 'AND artifact.artifact_id=$1 ORDER BY artifact_group_list.name, artifact.artifact_id',
					 array ($this->searchId)) ;

		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
