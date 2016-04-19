<?php
/**
 * FusionForge trackers
 *
 * Copyright 2002, GForge, LLC
 * Copyright 2009, Roland Mas
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'tracker/ArtifactQuery.class.php';

class ArtifactQueryFactory extends FFError {
	/**
	 * The ArtifactType object.
	 *
	 * @var	 object  $Group.
	 */
	var $ArtifactType;

	/**
	 * The ArtifactQueries array.
	 *
	 * @var	 array	ArtifactQueries.
	 */
	var $ArtifactQueries = null;

	/**
	 * Constructor.
	 *
	 * @param	object	$ArtifactType	The Group object to which this ArtifactQueryFactory is associated
	 */
	function __construct(&$ArtifactType) {
		parent::__construct();
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactQueryFactory: No ArtifactType Object');
			return;
		}
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactQueryFactory: '.$ArtifactType->getErrorMessage());
			return;
		}
		$this->ArtifactType =& $ArtifactType;
	}

	function &getArtifactQueries() {
		if (!is_null($this->ArtifactQueries)) {
			return $this->ArtifactQueries;
		}

		$this->ArtifactQueries = array();

		$res = db_query_params ('SELECT * FROM artifact_query WHERE user_id=$1
					 AND group_artifact_id=$2',
					array (user_getid(),
					       $this->ArtifactType->getID())) ;
		if (!$res) {
			$this->setError("ArtifactQueryFactory: Database error");
		}

		while ($data = db_fetch_array($res)) {
			$artifactQuery = new ArtifactQuery($this->ArtifactType, $data["artifact_query_id"]);
			$this->ArtifactQueries[] = $artifactQuery;
		}

		return $this->ArtifactQueries;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
