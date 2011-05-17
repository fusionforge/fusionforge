<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, Anthony J. Pugliese
 * Copyright 2009, Roland Mas
 * Copyright 2009, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Artifact ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';

class ArtifactExtraFieldElement extends Error {

	/** 
	 * The artifact type object.
	 *
	 * @var		object	$ArtifactExtraField.
	 */
	var $ArtifactExtraField; //object

	/**
	 * Array of artifact data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;
	/**
	 *	ArtifactExtraFieldElement - Constructer
	 *
	 *	@param	object	ArtifactExtraField object.
	 *  @param	array	(all fields from artifact_file_user_vw) OR id from database.
	 *  @return	boolean	success.
	 */
	function ArtifactExtraFieldElement(&$ArtifactExtraField,$data=false) {
		$this->Error(); 
		
		//was ArtifactExtraField legit?
		if (!$ArtifactExtraField || !is_object($ArtifactExtraField)) {
			$this->setError('ArtifactExtraField: No Valid ArtifactExtraField');
			return false;
		}
		//did ArtifactExtraField have an error?
		if ($ArtifactExtraField->isError()) {
			$this->setError('ArtifactExtraField: '.$ArtifactExtraField->getErrorMessage());
			return false;
		}
		$this->ArtifactExtraField =& $ArtifactExtraField;
		if ($data) {
			if (is_array($data)) {
//TODO validate that data actually belongs in this ArtifactExtraField
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
	 *  @param  int status_id - optional for status box - maps to either open/closed.
	 *  @return 	true on success / false on failure.
	 */
	
	function create($name,$status_id=0) {
		//
		//	data validation
		//
		if (trim($name) == '') {
			$this->setError(_('an element name is required'));
			return false;
		}
		if ($status_id) {
			if ($status_id==1) {
			} else {
				$status_id=2;
			}
		} else {
			$status_id=0;
		}
		if (!forge_check_perm ('tracker_admin', $this->ArtifactExtraField->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		$res = db_query_params ('SELECT element_name FROM artifact_extra_field_elements WHERE element_name=$1 AND extra_field_id=$2',
					array (htmlspecialchars ($name),
					       $this->ArtifactExtraField->getID())) ;
		if (db_numrows($res) > 0) {
			$this->setError(_('Element name already exists'));
			return false;
		}
		db_begin();
		$result = db_query_params ('INSERT INTO artifact_extra_field_elements (extra_field_id,element_name,status_id) VALUES ($1,$2,$3)',
					   array ($this->ArtifactExtraField->getID(),
						  htmlspecialchars($name),
						  $status_id)) ;
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			$id=db_insertid($result,'artifact_extra_field_elements','element_id');
			//
			//	Now set up our internal data structures
			//
			if (!$this->fetchData($id)) {
				db_rollback();
				return false;
			} else {
				// If new element belongs to Status custom field, then register the new element in the workflow.
				if ($this->ArtifactExtraField->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
					$atw = new ArtifactWorkflow($this->ArtifactExtraField->ArtifactType, $this->ArtifactExtraField->getID());
					$atw->addNode($id);
				}
				
				db_commit();
				return $id;
			}
		} else {
			$this->setError(db_error());
			db_rollback();
			return false;
		}
	}


	/**
	 *	fetchData - re-fetch the data for this ArtifactExtraFieldElement from the database.
	 *
	 *	@param	int		ID of the Box.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM artifact_extra_field_elements WHERE element_id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraFieldElement ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getArtifactExtraField - get the ArtifactExtraField Object this ArtifactExtraField is associated with.
	 *
	 *	@return object	ArtifactExtraField.
	 */
	function &getArtifactExtraField() {
		return $this->ArtifactExtraField;
	}
	
	/**
	 *	getID - get this ArtifactExtraField ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['element_id'];
	}
	
	/**
	 *	getBoxID - get this  artifact box id.
	 *
	 *	@return	int	The id #.
	 */
	function getBoxID() {
		return $this->data_array['extra_field_id'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	string	The name.
	 */
	function getName() {
		return $this->data_array['element_name'];
	}

	/**
	 *  getStatus - the status equivalent of this field (open or closed).
	 *
	 *  @return int status.
	 */
	function getStatusID() {
		return $this->data_array['status_id'];
	}

	/**
	 *  update - update rows in the table used to store the choices 
	 *  for a selection box. This function is used only for extra  
	 *  boxes and fields configured by the admin
	 *
	 *  @param	string	Name of the choice in a box.
	 *  @param  int status_id - optional for status box - maps to either open/closed.
	 *  @return	boolean	success.
	 */
	function update($name,$status_id=0) {
		if (!forge_check_perm ('tracker_admin', $this->ArtifactExtraField->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (trim($name) == '') {
			$this->setMissingParamsError();
			return false;
		}
		$res = db_query_params ('SELECT element_name FROM artifact_extra_field_elements WHERE element_name=$1 AND extra_field_id=$2 AND element_id != $3',
					array ($name,
					       $this->ArtifactExtraField->getID(),
					       $this->getID())) ;
		if (db_numrows($res) > 0) {
			$this->setError(_('Element name already exists'));
			return false;
		}
		if ($status_id) {
			if ($status_id==1) {
			} else {
				$status_id=2;
			}
		} else {
			$status_id=0;
		}
		$result = db_query_params ('UPDATE artifact_extra_field_elements 
			SET element_name=$1, status_id=$2
			WHERE element_id=$3',
					   array (htmlspecialchars($name),
						  $status_id,
						  $this->getID())) ;
		if ($result && db_affected_rows($result) > 0) {
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}

	/**
	 *  delete - delete the current value.
	 *
	 *  @return	boolean	success.
	 */
	function delete() {
		if (!forge_check_perm ('tracker_admin', $this->ArtifactExtraField->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		$res = db_query_params ('SELECT element_id FROM artifact_extra_field_elements WHERE element_id=$1',
					array ($this->getID()));
		if (db_numrows($res) != 1) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraFieldElement ID');
			return false;
		}

		// Reset all artifacts to 100 before removing the value.
		$ef=$this->getArtifactExtraField();
		db_query_params ('UPDATE artifact_extra_field_data SET field_data=100 WHERE field_data=$1 AND extra_field_id=$2',
				 array ($this->getID(),
					$ef->getID())) ;

		$result = db_query_params ('DELETE FROM artifact_extra_field_elements WHERE element_id=$1',
				    array ($this->getID())) ;
		if (! $result || ! db_affected_rows($result)) {
			$this->setError(db_error());
			return false;
		}
		$result = db_query_params ('DELETE FROM artifact_workflow_event WHERE from_value_id=$1 OR to_value_id=$1',
				    array ($this->getID())) ;
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
