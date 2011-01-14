<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, Anthony J. Pugliese
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';

define('ARTIFACT_EXTRAFIELD_FILTER_INT','1,2,3,5,7');
define('ARTIFACT_EXTRAFIELDTYPE_SELECT',1);
define('ARTIFACT_EXTRAFIELDTYPE_CHECKBOX',2);
define('ARTIFACT_EXTRAFIELDTYPE_RADIO',3);
define('ARTIFACT_EXTRAFIELDTYPE_TEXT',4);
define('ARTIFACT_EXTRAFIELDTYPE_MULTISELECT',5);
define('ARTIFACT_EXTRAFIELDTYPE_TEXTAREA',6);
define('ARTIFACT_EXTRAFIELDTYPE_STATUS',7);
//define('ARTIFACT_EXTRAFIELDTYPE_ASSIGNEE',8);
define('ARTIFACT_EXTRAFIELDTYPE_RELATION',9);
define('ARTIFACT_EXTRAFIELDTYPE_INTEGER',10);

class ArtifactExtraField extends Error {

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
	 *	ArtifactExtraField - Constructer
	 *
	 *	@param	object	ArtifactType object.
	 *  @param	array	(all fields from artifact_file_user_vw) OR id from database.
	 *  @return	boolean	success.
	 */
	function ArtifactExtraField(&$ArtifactType, $data=false) {
		$this->Error(); 

		//was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactExtraField: No Valid ArtifactType');
			return false;
		}
		//did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactExtraField: '.$ArtifactType->getErrorMessage());
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
	 *	create - create a row in the table that stores box names for a
	 *	a tracker.  This function is only used to create rows for boxes 
	 *	configured by the admin.
	 *
	 *	@param	string	Name of the extra field.
	 *	@param	int	The type of field - radio, select, text, textarea
	 *	@param	int	Attribute1 - for text (size) and textarea (rows)
	 *	@param	int	Attribute2 - for text (maxlength) and textarea (cols)
	 *	@param	int	is_required - true or false whether this is a required field or not.
	 *	@param	string	alias - alias for this extra field (optional)
	 *  @return 	true on success / false on failure.
	 */
	function create($name,$field_type,$attribute1,$attribute2,$is_required=0,$alias='') {
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('a field name is required'));
			return false;
		}
		if (!$field_type) {
			$this->setError("Type of custom field not selected");
			return false;			
		}
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		$res = db_query_params ('SELECT field_name FROM artifact_extra_field_list WHERE field_name=$1 AND group_artifact_id=$2',
					array($name,
					      $this->ArtifactType->getID()));
		if (db_numrows($res) > 0) {
			$this->setError(_('Field name already exists'));
			return false;
		}
		if ($field_type == ARTIFACT_EXTRAFIELDTYPE_TEXT || $field_type == ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
			if (!$attribute1 || !$attribute2 || $attribute2 < $attribute1) {
				$this->setError("Invalid size/maxlength for text field");
				return false;
			}
		}
		if ($field_type == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {
			if (!$attribute1 || !$attribute2) {
				$this->setError("Invalid rows/cols for textarea field");
				return false;
			}
		}
		
		if ($is_required) {
			$is_required=1;
		} else {
			$is_required=0;
		}	
		
		if (!($alias = $this->generateAlias($alias,$name))) {
			return false;
		}
		
		db_begin();
		$result = db_query_params ('INSERT INTO artifact_extra_field_list (group_artifact_id,field_name,field_type,attribute1,attribute2,is_required,alias) 
			VALUES ($1,$2,$3,$4,$5,$6,$7)',
					   array ($this->ArtifactType->getID(),
						  htmlspecialchars($name),
						  $field_type,
						  $attribute1,
						  $attribute2,
						  $is_required,
						  $alias));

		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			$id=db_insertid($result,'artifact_extra_field_list','extra_field_id');
			//
			//	Now set up our internal data structures
			//
			if (!$this->fetchData($id)) {
				db_rollback();
				return false;
			}
			if ($field_type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				if (!$this->ArtifactType->setCustomStatusField($id)) {
					db_rollback();
					return false;
				} else {
					//
					//	Must insert some default statuses for each artifact
					//
					$ao = new ArtifactExtraFieldElement($this);
					if (!$ao || !is_object($ao)) {
						$feedback .= 'Unable to create ArtifactExtraFieldElement Object';
						db_rollback();
						return false;
					} else {
						if (!$ao->create('Open', '1')) {
							$feedback .= _('Error inserting an element').': '.$ao->getErrorMessage();
							$ao->clearError();
							db_rollback();
							return false;
						}
						if (!$ao->create('Closed', '2')) {
							$feedback .= _('Error inserting an element').': '.$ao->getErrorMessage();
							$ao->clearError();
							db_rollback();
							return false;
						}
					}
				}
			} elseif (strstr(ARTIFACT_EXTRAFIELD_FILTER_INT,$field_type) !== false) {
//
//	Must insert some default 100 rows for the data table so None queries will work right
//
				$resdefault = db_query_params ('INSERT INTO artifact_extra_field_data(artifact_id,field_data,extra_field_id) 
					SELECT artifact_id,100,$1 FROM artifact WHERE group_artifact_id=$2',
							       array ($id,
								      $this->ArtifactType->getID())) ;
				if (!$resdefault) {
					echo db_error();
				}
			}
			db_commit();
			return $id;
		} else {
			$this->setError(db_error());
			db_rollback();
			return false;
		}
	}

	/**
	 *	fetchData - re-fetch the data for this ArtifactExtraField from the database.
	 *
	 *	@param	int		ID of the Box.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$this->id=$id;
		$res = db_query_params ('SELECT * FROM artifact_extra_field_list WHERE extra_field_id=$1',
					array ($id)) ;
		
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactExtraField: Invalid ArtifactExtraField ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getArtifactType - get the ArtifactType Object this ArtifactExtraField is associated with.
	 *
	 *	@return object	ArtifactType.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}
	
	/**
	 *	getID - get this ArtifactExtraField ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['extra_field_id'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	string	The name.
	 */
	function getName() {
		return $this->data_array['field_name'];
	}

	/**
	 *	getAttribute1 - get the attribute1 field.
	 *
	 *	@return int	The first attribute.
	 */
	function getAttribute1() {
		return $this->data_array['attribute1'];
	}

	/**
	 *	getAttribute2 - get the attribute2 field.
	 *
	 *	@return int	The second attribute.
	 */
	function getAttribute2() {
		return $this->data_array['attribute2'];
	}

	/**
	 *	getType - the type of field.
	 *
	 *	@return	int	type.
	 */
	function getType() {
		return $this->data_array['field_type'];
	}

	/**
	 *	getTypeName - the name of type of field.
	 *
	 *	@return	string	type.
	 */
	function getTypeName() {
		$arr = $this->getAvailableTypes();
		return $arr[$this->data_array['field_type']];
	}

	/**
	 *	isRequired - whether this field is required or not.
	 *
	 *	@return	boolean required.
	 */
	function isRequired() {
		return $this->data_array['is_required'];
	}

	/**
	 *	getAvailableTypes - the types of text fields and their names available.
	 *
	 *	@return	array	types.
	 */
	static function getAvailableTypes() {
		return array(
			1=>_('Select Box'),
			2=>_('Check Box'),
			3=>_('Radio Buttons'),
			4=>_('Text Field'),
			5=>_('Multi-Select Box'),
			6=>_('Text Area'),
			7=>_('Status'),
			9=>_('Relation'),
			10=>_('Integer')
			);
	}
	
	/**
	 *	getAlias - the alias that is used for this field
	 *
	 *	@return	string	alias
	 */
	function getAlias() {
		return $this->data_array['alias'];
	}
	
	/**
	 *	getAvailableValues - Get the list of available values for this extra field
	 *
	 *	@return array
	 */
	function getAvailableValues() {
		$res = db_query_params ('SELECT * FROM artifact_extra_field_elements WHERE extra_field_id=$1',
					array ($this->getID()));
		$return = array();
		while ($row = db_fetch_array($res)) {
			$return[] = $row;
		}
		return $return;
	}

	/**
	 *  update - update a row in the table used to store box names 
	 *  for a tracker.  This function is only to update rowsf
	 *  for boxes configured by
	 *  the admin.
	 *
	 *  @param	string	Name of the field.
	 *	@param	int	Attribute1 - for text (size) and textarea (rows)
	 *	@param	int	Attribute2 - for text (maxlength) and textarea (cols)
	 *	@param	int	is_required - true or false whether this is a required field or not.
	 *	@param	string	Alias for this field
	 *  @return	boolean	success.
	 */
	function update($name,$attribute1,$attribute2,$is_required=0,$alias="") {
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('a field name is required'));
			return false;
		}
		$res = db_query_params ('SELECT field_name FROM artifact_extra_field_list 
				WHERE field_name=$1 AND group_artifact_id=$2 AND extra_field_id !=$3',
			array($name,
				$this->ArtifactType->getID(),
				$this->getID()));
		if (db_numrows($res) > 0) {
			$this->setError(_('Field name already exists'));
			return false;
		}
		if ($is_required) {
			$is_required=1;
		} else {
			$is_required=0;
		}
		
		if (!($alias = $this->generateAlias($alias,$name))) {
			return false;
		}		

		$result = db_query_params ('UPDATE artifact_extra_field_list 
			SET field_name=$1,
			attribute1=$2,
			attribute2=$3,
			is_required=$4,
			alias=$5
			WHERE extra_field_id=$6
			AND group_artifact_id=$7',
					   array (htmlspecialchars($name),
						  $attribute1,
						  $attribute2,
						  $is_required,
						  $alias,
						  $this->getID(),
						  $this->ArtifactType->getID())) ;
		if ($result && db_affected_rows($result) > 0) {
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}

	/**
	 *
	 *
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();
		$result = db_query_params ('DELETE FROM artifact_extra_field_data WHERE extra_field_id=$1',
					   array ($this->getID())) ;
		if ($result) {
			$result = db_query_params ('DELETE FROM artifact_extra_field_elements WHERE extra_field_id=$1',
						   array ($this->getID())) ;
			if ($result) {
				$result = db_query_params ('DELETE FROM artifact_extra_field_list WHERE extra_field_id=$1',
							   array ($this->getID())) ;
				if ($result) {
					if ($this->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
						if (!$this->ArtifactType->setCustomStatusField(0)) {
							db_rollback();
							return false;
						}
					}
					db_commit();
					return true;
				} else {
					$this->setError(db_error());
					db_rollback();
					return false;
				}
			} else {
				$this->setError(db_error());
				db_rollback();
				return false;
			}
		} else {
			$this->setError(db_error());
			db_rollback();
			return false;
		}

	}
	
	/**
	 * 	Validate an alias.
	 *	Note that this function does not check for conflicts.
	 *	@param	string	alias - alias to validate
	 *	@return	bool	true if alias is valid, false otherwise and it sets the corresponding error
	 */
	function validateAlias($alias) {
		// these are reserved alias names
		static $reserved_alias = array(
			"project",
			"priority",
			"assigned_to",
			"submitted_by",
			"open_date",
			"close_date",
			"summary",
			"details",
			"last_modified_date"
		);

		if (strlen($alias) == 0) return true;		// empty alias

		// invalid chars?
		if (preg_match("/[^[:alnum:]_@\\-]/", $alias)) {
			$this->setError(_('The alias contains invalid characters. Only letters, numbers, hypens (-), arobase (@) and underscores (_) allowed.'));
			return false;
		} else if (in_array($alias, $reserved_alias)) {	// alias is reserved?
			$this->setError(sprintf(_('\'%1$s\' is a reserved alias. Please provide another name.'), $alias));
			return false;
		}
		
		return true;
	}
	
	/**
	 *	Generate an alias for this field. The alias can be entered by the user or
	 *	be generated automatically from the name of the field.
	 *	@param	string	Alias entered by the user
	 *	@param	string	Name of the field entered by the user (it'll be used when $alias is empty)
	 *	@return	string
	 */
	function generateAlias($alias, $name) {
		$alias = strtolower(trim($alias));
		if (strlen($alias) == 0) {		// no alias was entered, generate alias from $name
			$name = strtolower(trim($name));
			// Convert the original name to a valid alias (i.e., if the extra field is 
			// called "Quality test", make an alias called "quality_test").
			// The alias can be seen as a "unix name" for this field
			$alias = preg_replace("/ /", "_", $name);
			$alias = preg_replace("/[^[:alnum:]_@]/", "", $alias);
			$alias = strtolower($alias);
		} elseif (!$this->validateAlias($alias)) {
			// alias is invalid...
			return false;
		} 
		// check if the name conflicts with another alias in the same artifact type
		// in that case append a serial number to the alias
		$serial = 1;
		$conflict = false;	
		do {
			if ($this->data_array['extra_field_id']) {
				$res = db_query_params ('SELECT * FROM artifact_extra_field_list
                                                         WHERE LOWER (alias)=$1
                                                         AND group_artifact_id=$2
                                                         AND extra_field_id <> $3',
							array ($alias,
							       $this->ArtifactType->getID(),
							       $this->data_array['extra_field_id'])) ;
			} else {
				$res = db_query_params ('SELECT * FROM artifact_extra_field_list WHERE LOWER (alias)=$1 AND group_artifact_id=$2',
							array ($alias,
							       $this->ArtifactType->getID()));
			}
			if (!$res) {
				$this->setError(db_error());
				return false;
			} else if (db_numrows($res) > 0) {		// found another field with the same alias
				$conflict = true;
				$serial++;
				$alias = $alias.$serial;
			} else {
				$conflict = false;
			}
		} while ($conflict);

		// at this point, the alias is valid and unique
		return $alias;
	}

	function updateOrder($element_id, $order) {

		$result=db_query_params ('UPDATE artifact_extra_field_elements
				SET element_pos= $1
				WHERE element_id=$2',
			array($order,
				$element_id));
		if ($result && db_affected_rows($result) > 0) {
			return true;
		}
		else {
			$this->setError(db_error());
			return false;
		}
	}

	function reorderValues($element_id, $new_pos) {

		$res = db_query_params ('SELECT element_id FROM artifact_extra_field_elements WHERE extra_field_id=$1 ORDER BY element_pos ASC, element_id ASC',
			array($this->getID()));
		$max = db_numrows($res);
		if ($new_pos < 1 || $new_pos > $max) {
			$this->setError(_('Out of range value'));
			return false;
		}
		$i = 1;
		while ($i <= $max) {
			if ($i == $new_pos) {
				$data[] = $element_id;
				$i++;
			}
			if (($row = db_fetch_array($res)) && $row['element_id'] != $element_id) {
				$data[] = $row['element_id'];
				$i++;
			}
		}
		for ($i = 0; $i < count($data); $i++) {
			if (! $this->updateOrder($data[$i], $i + 1))
				return false;
		}

		return true;
	}

	function alphaorderValues() {

		$res = db_query_params ('SELECT element_id FROM artifact_extra_field_elements WHERE extra_field_id=$1 ORDER BY element_name ASC',
			array($this->getID()));
		$i = 1;
		while ($row = db_fetch_array($res)) {
			if (! $this->updateOrder($row['element_id'], $i))
				return false;
			$i++;
		}

		return true;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
