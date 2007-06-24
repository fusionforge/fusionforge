<?php
/**
 * ArtifactExtraField.class.php - Class to handle user defined artifacts
 *
 * Copyright 2004 (c) Anthony J. Pugliese
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
require_once('common/include/Error.class.php');

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
		global $Language;
		//
		//	data validation
		//
		if (!$name) {
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
		if (!$this->ArtifactExtraField->ArtifactType->userIsAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}
		$sql="INSERT INTO artifact_extra_field_elements (extra_field_id,element_name,status_id) 
			VALUES ('".$this->ArtifactExtraField->getID()."','".htmlspecialchars($name)."','$status_id')";
		db_begin();
		$result=db_query($sql);
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
		$res=db_query("SELECT * FROM artifact_extra_field_elements WHERE element_id='$id'");
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraFieldElement ID');
			return false;
		}
		$this->data_array =& db_fetch_array($res);
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
		if (!$this->ArtifactExtraField->ArtifactType->userIsAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!$name) {
			$this->setMissingParamsError();
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
		$sql="UPDATE artifact_extra_field_elements 
			SET element_name='".htmlspecialchars($name)."',
			status_id='$status_id' 
			WHERE element_id='".$this->getID()."'"; 
		$result=db_query($sql);
		if ($result && db_affected_rows($result) > 0) {
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}
}

?>
