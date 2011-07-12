<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, GForge, LLC
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

class ArtifactHistory extends Error {

	/**
	 * The artifact object.
	 *
	 * @var		object	$Artifact.
	 */
	var $Artifact; //object

	/**
	 * Array of artifact data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *  ArtifactHistory - constructor.
	 *
	 *	@param	object	Artifact object.
	 *  @param	array	(all fields from artifact_history_user_vw) OR id from database.
	 *  @return	boolean	success.
	 */
	function ArtifactHistory(&$Artifact, $data=false) {
		$this->Error();

		//was Artifact legit?
		if (!$Artifact || !is_object($Artifact)) {
			$this->setError('ArtifactHistory: No Valid Artifact');
			return false;
		}
		//did Artifact have an error?
		if ($Artifact->isError()) {
			$this->setError('ArtifactHistory: '.$Artifact->getErrorMessage());
			return false;
		}
		$this->Artifact =& $Artifact;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
				return true;
			} else {
				if (!$this->fetchData($data)) {
					return false;
				} else {
					return true;
				}
			}
		}
	}

	/**
	 *	create - create a new item in the database.
	 *
	 *	@param	string	Item name.
	 *	@param	int		User_id of assignee.
	 *  @return id on success / false on failure.
	 * /
	function create($name, $auto_assign_to) {
		//
		//	data validation
		//
		if (!$name || !$auto_assign_to) {
			$this->setError(_('ArtifactCategory: name and assignee are Required'));
			return false;
		}
		if (!forge_check_perm ('tracker', $this->Artifact->ArtifactType->getID()), 'manager') {
			$this->setPermissionDeniedError();
			return false;
		}
		$result = db_query_params ('INSERT INTO artifact_category (group_artifact_id,category_name,auto_assign_to)
			VALUES ($1,$2,$3)',
					   array ($this->Artifact->getID(),
						  htmlspecialchars($name),
						  $auto_assign_to)) ;


		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}

		//	Now set up our internal data structures
		if (!$this->fetchData($id)) {
			return false;
		}
	}*/

	/**
	 *	fetchData - re-fetch the data for this ArtifactHistory from the database.
	 *
	 *	@param	int		ID of the category.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM artifact_category WHERE id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactHistory: Invalid ArtifactHistory ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getArtifact - get the Artifact Object this ArtifactHistory is associated with.
	 *
	 *	@return object	Artifact.
	 */
	function &getArtifact() {
		return $this->Artifact;
	}

	/**
	 *	getID - get this ArtifactHistory's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['id'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	string	The name.
	 */
	function getName() {
		return $this->data_array['category_name'];
	}

	/**
	 *	getAssignee - get the user_id of the person to assign this category to.
	 *
	 *	@return int user_id.
	 */
	function getAssignee() {
		return $this->data_array['auto_assign_to'];
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
