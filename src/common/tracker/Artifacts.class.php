<?php
/**
 * FusionForge trackers
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge, LLC
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'tracker/Artifact.class.php';

class Artifacts extends Error {

	/**
	 * Status db resource ID.
	 *
	 * @var		int		$status_res.
	 */
	var $status_res;

	/**
	 * Artifact Type object.
	 *
	 * @var		object	$ArtifactType.
	 */
	var $ArtifactType; 

	/**
	 * Array of Artifact objects.
	 *
	 * @var		array	$artifacts_array.
	 */
	var $artifacts_array; 

	/**
	 *  Artifacts - constructor.
	 *
	 *  Use this constructor if you are modifying an existing artifact.
	 *
	 *	@param	object	Artifact Type object.
	 *  @param	int		(primary key from database).
	 *  @return	boolean	success.
	 */
	function Artifacts(&$ArtifactType) {
		$this->Error(); 

		$this->ArtifactType =& $ArtifactType;

		//was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('Artifact: No Valid ArtifactType');
			return false;
		}
		//did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError('Artifact: '.$ArtifactType->getErrorMessage());
			return false;
		}

	}

	/**
	 *  getArtifacts - get an array of artifacts.
	 *
	 *  Retrieves an array of artifact objects.
	 *
	 *  @param	boolean	Database query offset.
	 *  @return an array of artifact objects on success / false on failure.
	 */
	function getArtifacts($offset=false) {
		if (!$offset) {
			$offset = 0;
		}
		$res = db_query_params ('SELECT * FROM artifact_vw WHERE group_artifact_id=$1',
					array ($this->ArtifactType->getID()),
					500,
					$offset) ;

		if (!$res) {
			$this->setError('Could not get artifacts: ' . db_error());
			return false;
		} else {
			while ($rows = db_fetch_array($res)) {
				$this->artifacts_array[] = new Artifact($this->ArtifactType, $rows);
			}

			return $this->artifacts_array;
		}
	}

	/**
	 * getArtifactType - get the artifact type.
	 *
	 *	@return	object	The ArtifactType object.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
