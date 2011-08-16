<?php
/**
 * FusionForge search engine
 *
 * Copyright 1999-2001, VA Linux Systems, Inc
 * Copyright 2004, Guillaume Smet/Open Wide
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
	function ArtifactSearchQuery($words, $offset, $isExact, $groupId, $artifactId) {
		//TODO: Why is groupId an arg and var since it isn't used anywhere?
		$this->groupId = $groupId;
		$this->artifactId = $artifactId;

		$this->SearchQuery($words, $offset, $isExact);
	}

	/**
	 * getQuery - get the query built to get the search results
	 *
	 * @return array query+params array
	 */
	function getQuery() {


		$qpa = db_construct_qpa () ;

		if (forge_get_config('use_fti')) {
			$words=$this->getFormattedWords();
			$artifactId = $this->artifactId;

			if (count($words)) {
				$qpa = db_construct_qpa ($qpa,
							 'SELECT a.group_artifact_id, a.artifact_id, ts_headline(summary, $1) AS summary, ',
							 array ($this->getFormattedWords())) ;
				$qpa = db_construct_qpa ($qpa,
							 'a.open_date, users.realname, rank FROM (SELECT a.artifact_id, SUM (ts_rank(ai.vectors, q) + ts_rank(ami.vectors, q)) AS rank FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id)') ;

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
							 'SELECT a.group_artifact_id, a.artifact_id, summary, a.open_date, users.realname, rank FROM (SELECT a.artifact_id, 0 AS rank FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id)') ;

				$qpa = db_construct_qpa ($qpa,
							 'WHERE a.group_artifact_id=$1',
							 array ($artifactId)) ;

				if (count($this->phrases)) {
					$qpa = db_construct_qpa ($qpa,
								 ' AND ((') ;
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
							 ' GROUP BY a.artifact_id) x, artifact a, users WHERE a.artifact_id=x.artifact_id AND users.user_id=a.submitted_by ORDER BY group_artifact_id ASC, rank DESC, a.artifact_id ASC') ;
			}
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
