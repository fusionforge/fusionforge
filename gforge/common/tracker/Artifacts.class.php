<?php
/**
 * Artifacts.class.php - Class to handle multiple artifacts
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('common/include/Error.class.php');
require_once('common/tracker/Artifact.class.php');

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
	function &getArtifacts($offset=false) {
		if (!$offset) {
			$offset = 0;
		}

		$sql = "SELECT 
					* 
				FROM 
					artifact_vw 
				WHERE 
					group_artifact_id='". $this->ArtifactType->getID() ."'";
	
		$res = db_query($sql,500,$offset);

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

?>
