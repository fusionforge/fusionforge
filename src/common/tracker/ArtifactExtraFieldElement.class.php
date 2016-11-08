<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, Anthony J. Pugliese
 * Copyright 2009, Roland Mas
 * Copyright 2009, Alcatel-Lucent
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';

class ArtifactExtraFieldElement extends FFError {

	/**
	 * The artifact type object.
	 *
	 * @var	object	$ArtifactExtraField.
	 */
	var $ArtifactExtraField; //object

	/**
	 * Array of artifact data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * @param	object		$ArtifactExtraField	ArtifactExtraField object.
	 * @param	array|bool	$data			(all fields from artifact_file_user_vw) OR id from database.
	 */
	function __construct(&$ArtifactExtraField,$data=false) {
		parent::__construct();

		// Was ArtifactExtraField legit?
		if (!$ArtifactExtraField || !is_object($ArtifactExtraField)) {
			$this->setError('ArtifactExtraField: No Valid ArtifactExtraField');
			return;
		}
		// Did ArtifactExtraField have an error?
		if ($ArtifactExtraField->isError()) {
			$this->setError('ArtifactExtraField: '.$ArtifactExtraField->getErrorMessage());
			return;
		}
		$this->ArtifactExtraField =& $ArtifactExtraField;
		if ($data) {
			if (is_array($data)) {
//TODO validate that data actually belongs in this ArtifactExtraField
				$this->data_array =& $data;
			} else {
				$this->fetchData($data);
			}
		}
	}

	/**
	 * create - create a new row in the table used to store the
	 * choices for selection boxes.  This function is only used for
	 * extra fields and boxes configured by the admin
	 *
	 * @param	string	$name		Name of the choice
	 * @param	int	$status_id	Id the box that contains the choice (optional).
	 * @return	bool	true on success / false on failure.
	 */
	function create($name,$status_id=0,$auto_assign_to=100,$is_default=0) {
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
		$result = db_query_params ('INSERT INTO artifact_extra_field_elements (extra_field_id,element_name,status_id,auto_assign_to) VALUES ($1,$2,$3,$4)',
					   array ($this->ArtifactExtraField->getID(),
						  htmlspecialchars($name),
						  $status_id,
						  $auto_assign_to));
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			$id=db_insertid($result,'artifact_extra_field_elements','element_id');

			if ($is_default) {
				$type = $this->ArtifactExtraField->getType();
				if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPE_SINGLECHOICETYPE))) {
					$result = db_query_params ('DELETE FROM artifact_extra_field_default WHERE extra_field_id = $1',
							array ($this->ArtifactExtraField->getID()));
					if (!$result) {
						$this->setError(db_error());
						db_rollback();
						return false;
					}
				}
				$result = db_query_params ('INSERT INTO artifact_extra_field_default (extra_field_id, default_value) VALUES ($1,$2)',
						array ($this->ArtifactExtraField->getID(),$id));
				if (!$result) {
					$this->setError(db_error());
					db_rollback();
					return false;
				}
			}
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
	 * fetchData - re-fetch the data for this ArtifactExtraFieldElement from the database.
	 *
	 * @param	int	$id	ID of the Box.
	 * @return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT *, 0 AS is_default FROM artifact_extra_field_elements WHERE element_id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraFieldElement ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		$default = db_query_params ('SELECT 1 FROM artifact_extra_field_default WHERE default_value=$1',
				array ($id)) ;
		if (!$default) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraFieldElement ID');
			return false;
		}
		if (db_numrows($default) >= 1) {
			$this->data_array['is_default'] = true;
		}
		db_free_result($default);
		return true;
	}

	/**
	 * getArtifactExtraField - get the ArtifactExtraField Object this ArtifactExtraField is associated with.
	 *
	 * @return	object	ArtifactExtraField.
	 */
	function &getArtifactExtraField() {
		return $this->ArtifactExtraField;
	}

	/**
	 * getID - get this ArtifactExtraField ID.
	 *
	 * @return	int	The id #.
	 */
	function getID() {
		return $this->data_array['element_id'];
	}

	/**
	 * getBoxID - get this  artifact box id.
	 *
	 * @return	int	The id #.
	 */
	function getBoxID() {
		return $this->data_array['extra_field_id'];
	}

	/**
	 * getName - get the name.
	 *
	 * @return	string	The name.
	 */
	function getName() {
		switch ($this->ArtifactExtraField->getType()) {
			case ARTIFACT_EXTRAFIELDTYPE_USER:
				$role = RBACEngine::getInstance()->getRoleById($this->data_array['element_name']);
				$name = $role->getName();
				break;
			case ARTIFACT_EXTRAFIELDTYPE_RELEASE:
				$package = frspackage_get_object($this->data_array['element_name']);
				$name = $package->getName();
				break;
			default:
				$name = $this->data_array['element_name'];
		}
		return $name;
	}

	/**
	 * getStatus - the status equivalent of this field (open or closed).
	 *
	 * @return	int	status.
	 */
	function getStatusID() {
		return $this->data_array['status_id'];
	}

	/**
	 * getAutoAssignedUser - return id of the user witch issue is auto assign to.
	 *
	 * @return	integer user id.
	 */
	function getAutoAssignto() {
		return $this->data_array['auto_assign_to'];
	}

	/**
	 * getParentElements - return the list of the elements of the parent field on which depends the current element
	 *
	 * @return	array of parent elements
	 */
	function getParentElements() {
		$res = db_query_params ('SELECT parent_element_id
				FROM artifact_extra_field_elements_dependencies
				WHERE child_element_id=$1',
				array($this->getID()));
		$values = array();
		while($arr = db_fetch_array($res)) {
			$values[] = $arr['parent_element_id'];
		}
		return $values;
	}

	/**
	 * isDefault - whether this field element is default value or not.
	 *
	 * @return	boolean
	 */
	function isDefault() {
		return $this->data_array['is_default'];
	}

	/**
	 * getChildrenElements - return the array of the elements of children fields who depend on current element
	 *
	 * @return	array of parent elements
	 */
	function getChildrenElements($childExtraFieldId = null) {
		if (is_null($childExtraFieldId)) {
			$aefChildren = $this->ArtifactExtraField->getChildren();
			$res = db_query_params ('SELECT extra_field_id, child_element_id
				FROM artifact_extra_field_elements_dependencies
				INNER JOIN artifact_extra_field_elements ON child_element_id = element_id
				WHERE parent_element_id=$1
				ORDER BY extra_field_id',
					array($this->getID()));
		} else {
			$aefChildren = array($childExtraFieldId);
			$res = db_query_params ('SELECT extra_field_id, child_element_id
				FROM artifact_extra_field_elements_dependencies
				INNER JOIN artifact_extra_field_elements ON child_element_id = element_id
				WHERE parent_element_id=$1
				AND extra_field_id=$2
				ORDER BY extra_field_id',
					array($this->getID(),
					$childExtraFieldId));
		}
		$values = array();
		$current = 0;
		if (is_array($aefChildren)) {
			foreach ($aefChildren as $aefChild) {
				$values[$aefChild] = array();
			}
			while($arr = db_fetch_array($res)) {
				$values[$arr['extra_field_id']][] = $arr['child_element_id'];
			}
		}
		return $values;
	}
	/**
	 * saveParentElements - save the list of the elements of the parent field on which depends the current element
	 *
	 * @param	elements	array of new parent elements
	 * @return	bool	always true
	 */
	function saveParentElements($elements) {
		$return = true;
		// Get current parent elements.
		$currentElements = $this->getParentElements();
		// Remove parent elements no longer present.
		foreach ($currentElements as $element) {
			if (!in_array($element, $elements)) {
				if (!$this->_removeParentElement($element)) {
					$return = false;
				}
			}
		}
		// Add missing required fields.
		foreach ($elements as $element) {
			if (!in_array($element, $currentElements)) {
				if (!$this->_addParentElement($element)) {
					$return = false;
				}
			}
		}
		return $return;
	}

	function _addParentElement($ParentElementId) {
		$res = db_query_params ('INSERT INTO artifact_extra_field_elements_dependencies
				(parent_element_id, child_element_id)
				VALUES ($1, $2)',
				array($ParentElementId,
						$this->getID()));
		if (!$res) {
			$this->setError(sprintf(_('Unable to add Parent Element %s for Child Element %s'), $ParentElementId, $this->getID())._(':').' '.db_error());
			return false;
		}
		return true;
	}

	function _removeParentElement($ParentElementId) {
		$res = db_query_params ('DELETE FROM artifact_extra_field_elements_dependencies
				WHERE parent_element_id=$1 AND child_element_id=$2',
				array($ParentElementId,
						$this->getID()));
		if (!$res) {
			$this->setError(sprintf(_('Unable to remove Parent Element %s for Child Element %s'), $ParentElementId, $this->getID())._(':').' '.db_error());
			return false;
		}
		return true;
	}

	/**
	 * update - update rows in the table used to store the choices
	 * for a selection box. This function is used only for extra
	 * boxes and fields configured by the admin
	 *
	 * @param	string	$name		Name of the choice in a box.
	 * @param	int	$status_id	Optional for status box - maps to either open/closed.
	 * @param	boolean	$is_default	Set this element as default value
	 * @return	bool	success.
	 */
	function update($name, $status_id=0, $auto_assign_to=100, $is_default=false) {
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
			SET element_name=$1, status_id=$2, auto_assign_to=$3
			WHERE element_id=$4',
					   array (htmlspecialchars($name),
						  $status_id,
						  $auto_assign_to,
						  $this->getID())) ;
		if (!$result || db_affected_rows($result) == 0) {
			$this->setError(db_error());
			return false;
		}

		$default = db_query_params ('SELECT 1 FROM artifact_extra_field_default WHERE default_value=$1',
				array ($this->getID())) ;
		if (!$default) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraFieldElement ID');
			return false;
		}
		if (db_numrows($default) >= 1 && !$is_default) {
			$result = db_query_params ('DELETE FROM artifact_extra_field_default WHERE extra_field_id = $1 AND default_value = $2',
					array ($this->ArtifactExtraField->getID(), $this->getID()));
			if (!$result) {
				$this->setError(db_error());
				return false;
			}
		}
		if (db_numrows($default) == 0 && $is_default) {
			if (in_array($this->ArtifactExtraField->getType(), unserialize(ARTIFACT_EXTRAFIELDTYPE_SINGLECHOICETYPE))) {
				$result = db_query_params ('DELETE FROM artifact_extra_field_default WHERE extra_field_id = $1',
						array ($this->ArtifactExtraField->getID()));
				if (!$result) {
					$this->setError(db_error());
					$return = false;
				}
			}
			$result = db_query_params ('INSERT INTO artifact_extra_field_default (extra_field_id, default_value) VALUES ($1,$2)',
					array ($this->ArtifactExtraField->getID(), $this->getID()));
			if (!$result) {
				$this->setError(db_error());
				return false;
			}
		}
		db_free_result($default);
		return true;
	}

	/**
	 * delete - delete the current value.
	 *
	 * @return	boolean	success.
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
		db_query_params ('DELETE FROM artifact_workflow_event WHERE from_value_id=$1 OR to_value_id=$1',
				    array ($this->getID())) ;
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
