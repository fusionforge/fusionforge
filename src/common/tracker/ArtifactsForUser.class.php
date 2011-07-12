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
	function &getArtifactsFromSQLwithParams ($sql, $params) {
		$artifacts = array();
		$result = db_query_params ($sql, $params);
		$rows=db_numrows($result);
		if ($rows<=0) {
			return $artifacts;
		}
		for ($i=0; $i < $rows; $i++) {
			$id  = db_result($result,$i,'artifact_id');
			$arr = db_fetch_array($result);
			$afi = new ArtifactFromID($id,$arr);
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
	function &getAssignedArtifactsByGroup() {
		return $this->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av WHERE av.assigned_to=$1 AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',
							    array($this->User->getID())) ;

	}

	/**
	*	getSubmittedArtifactsByGroup
	*
	*	@return Artifact[] The array of Artifacts
	*/
	function &getSubmittedArtifactsByGroup() {
		return $this->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av WHERE av.submitted_by=$1 AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',
							    array($this->User->getID())) ;
	}

	/**
	*	getMonitoredArtifacts
	*
	*	@return Artifact[] The array of Artifacts
	*/
	function & getMonitoredArtifacts() {
		$artifacts = array();

		$result=db_query_params ('SELECT groups.group_name,groups.group_id,
artifact_group_list.group_artifact_id,
artifact_group_list.name
FROM groups,artifact_group_list,artifact_type_monitor
WHERE groups.group_id=artifact_group_list.group_id
AND groups.status =$1
AND artifact_group_list.group_artifact_id=artifact_type_monitor.group_artifact_id
AND artifact_type_monitor.user_id=$2
ORDER BY group_name DESC',
					 array('A',
					       $this->User->getID()));
		$rows=db_numrows($result);
		if ($rows < 1) {
		        return $artifacts;
		}
		for ($i=0; $i<$rows; $i++) {
			$group_id = db_result($result,$i,'group_id');
			$group_artifact_id = db_result($result,$i,'group_artifact_id');
			$group = group_get_object($group_id);
			$artifact = new ArtifactType($group,$group_artifact_id);
			$ag = $artifact->getGroup();
			if ($artifact->isError()) {
				$this->setError($artifact->getErrorMessage());
			} else {
				$artifacts[] = $artifact;
			}
		}
		return $artifacts;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
