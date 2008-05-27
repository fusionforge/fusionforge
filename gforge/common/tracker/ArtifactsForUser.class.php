<?php
/**
 * GForge Tracker Facility
 *
 * Copyright 2002 GForge, LLC
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFromID.class.php';

class ArtifactsForUser extends Error {

	var $User;
	var $Group;
	var $ArtifactType;
	var $Artifact;

	/**
	* Creates a new ArtifactsFor User object
	*
	* @param	user	the User object for which to collect artifacts
	*/
	function ArtifactsForUser(&$user) {
		$this->User =& $user;	
		return true;
	}

	/**
	*	getArtifactsFromSQL - Gets an array of Artifacts
	*	
	*	@param	sql	The sql that returns artifact_id
	*	@return	Artifact[]	The array of Artifacts
	*/
	function & getArtifactsFromSQL($sql) {
		$artifacts = array();
		$result=db_query($sql);
		$rows=db_numrows($result);
		if ($rows<=0) {
			return $artifacts;
		}
		for ($i=0; $i < $rows; $i++) {
			$id  = db_result($result,$i,'artifact_id');
			$arr = db_fetch_array($result);
			$afi =& new ArtifactFromID($id,$arr);
			if ($afi->isError()) {
				$this->setError($afi->getErrorMessage());
			} elseif($afi->Artifact->ArtifactType->Group->getStatus() == 'A') {
				$artifacts[] =& $afi->Artifact;
			}
		}
		return $artifacts;
	}

	/**
	*	getAssignedArtifacts	- Get the users's assigned artifacts
	*	@return	Artifact[]	The array of Artifacts
	*/
	function & getAssignedArtifactsByGroup() {
		$sql="SELECT * FROM artifact_vw av WHERE av.assigned_to=".$this->User->getID()."
			AND av.status_id='1' ORDER BY av.group_artifact_id, av.artifact_id DESC";
		return $this->getArtifactsFromSQL($sql);
	}

	/**
	*	getSubmittedArtifactsByGroup
	*
	*	@return Artifact[] The array of Artifacts
	*/
	function & getSubmittedArtifactsByGroup() {
		$sql="SELECT *
			FROM artifact_vw av
			WHERE av.submitted_by=".$this->User->getID()."
			AND av.status_id='1'
			ORDER BY av.group_artifact_id, av.artifact_id DESC";
		return $this->getArtifactsFromSQL($sql);
	}
}
?>
