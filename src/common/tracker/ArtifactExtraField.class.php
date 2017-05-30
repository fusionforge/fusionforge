<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, Anthony J. Pugliese
 * Copyright 2009, Roland Mas
 * Copyright 2014,2017, Franck Villaume - TrivialDev
 * Copyright 2016-2017, Stéphane-Eymeric Bredthauer - TrivialDev
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
/* 13: reserved SLA */
define('ARTIFACT_EXTRAFIELDTYPE_SLA',13);
define('ARTIFACT_EXTRAFIELDTYPE_USER',14);
define('ARTIFACT_EXTRAFIELDTYPE_MULTIUSER',15);
define('ARTIFACT_EXTRAFIELDTYPE_RELEASE',16);
define('ARTIFACT_EXTRAFIELDTYPE_MULTIRELEASE',17);
define('ARTIFACT_EXTRAFIELDTYPE_DATE',18);
define('ARTIFACT_EXTRAFIELDTYPE_DATETIMERANGE', 19);
define('ARTIFACT_EXTRAFIELDTYPE_DATERANGE', 20);
define('ARTIFACT_EXTRAFIELDTYPE_EFFORT',21);
define('ARTIFACT_EXTRAFIELDTYPE_EFFORTRANGE',22);
define('ARTIFACT_EXTRAFIELDTYPE_PARENT',23);

define ('ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE', serialize (array (ARTIFACT_EXTRAFIELDTYPE_SELECT, ARTIFACT_EXTRAFIELDTYPE_RADIO, ARTIFACT_EXTRAFIELDTYPE_STATUS)));
define ('ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE', serialize (array (ARTIFACT_EXTRAFIELDTYPE_CHECKBOX, ARTIFACT_EXTRAFIELDTYPE_MULTISELECT)));
define ('ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE', serialize (array_merge(unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE), unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))));
define ('ARTIFACT_EXTRAFIELDTYPEGROUP_SPECALCHOICE', serialize(array(ARTIFACT_EXTRAFIELDTYPE_USER, ARTIFACT_EXTRAFIELDTYPE_RELEASE)));
define ('ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE', serialize (array (ARTIFACT_EXTRAFIELDTYPE_TEXT,ARTIFACT_EXTRAFIELDTYPE_TEXTAREA,ARTIFACT_EXTRAFIELDTYPE_RELATION,ARTIFACT_EXTRAFIELDTYPE_INTEGER,ARTIFACT_EXTRAFIELDTYPE_FORMULA,ARTIFACT_EXTRAFIELDTYPE_DATETIME, ARTIFACT_EXTRAFIELDTYPE_EFFORT)));

define ('ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_NO_AGGREGATION', 0);
define ('ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_SUM', 1);
define ('ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_STATUS_CLOSE_RESTRICTED', 2);
define ('ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_STATUS_CLOSE_UPWARDS', 3);

define ('ARTIFACT_EXTRAFIELD_DISTRIBUTION_RULE_NO_DISTRIBUTION', 0);
define ('ARTIFACT_EXTRAFIELD_DISTRIBUTION_RULE_STATUS_CLOSE_RECURSIVELY', 1);

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
	 * @param	string	$name			Name of the extra field.
	 * @param	int	$field_type		The type of field - radio, select, text, textarea
	 * @param	int	$attribute1		For text (size) and textarea (rows)
	 * @param	int	$attribute2		For text (maxlength) and textarea (cols)
	 * @param	int	$is_required		True or false whether this is a required field or not.
	 * @param	string	$alias			Alias for this extra field (optional)
	 * @param	int	$show100		True or false whether the 100 value is displayed or not
	 * @param	string	$show100label		The label used for the 100 value if displayed
	 * @param	string	$description		Description used for help text.
	 * @param	string	$pattern		A regular expression to check the field.
	 * @param	int	$parent			Parent extra field id.
	 * @param	int	$autoassign		True or false whether it triggers auto-assignment rules
	 * @param	int	$is_hidden_on_submit	True or false to display the extrafield in the new artifact submit page
	 * @param	int	$disabled		True or false to enable/disable the extrafield
	 * @return	bool	true on success / false on failure.
	 */
	function create($name, $field_type, $attribute1, $attribute2, $is_required = 0, $alias = '', $show100 = true, $show100label = 'none', $description = '', $pattern = '', $parent = 100, $autoassign = 0, $is_hidden_on_submit = 0, $is_disabled = 0, $aggregation_rule = 0, $distribution_rule = 0) {
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
		}  elseif ($field_type == ARTIFACT_EXTRAFIELDTYPE_USER) {
			$show100label='nobody';
		}
		$is_required = ($is_required ? 1 : 0);
		$autoassign = ($autoassign ? 1 : 0);
		$is_hidden_on_submit = ($is_hidden_on_submit ? 1 : 0);
		$is_disabled = ($is_disabled ? 1 : 0);

		if (!($alias = $this->generateAlias($alias,$name))) {
			$this->setError(_('Unable to generate alias'));
			return false;
		}

		db_begin();
		$result = db_query_params ('INSERT INTO artifact_extra_field_list (group_artifact_id, field_name, field_type, attribute1, attribute2, is_required, alias, show100, show100label, description, pattern, parent, is_hidden_on_submit, is_disabled, aggregation_rule, distribution_rule)
			VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15,$16)',
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
							  $parent,
							  $is_hidden_on_submit,
							  $is_disabled,
							  $aggregation_rule,
							  $distribution_rule));

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
			if ($autoassign && $this->setAutoAssign()) {
				$this->setError(_('Unable to set Auto Assign Field')._(':').db_error());
				return false;
			}
			if ($field_type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				if (!$this->ArtifactType->setCustomStatusField($id)) {
					$this->setError(_('Unable to set Custom Status Field')._(':').db_error());
					db_rollback();
					return false;
				} else {
					//
					//	Must insert some default statuses for each artifact
					//
					$ao = new ArtifactExtraFieldElement($this);
					if (!$ao || !is_object($ao)) {
						$this->setError(_('Unable to create ArtifactExtraFieldElement Object'));
						db_rollback();
						return false;
					} else {
						if (!$ao->create('Open', '1')) {
							$this->setError(_('Insert Error')._(': ').$ao->getErrorMessage());
							$ao->clearError();
							db_rollback();
							return false;
						}
						if (!$ao->create('Closed', '2')) {
							$this->setError(_('Insert Error')._(': ').$ao->getErrorMessage());
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
	 * isHiddenOnSubmit - whether this field is hidden on a new submission or not.
	 *
	 * @return	boolean	required.
	 */
	function isHiddenOnSubmit() {
		return $this->data_array['is_hidden_on_submit'];
	}

	/**
	 * isDisabled - whether this field is disabled or not.
	 *
	 * @return	boolean	required.
	 */
	function isDisabled() {
		return $this->data_array['is_disabled'];
	}

	/**
	 * isAutoAssign
	 *
	 * @return	boolean	assign.
	 */
	function isAutoAssign() {
		if ($this->getArtifactType()->getAutoAssignField() == $this->getID()) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * setAutoAssign - set this field that triggers auto-assignment rules.
	 *
	 * @return	boolean
	 */
	function setAutoAssign() {
		return $this->getArtifactType()->setAutoAssignField($this->getID());
	}

	/**
	 * unsetAutoAssign - unset this field that triggers auto-assignment rules.
	 *
	 * @return	boolean
	 */
	function unsetAutoAssign() {
		return $this->getArtifactType()->setAutoAssignField(100);
	}

	/**
	 * setDefaultValues - set default value(s) for this field.
	 *
	 * @param	string|integer|array	$default	default value, default id value, or array of default values.
	 * @return	boolean
	 */
	function setDefaultValues($default) {
		$type = $this->getType();
		$return = true;
		if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
			if (is_array($default)) {
				$efValues = $this->getAvailableValues();
				$oldDefault = $this->getDefaultValues();
				if (!is_array($oldDefault)) {
					if (is_null($oldDefault)) {
						$oldDefault = array();
					} else {
						$oldDefault = array($oldDefault);
					}
				}
				$efID = $this->getID();
				if ($this->getShow100()) {
					$efValues [] = array('element_id'=>100);
				}
				foreach ($efValues as $efValue) {
					$value = $efValue['element_id'];
					if (in_array($value, $default) && !in_array($value, $oldDefault)) {
						$res = db_query_params ('INSERT INTO artifact_extra_field_default (extra_field_id, default_value) VALUES ($1,$2)',
								array ($efID, $value)) ;
						if (!$res) {
							$this->setError(_('Unable to set default values')._(':').' '.db_error());
							$return = false;
						}
					} elseif (!in_array($value, $default) && in_array($value, $oldDefault)) {
						$res = db_query_params ('DELETE FROM artifact_extra_field_default WHERE extra_field_id=$1 AND default_value=$2',
								array ($efID, $value)) ;
						if (!$res) {
							$this->setError(_('Unable to set default values')._(':').' '.db_error());
							$return = false;
						}
					}
				}
			} elseif (is_integer($default)) {
				$efe = new ArtifactExtraFieldElement($this, $default);
				if (!$efe || !is_object($efe)) {
					if (is_object($efe)) {
						$this->setError(_('Unable to create extra field element').' (id='.$default.') '._(':').$efe->getErrorMessage());
					} else {
						$this->setError(_('Unable to create extra field element').' (id='.$default.')');
					}
					$return = false;
				} elseif (!$efe->setAsDefault(true)) {
					$this->setError(_('Unable to update extra field element').' '.$efe->getName().' '._(':').$efe->getErrorMessage());
					$return = false;
				}
			} else {
				$this->setError(_('Unable to set default value')._(':').$default);
				$return = false;
			}
		} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
			if (is_integer($default)) {
				if ($default == '100') {
					$return = $this->resetDefaultValues();
				} else {
					$efe = new ArtifactExtraFieldElement($this, $default);
					if (!$efe || !is_object($efe)) {
						if (is_object($efe)) {
							$this->setError(_('Unable to create extra field element').' (id='.$default.') '._(':').$efe->getErrorMessage());
						} else {
							$this->setError(_('Unable to create extra field element').' (id='.$default.')');
						}
						$return = false;
					} elseif (!$efe->setAsDefault(true)) {
						$this->setError(_('Unable to update extra field element').' '.$efe->getName().' '._(':').$efe->getErrorMessage());
						$return = false;
					}
				}
			} else {
				$this->setError(_('Unable to set default value')._(':').$default);
				$return = false;
			}
		} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE)) || in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SPECALCHOICE))) {
			if (in_array($type,unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SPECALCHOICE)) && $default=='100' ||
					$type==ARTIFACT_EXTRAFIELDTYPE_INTEGER && $default=='0' ||
					$type==ARTIFACT_EXTRAFIELDTYPE_TEXT && trim($default)=='' ||
					$type==ARTIFACT_EXTRAFIELDTYPE_TEXTAREA && trim($default)=='' ||
					$type==ARTIFACT_EXTRAFIELDTYPE_EFFORT && intVal($default)==0) {
				$return = $this->resetDefaultValues();
			} else {
				$efID = $this->getID();
				$res = db_query_params ('SELECT default_value FROM artifact_extra_field_default WHERE extra_field_id=$1',
						array ($efID)) ;
				if (db_numrows($res) > 0) {
					$res = db_query_params ('UPDATE artifact_extra_field_default SET default_value = $1 WHERE extra_field_id=$2',
							array ($default, $efID));
				} else {
					$res = db_query_params ('INSERT INTO artifact_extra_field_default (extra_field_id, default_value) VALUES ($1,$2)',
							array ($efID, $default)) ;
				}
				if (!$res) {
					$this->setError(db_error());
					$return = false;
				}
			}
		}
		return $return;
	}

	/**
	 * resetDefaultValues - reset default value(s) for this field.
	 *
	 * @return	boolean
	 */
	function resetDefaultValues() {
		$result = db_query_params ('DELETE FROM artifact_extra_field_default WHERE extra_field_id = $1',
				array ($this->getID()));
		if (!$result) {
			$this->setError(db_error());
			$return = false;
		} else {
			$return = true;
		}
		return $return;
	}

	/**
	 * getDefaultValues - Get default value, id of default value or list of id of default values for this extra field
	 *
	 * @return	string|integer|array
	 */
	function getDefaultValues() {
		$return = false;
		$res = db_query_params ('SELECT default_value FROM artifact_extra_field_default WHERE extra_field_id=$1',
				array ($this->getID()));
		$type = $this->getType();
		if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE))) {
			$row = db_fetch_array($res);
			$return = $row['default_value'];
			if (is_null($return) && $type == ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
					$return = 0;
			}
		} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_USER || $type == ARTIFACT_EXTRAFIELDTYPE_RELEASE) {
			$row = db_fetch_array($res);
			if (!$row) {
				if ($this->getShow100()) {
					$return = 100;
				} else {
					$return = null;
				}
			} else {
				$return = (integer)$row['default_value'];
			}
		} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
			$row = db_fetch_array($res);
			if (!$row) {
				if ($this->getShow100()) {
					$return = 100;
				} else {
					$return = null;
				}
			} else {
				$return = (integer)$row['default_value'];
			}
		} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
			$return = array();
			while ($row = db_fetch_array($res)) {
				$return[] = $row['default_value'];
			}
			if (empty($return)) {
				$return = null;
			}
		}
		return $return;
	}

	/**
	 * getFormula - Get formula(s) to calculate field
	 *
	 * @return	string|array
	 */
	function getFormula() {
		$return = false;
		$res = db_query_params ('SELECT id, formula FROM artifact_extra_field_formula WHERE extra_field_id=$1',
				array ($this->getID()));
		$type = $this->getType();
		if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE))) {
			if (db_numrows($res) > 0) {
				$row = db_fetch_array($res);
				$return = $row['formula'];
			} else {
				$return ='';
			}
		} elseif (in_array($type, array_merge(unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SPECALCHOICE), unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE)))) {
			$return = array();
			while ($row = db_fetch_array($res)) {
				$return[$row['id']] = $row['formula'];
			}
		}
		return $return;
	}

	/**
	 * setFormula - set formula to calculate field value
	 *
	 * @param	string	$formula	formula
	 * @return	string|array
	 */
	function setFormula($formula) {
		$formula = trim($formula);
		$return = true;
		if ($formula=='') {
			$this->resetFormula();
		} else {
			$type = $this->getType();
			$efID = $this->getID();
			$res = db_query_params ('SELECT id, formula FROM artifact_extra_field_formula WHERE extra_field_id=$1',
					array ($efID)) ;
			if (db_numrows($res) > 0) {
				$res = db_query_params ('UPDATE artifact_extra_field_formula SET formula = $1 WHERE extra_field_id=$2',
						array ($formula, $efID));
			} else {
				$res = db_query_params ('INSERT INTO artifact_extra_field_formula (extra_field_id, formula) VALUES ($1,$2)',
						array ($efID, $formula)) ;
			}
			if (!$res) {
				$this->setError(db_error());
				$return = false;
			}
		}
		return $return;
	}

	/**
	 * resetFormula - reset formula
	 *
	 * @return	boolean
	 */
	function resetFormula() {
		$result = db_query_params ('DELETE FROM artifact_extra_field_formula WHERE extra_field_id = $1',
				array ($this->getID()));
		if (!$result) {
			$this->setError(db_error());
			$return = false;
		} else {
			$return = true;
		}
		return $return;
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
			ARTIFACT_EXTRAFIELDTYPE_DATETIME => _('Datetime'),
			ARTIFACT_EXTRAFIELDTYPE_USER => _('User'),
			ARTIFACT_EXTRAFIELDTYPE_RELEASE => _('Release'),
			ARTIFACT_EXTRAFIELDTYPE_EFFORT => _('Effort'),
			ARTIFACT_EXTRAFIELDTYPE_FORMULA => _('Formula'),
			ARTIFACT_EXTRAFIELDTYPE_SLA => _('SLA'),
			ARTIFACT_EXTRAFIELDTYPE_PARENT => _('Parent artifact')
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
		$type = $this->getType();
		if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE))) {
			$return = array();
		} else {
			$res = db_query_params('SELECT *, 0 AS is_default
						FROM artifact_extra_field_elements
						WHERE extra_field_id=$1
						ORDER BY element_pos ASC, element_id ASC',
						array ($this->getID()));
			$default = $this->getDefaultValues();

			$return = array();
			if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
				while ($row = db_fetch_array($res)) {
					if ($row['element_id'] == $default) {
						$row['is_default'] = 1;
					}
					$return[] = $row;
				}
			} elseif (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_MULTICHOICE))) {
				while ($row = db_fetch_array($res)) {
					if (is_array($default) && in_array($row['element_id'], $default)) {
						$row['is_default'] = 1;
					}
					$return[] = $row;
				}
			} elseif ($type == ARTIFACT_EXTRAFIELDTYPE_USER || $type == ARTIFACT_EXTRAFIELDTYPE_RELEASE) {
				while ($row = db_fetch_array($res)) {
					if ($row['element_id'] == $default) {
						$row['is_default'] = 1;
					}
					$return[] = $row;
				}
			} else {
				while ($row = db_fetch_array($res)) {
					if (!is_null($default) && in_array($row['element_id'],$default)) {
						$row['is_default'] = 1;
					}
					$return[] = $row;
				}
			}
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
		if ($this->getType() != ARTIFACT_EXTRAFIELDTYPE_STATUS &&
				$this->getType() != ARTIFACT_EXTRAFIELDTYPE_SELECT &&
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
	 * setRequired - Set the required for this extra field
	 *
	 * @param	int	$required		value of the field.
	 * @param	int	$id				id of the field.
	 */
	function setRequired($required, $id) {
		$res = db_query_params ('UPDATE artifact_extra_field_list SET is_required = $1 WHERE extra_field_id = $2',
				array ($required, $id));
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
	function update($name, $attribute1, $attribute2, $is_required = 0, $alias = "", $show100 = true, $show100label = 'none', $description = '', $pattern = '', $parent = 100, $autoassign = 0, $is_hidden_on_submit = 0, $is_disabled = 0, $aggregation_rule = 0, $distribution_rule = 0) {
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

		$is_required = ($is_required ? 1 : 0);
		$autoassign = ($autoassign ? 1 : 0);
		$is_hidden_on_submit = ($is_hidden_on_submit ? 1 : 0);
		$is_disabled = ($is_disabled ? 1 : 0);
		$parent = ((integer)$parent ? $parent : 100);

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
			parent = $10,
			is_hidden_on_submit = $11,
			is_disabled = $12,
			aggregation_rule = $13,
			distribution_rule = $14
			WHERE extra_field_id = $15
			AND group_artifact_id = $16',
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
							  $is_hidden_on_submit,
							  $is_disabled,
							  $aggregation_rule,
							  $distribution_rule,
							  $this->getID(),
							  $this->ArtifactType->getID())) ;
		if ($result && db_affected_rows($result) > 0) {
			if ($autoassign && !$this->isAutoAssign()) {
				if (!$this->setAutoAssign()) {
					$this->setError(_('Unable to set Auto Assign Field')._(':').db_error());
					return false;
				}
			}
			if (!$autoassign && $this->isAutoAssign()) {
				if (!$this->unsetAutoAssign()) {
					$this->setError(_('Unable to unset Auto Assign Field')._(':').db_error());
					return false;
				}
			}
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
					if ($this->isAutoAssign()) {
						if (!$this->unsetAutoAssign()) {
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
			'assigned_to',
			'close_date',
			'details',
			'id',
			'last_modified_by',
			'last_modified_date',
			'open_date',
			'priority',
			'project', //why???
			'related_tasks',
			'status_id',
			'submitted_by',
			'summary',
			'_votes',
			'_voters',
			'_votage'
		);

		if (strlen($alias) == 0) return true;		// empty alias

		// invalid chars?
		if (preg_match("/[^[:alnum:]_@\\-]/", $alias)) {
			$this->setError(_('The alias contains invalid characters.').' '._('Only letters, numbers, hyphens (-), at sign (@) and underscores (_) allowed.'));
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

	/**
	 *    getMandatoryExtraFields - List of possible user built extra fields
	 *    set up for this artifact type.
	 *
	 * @return array arrays of data;
	 */
	static function getMandatoryExtraFields($atid) {
		$return = array();

		$res = db_query_params('SELECT *
				FROM artifact_extra_field_list
				WHERE group_artifact_id=$1
				AND is_required = 1
				ORDER BY field_type ASC, alias ASC',
				array($atid));

		while ($arr = db_fetch_array($res)) {
			$return[] = $arr;
		}

		return $return;
	}

	/**
	 *    getAllExtraFields - List of possible user built extra fields
	 *    set up for this artifact type.
	 *
	 * @return array arrays of data;
	 */
	static function getAllExtraFields($atid) {
		$return = array();

		$res = db_query_params('SELECT *
				FROM artifact_extra_field_list
				WHERE group_artifact_id=$1
				ORDER BY field_type ASC, alias ASC',
				array($atid));

		while ($arr = db_fetch_array($res)) {
			$return[] = $arr;
		}

		return $return;
	}

	/**
	 *    checkExtraFieldElements - Check the elements for extra fields
	 *    set up for this artifact type.
	 *
	 * @return boolean;
	 */
	static function checkExtraFieldElements($field_id, $element_name) {
		$return = false;

		$result = db_query_params('SELECT element_id
				FROM artifact_extra_field_elements
				WHERE extra_field_id=$1
				AND element_name=$2',
				array($field_id, trim($element_name)));

		if ($result) {
			while ($row = db_fetch_array($result)) {
				if ($row['element_id'] > 0)
					return $row['element_id'];
			}
		}

		return $return;
	}

	/**
	 * getAvailableAggregationRules - the types of text fields and their names available.
	 *
	 * @return	array	rules.
	 */
	function getAvailableAggregationRules() {
		$return = array();
		$return[ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_NO_AGGREGATION] = _('Parent value is not depending on children\'s values');
		$type = $this->getType();

		if ($type == ARTIFACT_EXTRAFIELDTYPE_EFFORT) {
			$return[ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_SUM] = _('Parent value is the sum of children\'s values');
		}

		if ($type == ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
			$return[ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_SUM] = _('Parent value is the sum of children\'s values');
		}

		if ($type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			$return[ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_STATUS_CLOSE_RESTRICTED] = _('Deny closing the parent, as long as not all children have been closed');
			//$return[ARTIFACT_EXTRAFIELD_AGGREGATION_RULE_STATUS_CLOSE_UPWARDS] = _('Close the parent, after the last child has been closed');
		}

		if (count($return)==1) {
			$return = array();
		}
		return $return;
	}

	function getAggregationRule() {
		return $this->data_array['aggregation_rule'];
	}

	/**
	* getAvailableDistributionRules- the types of text fields and their names available.
	*
	* @return	array	rules.
	*/
	function getAvailableDistributionRules() {
		$return = array();
		$return[ARTIFACT_EXTRAFIELD_DISTRIBUTION_RULE_NO_DISTRIBUTION] = _('Parent value is not depending on children\'s values');
		$type = $this->getType();

		if ($type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
			$return[ARTIFACT_EXTRAFIELD_DISTRIBUTION_RULE_STATUS_CLOSE_RECURSIVELY] = _('Closure of parent involves recursive closure of children');
		}

		if (count($return)==1) {
			$return = array();
		}
		return $return;
	}

	function getDistributionRule() {
		return $this->data_array['distribution_rule'];
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
