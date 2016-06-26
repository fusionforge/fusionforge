<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, Anthony J. Pugliese
 * Copyright 2009, Roland Mas
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';

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
/* reserved for aljeux extension, for merge into FusionForge */
define('ARTIFACT_EXTRAFIELDTYPE_FORMULA',11);
/* reserved for Evolvis extension, for merge into FusionForge */
define('ARTIFACT_EXTRAFIELDTYPE_DATETIME',12);
/* reserved */
define('ARTIFACT_EXTRAFIELDTYPE_DATE',13);
define('ARTIFACT_EXTRAFIELDTYPE_USER',14);

class ArtifactExtraField extends FFError {

	/**
	 * The artifact type object.
	 *
	 * @var	object	$ArtifactType.
	 */
	var $ArtifactType; //object

	/**
	 * Array of artifact data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * @param	$ArtifactType
	 * @param	bool		$data
	 */
	function __construct(&$ArtifactType, $data=false) {
		parent::__construct();

		//was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError(_('Invalid Artifact Type'));
			return;
		}
		//did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactExtraField: '.$ArtifactType->getErrorMessage());
			return;
		}
		$this->ArtifactType =& $ArtifactType;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
			} else {
				$this->fetchData($data);
			}
		}
	}

	/**
	 * create - create a row in the table that stores box names for a
	 * a tracker.  This function is only used to create rows for boxes
	 * configured by the admin.
	 *
	 * @param	string	$name		Name of the extra field.
	 * @param	int	$field_type	The type of field - radio, select, text, textarea
	 * @param	int	$attribute1	For text (size) and textarea (rows)
	 * @param	int	$attribute2	For text (maxlength) and textarea (cols)
	 * @param	int	$is_required	True or false whether this is a required field or not.
	 * @param	string	$alias		Alias for this extra field (optional)
	 * @param	int	$show100	True or false whether the 100 value is displayed or not
	 * @param	string	$show100label	The label used for the 100 value if displayed
	 * @param	string	$description	Description used for help text.
	 * @param	string	$pattern	A regular expression to check the field.
	 * @param	int	$parent		Parent extra field id.
	 * @return	bool	true on success / false on failure.
	 */
	function create($name, $field_type, $attribute1, $attribute2, $is_required = 0, $alias = '', $show100 = true, $show100label = 'none', $description = '', $pattern='', $parent=100) {
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('A field name is required'));
			return false;
		}
		if (!$field_type) {
			$this->setError(_('Type of custom field not selected'));
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
				$this->setError(_('Invalid size/maxlength for text field'));
				return false;
			}
		}
		if ($field_type == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA) {
			if (!$attribute1 || !$attribute2) {
				$this->setError(_('Invalid rows/cols for textarea field'));
				return false;
			}
		} elseif ($field_type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			if ($this->ArtifactType->getCustomStatusField()) {
				$this->setError(_('This Tracker already uses custom statuses'));
				return false;
			}
		}  elseif ($field_type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			$show100label='nobody';
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
		$result = db_query_params ('INSERT INTO artifact_extra_field_list (group_artifact_id, field_name, field_type, attribute1, attribute2, is_required, alias, show100, show100label, description, pattern, parent)
			VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12)',
					   array ($this->ArtifactType->getID(),
							  htmlspecialchars($name),
							  $field_type,
							  $attribute1,
							  $attribute2,
							  $is_required,
							  $alias,
							  $show100,
							  $show100label,
							  $description,
							  $pattern,
							  $parent));

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
							$feedback .= _('Insert Error')._(': ').$ao->getErrorMessage();
							$ao->clearError();
							db_rollback();
							return false;
						}
						if (!$ao->create('Closed', '2')) {
							$feedback .= _('Insert Error')._(': ').$ao->getErrorMessage();
							$ao->clearError();
							db_rollback();
							return false;
						}
					}
				}
			}
			if (strstr(ARTIFACT_EXTRAFIELD_FILTER_INT,$field_type) !== false) {
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
	 * fetchData - re-fetch the data for this ArtifactExtraField from the database.
	 *
	 * @param	int	$id ID of the Box.
	 * @return	boolean	success.
	 */
	function fetchData($id) {
		$this->id=$id;
		$res = db_query_params ('SELECT * FROM artifact_extra_field_list WHERE extra_field_id=$1',
					array ($id)) ;

		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid ArtifactExtraField ID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getArtifactType - get the ArtifactType Object this ArtifactExtraField is associated with.
	 *
	 * @return	object	ArtifactType.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}

	/**
	 * getID - get this ArtifactExtraField ID.
	 *
	 * @return	int	The id #.
	 */
	function getID() {
		return $this->data_array['extra_field_id'];
	}

	/**
	 * getName - get the name.
	 *
	 * @return	string	The name.
	 */
	function getName() {
		return $this->data_array['field_name'];
	}

	/**
	 * getDescription - get the description.
	 *
	 * @return	string	The description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 * getAttribute1 - get the attribute1 field.
	 *
	 * @return	int	The first attribute.
	 */
	function getAttribute1() {
		return $this->data_array['attribute1'];
	}

	/**
	 * getAttribute2 - get the attribute2 field.
	 *
	 * @return	int	The second attribute.
	 */
	function getAttribute2() {
		return $this->data_array['attribute2'];
	}

	/**
	 * getPattern - get the pattern for text field.
	 *
	 * @return	string	The pattern.
	 */
	function getPattern() {
		return $this->data_array['pattern'];
	}

	/**
	 * getParent - get the parent field id for select/multiselect/radio/check field.
	 *
	 * @return	string	The parent.
	 */
	function getParent() {
		return $this->data_array['parent'];
	}

	/**
	 * getChildren - get children fields id for a select/multiselect/radio/check field.
	 *
	 * @return	array	Children.
	 */
	function getChildren() {

		$id = $this->getID();
		$return = array();
		$res = db_query_params ('SELECT extra_field_id FROM artifact_extra_field_list WHERE parent=$1',
				array ($id)) ;
		if (!$res) {
			$this->setError(_('Invalid ArtifactExtraField ID'));
			return $return;
		}
		while ($row = db_fetch_array($res)) {
			$return[] = $row['extra_field_id'];
		}
		return $return;
	}

	/**
	 * getProgeny - get progeny fields id for a select/multiselect/radio/check field.
	 *
	 * @return	array	Progeny.
	 */
	function getProgeny() {
		$return = array();
		$childrenArr = $this->getChildren();
		if (is_array($childrenArr)) {
			$return = $childrenArr;
			$at = $this->ArtifactType;
			foreach ($childrenArr as $child) {
				$childObj = new ArtifactExtraField($at,$child);
				$childProgenyArr = $childObj->getProgeny();
				if (is_array($childProgenyArr)) {
					$return = array_merge($return, $childProgenyArr);
				}
			}
		}
		return $return;
	}

	/**
	 * getShow100 - get the show100 field.
	 *
	 * @return	int	The show100 attribute.
	 */
	function getShow100() {
		return $this->data_array['show100'];
	}

	/**
	 * getShow100label - get the show100label field.
	 *
	 * @return	int	The show100label attribute.
	 */
	function getShow100label() {
		return $this->data_array['show100label'];
	}

	/**
	 * getType - the type of field.
	 *
	 * @return	int	type.
	 */
	function getType() {
		return $this->data_array['field_type'];
	}

	/**
	 * getTypeName - the name of type of field.
	 *
	 * @return	string	type.
	 */
	function getTypeName() {
		$arr = $this->getAvailableTypes();
		return $arr[$this->data_array['field_type']];
	}

	/**
	 * isRequired - whether this field is required or not.
	 *
	 * @return	boolean	required.
	 */
	function isRequired() {
		return $this->data_array['is_required'];
	}

	/**
	 * getAvailableTypes - the types of text fields and their names available.
	 *
	 * @return	array	types.
	 */
	static function getAvailableTypes() {
		return array(
			ARTIFACT_EXTRAFIELDTYPE_SELECT => _('Select Box'),
			ARTIFACT_EXTRAFIELDTYPE_CHECKBOX => _('Check Box'),
			ARTIFACT_EXTRAFIELDTYPE_RADIO => _('Radio Buttons'),
			ARTIFACT_EXTRAFIELDTYPE_TEXT => _('Text Field'),
			ARTIFACT_EXTRAFIELDTYPE_MULTISELECT => _('Multi-Select Box'),
			ARTIFACT_EXTRAFIELDTYPE_TEXTAREA => _('Text Area'),
			ARTIFACT_EXTRAFIELDTYPE_STATUS => _('Status'),
			ARTIFACT_EXTRAFIELDTYPE_RELATION => _('Relation between artifacts'),
			ARTIFACT_EXTRAFIELDTYPE_INTEGER => _('Integer'),
			ARTIFACT_EXTRAFIELDTYPE_USER => _('User Select Box')
			);
	}

	/**
	 * getAlias - the alias that is used for this field
	 *
	 * @return	string	alias
	 */
	function getAlias() {
		return $this->data_array['alias'];
	}

	/**
	 * getAvailableValues - Get the list of available values for this extra field
	 *
	 * @return	array
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
	 * getAllowedValues - Get the list of allowed values for this extra field
	 *
	 * @param	string|array	$parentValues	Id or id list of selected parent values.
	 * @return	array|bool
	 */
	function getAllowedValues($parentValues) {

		$parentId = $this->getParent();
		if ($parentId=='100') {
			return false;
		}
		if ($this->getType() != ARTIFACT_EXTRAFIELDTYPE_SELECT &&
				$this->getType() != ARTIFACT_EXTRAFIELDTYPE_MULTISELECT &&
				$this->getType() != ARTIFACT_EXTRAFIELDTYPE_RADIO &&
				$this->getType() != ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) {
			return false;
		}
		if (empty($parentValues) || $parentValues=='100') {
			return false;
		}

		if (is_array($parentValues)) {
			if (count($parentValues)==1 && implode('',$parentValues)=='100' ) {
				return false;
			}
			$res = db_query_params ('SELECT child_element_id FROM artifact_extra_field_elements
										INNER JOIN artifact_extra_field_elements_dependencies ON child_element_id = element_id
										WHERE extra_field_id=$1 AND parent_element_id IN ($2)',
					array ($this->getID(), implode(', ', $parentValues)));
		} else {
			$res = db_query_params ('SELECT child_element_id FROM artifact_extra_field_elements
										INNER JOIN artifact_extra_field_elements_dependencies ON child_element_id = element_id
										WHERE extra_field_id=$1 AND parent_element_id=$2',
					array ($this->getID(), $parentValues));
		}
		$return = array();
		while ($row = db_fetch_array($res)) {
			$return[] = $row['child_element_id'];
		}
		return $return;
	}

	/**
	 * update - update a row in the table used to store box names
	 * for a tracker.  This function is only to update rowsf
	 * for boxes configured by the admin.
	 *
	 * @param	string	$name		Name of the field.
	 * @param	int	$attribute1	For text (size) and textarea (rows)
	 * @param	int	$attribute2	For text (maxlength) and textarea (cols)
	 * @param	int	$is_required	True or false whether this is a required field or not.
	 * @param	string	$alias		Alias for this field
	 * @param	int	$show100	True or false whether the 100 value is displayed or not
	 * @param	string	$show100label	The label used for the 100 value if displayed
	 * @param	string	$description	Description used for help text.
	 * @param	string	$pattern	A regular expression to check the field.
	 * @param	int	$parent		Parent extra field id.
	 * @return	bool	success.
	 */
	function update($name, $attribute1, $attribute2, $is_required = 0, $alias = "", $show100 = true, $show100label = 'none', $description = '', $pattern='', $parent=100) {
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('A field name is required'));
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
			SET field_name = $1,
			description = $2,
			attribute1 = $3,
			attribute2 = $4,
			is_required = $5,
			alias = $6,
			show100 = $7,
			show100label = $8,
			pattern = $9,
			parent = $10
			WHERE extra_field_id = $11
			AND group_artifact_id = $12',
					   array (htmlspecialchars($name),
							  $description,
							  $attribute1,
							  $attribute2,
							  $is_required,
							  $alias,
							  $show100,
							  $show100label,
							  $pattern,
							  $parent,
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
	 * Validate an alias.
	 * Note that this function does not check for conflicts.
	 *
	 * @param	string	alias - alias to validate
	 * @return	bool	true if alias is valid, false otherwise and it sets the corresponding error
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
			$this->setError(_('The alias contains invalid characters. Only letters, numbers, hyphens (-), at sign (@) and underscores (_) allowed.'));
			return false;
		} elseif (in_array($alias, $reserved_alias)) {	// alias is reserved?
			$this->setError(sprintf(_('“%s” is a reserved alias. Please provide another name.'), $alias));
			return false;
		}

		return true;
	}

	/**
	 * Generate an alias for this field. The alias can be entered by the user or
	 * be generated automatically from the name of the field.
	 *
	 * @param	string	$alias	Alias entered by the user
	 * @param	string	$name	Name of the field entered by the user (it'll be used when $alias is empty)
	 * @return	string
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
			} elseif (db_numrows($res) > 0) {		// found another field with the same alias
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
		$data = array();
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
