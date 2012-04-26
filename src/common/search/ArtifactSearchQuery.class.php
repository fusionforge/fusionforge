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

class ArtifactSearchQuery extends SearchQuery {

	/**
	 * group id
	 *
	 * @var int $groupId
	 */
	var $groupId;

	/**
	 * artifact id
	 *
	 * @var int $artifactId
	 */
	var $artifactId;

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 * @param int $groupId group id
	 * @param int $artifactId artifact id
	 */
	function __construct($words, $offset, $isExact, $groupId, $artifactId) {
		//TODO: Why is groupId an arg and var since it isn't used anywhere?
		$this->groupId = $groupId;
		$this->artifactId = $artifactId;

		parent::__construct($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {
		$qpa = db_construct_qpa () ;

		$words = $this->getFTIwords();

		$qpa = db_construct_qpa ($qpa,
					 'SELECT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.summary||$1||artifact.details||$1||coalesce(ff_string_agg(artifact_message.body), $1) as full_string_agg',
						 array ($this->field_separator));
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa,
						 ', (artifact_idx.vectors || coalesce(ff_tsvector_agg(artifact_message_idx.vectors), to_tsvector($1))) AS full_vector_agg',
						 array(''));
						 }
		$qpa = db_construct_qpa ($qpa, 
					 ' FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users',
						 array ()) ;
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa, 
						 ', artifact_idx, artifact_message_idx',
						 array ()) ;
		}
		$qpa = db_construct_qpa ($qpa, 
					 ' WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = $1 ',
					 array ($this->artifactId)) ;
		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa, 
						 'AND artifact.artifact_id = artifact_idx.artifact_id AND artifact_message.id = artifact_message_idx.id ',
						 array ()) ;
		}
		$qpa = db_construct_qpa ($qpa,
					 'GROUP BY artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.details') ;

		if (forge_get_config('use_fti')) {
			$qpa = db_construct_qpa ($qpa, 
						 ', artifact_idx.vectors',
						 array ()) ;
		}
		$qpa = db_construct_qpa ($qpa, 
					 ') AS x WHERE ') ;
		
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
						 'ORDER BY x.artifact_id') ;
		}
		return $qpa ;






		$qpa = db_construct_qpa () ;

		if (forge_get_config('use_fti')) {
			$words=$this->getFTIwords();
			$artifactId = $this->artifactId;

			$qpa = db_construct_qpa ($qpa,
						 'SELECT a.group_artifact_id, a.artifact_id, ts_headline(summary, to_tsquery($1)) AS summary, ',
						 array ($words)) ;
			$qpa = db_construct_qpa ($qpa,
						 'a.open_date, users.realname, rank FROM (SELECT a.artifact_id, SUM (ts_rank(ai.vectors, q) + ts_rank(ami.vectors, q)) AS rank, artifact.summary||$1||artifact.details||$1||coalesce(ff_string_agg(artifact_message.body), $1) as full_string_agg FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id)',
						 array($this->field_separator)) ;

			$qpa = db_construct_qpa ($qpa,
						 ', artifact_idx ai, artifact_message_idx ami, to_tsquery($1) q',
						 array ($words)) ;
			$qpa = db_construct_qpa ($qpa,
						 'WHERE a.group_artifact_id=$1',
						 array ($artifactId)) ;
			$qpa = db_construct_qpa ($qpa,
						 ' AND ai.artifact_id = a.artifact_id AND ami.id = am.id AND ((ai.vectors @@ q OR ami.vectors @@ q) ') ;

			if (count($this->phrases)) {
				$qpa = db_construct_qpa ($qpa,
							 $this->getOperator()) ;
				$qpa = db_construct_qpa ($qpa,
							 '((') ;
				$qpa = $this->addMatchCondition($qpa, 'a.details');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'a.summary');
				$qpa = db_construct_qpa ($qpa,
							 ') OR (') ;
				$qpa = $this->addMatchCondition($qpa, 'am.body');
				$qpa = db_construct_qpa ($qpa,
							 '))') ;
			}
			$qpa = db_construct_qpa ($qpa,
						 ') GROUP BY a.artifact_id) x, artifact a, users WHERE a.artifact_id=x.artifact_id AND users.user_id=a.submitted_by ORDER BY group_artifact_id ASC, rank DESC, a.artifact_id ASC') ;
		} else {
			$qpa = db_construct_qpa ($qpa,
						 'SELECT DISTINCT ON (a.group_artifact_id,a.artifact_id) a.group_artifact_id,a.artifact_id,a.summary,a.open_date,users.realname ') ;
			$qpa = db_construct_qpa ($qpa,
						 'FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id), users WHERE a.group_artifact_id=$1 AND users.user_id=a.submitted_by AND ((',
						 array ($this->artifactId)) ;

			$qpa = $this->addIlikeCondition ($qpa, 'a.details') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'a.summary') ;
			$qpa = db_construct_qpa ($qpa,
						 ') OR (') ;
			$qpa = $this->addIlikeCondition ($qpa, 'am.body') ;
			$qpa = db_construct_qpa ($qpa,
						 ')) ORDER BY group_artifact_id ASC, a.artifact_id ASC') ;
		}
		return $qpa;
	}

	/**
	 * getSearchByIdQuery - get the query built to get the search results when we are looking for an int
	 *
	 * @return array query+params array
	 */
	function getSearchByIdQuery() {
		$qpa = db_construct_qpa () ;

		$qpa = db_construct_qpa ($qpa,
					 'SELECT DISTINCT ON (a.group_artifact_id,a.artifact_id) a.group_artifact_id, a.artifact_id') ;
		$qpa = db_construct_qpa ($qpa,
					 ' FROM artifact a WHERE a.group_artifact_id=$1 AND a.artifact_id=$2',
					 array ($this->artifactId,
						$this->searchId)) ;

		return $qpa;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
