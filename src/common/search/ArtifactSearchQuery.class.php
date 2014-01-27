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
		$qpa = db_construct_qpa() ;

		if (forge_get_config('use_fti')) {
			$words = $this->getFTIwords();

			if (count($this->phrases)) {
				$qpa = db_construct_qpa($qpa,
							 'SELECT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.summary||$1||artifact.details||$1||coalesce(ff_string_agg(artifact_message.body), $1) as full_string_agg, artifact_idx.vectors FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_idx WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = $2 AND artifact.artifact_id = artifact_idx.artifact_id AND vectors @@ to_tsquery($3) GROUP BY artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.details, vectors) AS x WHERE ',
							 array ($this->field_separator, $this->artifactId, $words)) ;
				$qpa = $this->addMatchCondition ($qpa, 'full_string_agg') ;
				$qpa = db_construct_qpa($qpa,
						 ' ORDER BY ts_rank(vectors, to_tsquery($1)) DESC',
						 array($words)) ;
			} else {
				$qpa = db_construct_qpa($qpa,
							 'SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact_idx.vectors FROM artifact, users, artifact_idx WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = $1 AND artifact.artifact_id = artifact_idx.artifact_id AND vectors @@ to_tsquery($2) ORDER BY ts_rank(vectors, to_tsquery($2)) DESC',
							 array ($this->artifactId, $words)) ;
			}

		} else {
			$qpa = db_construct_qpa($qpa,
						 'SELECT x.* FROM (SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.summary||$1||artifact.details||$1||coalesce(ff_string_agg(artifact_message.body), $1) as full_string_agg FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users WHERE users.user_id = artifact.submitted_by AND artifact.group_artifact_id = $2 GROUP BY artifact.artifact_id, artifact.group_artifact_id, artifact.summary, artifact.open_date, users.realname, artifact.details) AS x WHERE ',
						 array ($this->field_separator, $this->artifactId)) ;
			$qpa = $this->addIlikeCondition ($qpa, 'full_string_agg') ;
			$qpa = db_construct_qpa($qpa,
						 ' ORDER BY artifact_id') ;
		}

		return $qpa ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
