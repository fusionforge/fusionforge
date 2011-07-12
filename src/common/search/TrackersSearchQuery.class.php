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
	function TrackersSearchQuery($words, $offset, $isExact, $groupId, $sections=SEARCH__ALL_SECTIONS, $showNonPublic=false) {
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
				$qpa = db_construct_qpa ($qpa,
							 'SELECT DISTINCT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name, (rank(artifact_idx.vectors, q)+rank(artifact_message_idx.vectors, q)) AS rank FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list, to_tsquery($1) q, artifact_idx, artifact_message_idx WHERE users.user_id = artifact.submitted_by AND artifact_idx.artifact_id = artifact.artifact_id AND artifact_message_idx.id = artifact_message.id AND artifact_message_idx.artifact_id = artifact_message_idx.artifact_id AND artifact_group_list.group_artifact_id = artifact.group_artifact_id AND artifact_group_list.group_id = $2 ',

							 array ($this->getFormattedWords(),
								$this->groupId)) ;
				$tsmatch = "(artifact_idx.vectors @@ q OR artifact_message_idx.vectors @@ q)";
				$phraseOp = $this->getOperator();
			} else {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT DISTINCT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list WHERE users.user_id = artifact.submitted_by AND artifact_group_list.group_artifact_id = artifact.group_artifact_id AND artifact_group_list.group_id = $1 ',
							 array ($this->groupId)) ;


				$tsmatch = "";
				$orderBy = "";
				$phraseOp = "";
			}

			if (count($this->phrases)) {
				$qpa = db_construct_qpa ($qpa,
							 'AND ((') ;
				$qpa = $this->addMatchCondition($qpa, 'artifact.details');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'artifact.summary');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'artifact_message.body');
				$qpa = db_construct_qpa ($qpa,
							 ')) ') ;
			}
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND artifact_group_list.group_artifact_id = ANY ($1) ',
							 db_int_array_to_any_clause ($this->sections)) ;
			}
			if (!$this->showNonPublic) {
				$qpa = db_construct_qpa ($qpa,
							 'AND artifact_group_list.is_public = 1 ') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 ') x') ;
			if (count ($this->words)) {
				$qpa = db_construct_qpa ($qpa,
							 'ORDER BY rank DESC') ;
			}
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT DISTINCT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list WHERE users.user_id = artifact.submitted_by AND artifact_group_list.group_artifact_id = artifact.group_artifact_id AND artifact_group_list.group_id = $1 ',
						 array ($this->groupId)) ;
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$qpa = db_construct_qpa ($qpa,
							 'AND artifact_group_list.group_artifact_id = ANY ($1) ',
							 db_int_array_to_any_clause ($this->sections)) ;
			}
			if (!$this->showNonPublic) {
				$qpa = db_construct_qpa ($qpa,
							 'AND artifact_group_list.is_public = 1 ') ;
			}

			$qpa = db_construct_qpa ($qpa,
						 ' AND ((') ;
			$qpa = $this->addIlikeCondition ($qpa, 'artifact.details') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'artifact.summary') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'artifact_message.body') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) ORDER BY artifact_group_list.name, artifact.artifact_id') ;
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
		if (!$showNonPublic) {
			$sql .= ' AND artifact_group_list.is_public = 1';
		}
		$sql .= ' ORDER BY name';

		$res = db_query_params ($sql,
					array ($groupId));
		$sections = array();
		while($data = db_fetch_array($res)) {
			$sections[$data['group_artifact_id']] = $data['name'];
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
						 db_int_array_to_any_clause ($this->sections)) ;
		}
		if (!$this->showNonPublic) {
			$qpa = db_construct_qpa ($qpa,
						 'AND artifact_group_list.is_public = 1 ') ;
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
