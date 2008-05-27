<?php
/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * http://gforge.org
 *
 * @version $Id$
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
	 * getQuery - get the sql query built to get the search results
	 *
	 * @return string sql query to execute
	 */
	function getQuery() {
		global $sys_use_fti;
		if ($sys_use_fti) {
			if(count($this->words)) {
				$tsquery = ", to_tsquery('".$this->getFormattedWords()."') q, artifact_idx, artifact_message_idx ";
				$tsmatch = "(artifact_idx.vectors @@ q OR artifact_message_idx.vectors @@ q)";
				$rankCol = ", (rank(artifact_idx.vectors, q)+rank(artifact_message_idx.vectors, q)) AS rank ";
				$tsjoin = 'AND artifact_idx.artifact_id = artifact.artifact_id '
						. 'AND artifact_message_idx.id = artifact_message.id '
						. 'AND artifact_message_idx.artifact_id = artifact_message_idx.artifact_id ';
				$orderBy = "ORDER BY RANK DESC";
				$phraseOp = $this->getOperator();
			} else {
				$tsquery = "";
				$tsmatch = "";
				$tsjoin = "";
				$rankCol = "";
				$orderBy = "";
				$phraseOp = "";
			}
			$phraseCond = '';
			if(count($this->phrases)) {
				$phraseCond .= $phraseOp.'('
					. ' ('.$this->getMatchCond('artifact.details', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('artifact.summary', $this->phrases).')'
					. ' OR ('.$this->getMatchCond('artifact_message.body', $this->phrases).'))';
			}
			$sql = 'SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name '
				. $rankCol
				. 'FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list '
				. $tsquery
				. ' WHERE users.user_id = artifact.submitted_by '
				. $tsjoin
				. 'AND artifact_group_list.group_artifact_id = artifact.group_artifact_id '
				. 'AND artifact_group_list.group_id = '.$this->groupId.' ';
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sql .= 'AND artifact_group_list.group_artifact_id in ('.$this->sections.') ';
			}
			if (!$this->showNonPublic) {
				$sql .= 'AND artifact_group_list.is_public = 1 ';
			}
			$sql .= "AND ($tsmatch $phraseCond)";
			$sql = "SELECT DISTINCT x.* FROM ($sql) x $orderBy";
		} else {
			$sql = 'SELECT DISTINCT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name '
				. 'FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list '
				. 'WHERE users.user_id = artifact.submitted_by '
				. 'AND artifact_group_list.group_artifact_id = artifact.group_artifact_id '
				. 'AND artifact_group_list.group_id = '.$this->groupId.' ';
			if ($this->sections != SEARCH__ALL_SECTIONS) {
				$sql .= 'AND artifact_group_list.group_artifact_id in ('.$this->sections.') ';
			}
			if (!$this->showNonPublic) {
				$sql .= 'AND artifact_group_list.is_public = 1 ';
			}
			$sql .= 'AND (('.$this->getIlikeCondition('artifact.details', $this->words).') ' 
				. 'OR ('.$this->getIlikeCondition('artifact.summary', $this->words).') '
				. 'OR ('.$this->getIlikeCondition('artifact_message.body', $this->words).')) '
				. 'ORDER BY artifact_group_list.name, artifact.artifact_id';
		}
		return $sql;
	}
	
	/**
	 * getSections - returns the list of available trackers
	 *
	 * @param $groupId int group id
	 * @param $showNonPublic boolean if we should consider non public sections
	 */
	function getSections($groupId, $showNonPublic=false) {
		$sql = 'SELECT group_artifact_id, name FROM artifact_group_list WHERE group_id = '.$groupId.'';
		if (!$showNonPublic) {
			$sql .= ' AND artifact_group_list.is_public = 1';
		}
		$sql .= ' ORDER BY name';
		
		$sections = array();
		$res = db_query($sql);
		while($data = db_fetch_array($res)) {
			$sections[$data['group_artifact_id']] = $data['name'];
		}
		return $sections;
	}
	
	function getSearchByIdQuery() {
		$sql = 'SELECT DISTINCT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_group_list.name '
			. 'FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list '
			. 'WHERE users.user_id = artifact.submitted_by '
			. 'AND artifact_group_list.group_artifact_id = artifact.group_artifact_id '
			. 'AND artifact_group_list.group_id = '.$this->groupId.' ';
		if ($this->sections != SEARCH__ALL_SECTIONS) {
			$sql .= 'AND artifact_group_list.group_artifact_id in ('.$this->sections.') ';
		}
		if (!$this->showNonPublic) {
			$sql .= 'AND artifact_group_list.is_public = 1 ';
		}
		$sql .= 'AND artifact.artifact_id=\''.$this->searchId.'\''
			. 'ORDER BY artifact_group_list.name, artifact.artifact_id';


		return $sql;
	}
}

?>
