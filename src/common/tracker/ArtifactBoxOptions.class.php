<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, Anthony J. Pugliese
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

class ArtifactBoxOptions extends Error {

	/**
	 * The artifact type object.
	 *
	 * @var		object	$ArtifactType.
	 */
	var $ArtifactType; //object

	/**
	 * Array of artifact data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;
	/**
	 *	ArtifactSelectionBox - Constructer
	 *
	 *	@param	object	ArtifactType object.
	 *  @param	array	(all fields from artifact_file_user_vw) OR id from database.
	 *  @return	boolean	success.
	 */
	function ArtifactBoxOptions(&$ArtifactType,$data=false) {
		$this->Error();

		//was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactSelectionBox: No Valid ArtifactType');
			return false;
		}
		//did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactSelectionBox: '.$Artifact->getErrorMessage());
			return false;
		}
		$this->ArtifactType =& $ArtifactType;
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
	 *	create - create a new row in the table used to store the
	 *	choices for selection boxes.  This function is only used for
	 *	extra fields and boxes configured by the admin
	 *
	 *	@param	string		Name of the choice
	 *	@param	int		Id the box that contains the choice.
	 *  @return 	true on success / false on failure.
	 */

	function create($name,$id) {
//settype($id,"integer");
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('an element name is required'));
			return false;
		}
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		$result = db_query_params ('INSERT INTO artifact_group_selection_box_options (artifact_box_id,box_options_name) VALUES ($1,$2)',
					   array ($id,
						  htmlspecialchars($name))) ;

		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}


			//
			//	Now set up our internal data structures
			//
			if (!$this->fetchData($id)) {
				return false;
			}

	}


	/**
	 *	fetchData - re-fetch the data for this ArtifactBoxOptions from the database.
	 *
	 *	@param	int		ID of the Box.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM artifact_group_selection_box_options WHERE id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactSelectionBox: Invalid Artifact ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getArtifactType - get the ArtifactType Object this ArtifactSelectionBox is associated with.
	 *
	 *	@return object	ArtifactType.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}

	/**
	 *	getID - get this ArtifactSelectionBox ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['id'];
	}

	/**
	 *	getBoxID - get this  artifact box id.
	 *
	 *	@return	int	The id #.
	 */
	function getBoxID() {
		return $this->data_array['artifact_box_id'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	string	The name.
	 */
	function getName() {
		return $this->data_array['box_options_name'];
	}


	/**
	 *  update - update rows in the table used to store the choices
	 *  for a selection box. This function is used only for extra
	 *  boxes and fields configured by the admin
	 *
	 *  @param	string	Name of the choice in a box.
	 *  @param	int	Id of the box
	 *  @param	int	id of the row
	 *  @return	boolean	success.
	 */
	function update($name,$boxid,$id) {
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		$result = db_query_params ('UPDATE artifact_group_selection_box_options
			SET box_options_name=$1
			WHERE id=$2',
					   array (htmlspecialchars($name),
						  $id)) ;
		if ($result && db_affected_rows($result) > 0) {
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
