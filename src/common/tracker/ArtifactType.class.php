<?php
/**
 * FusionForge trackers
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * Copyright 2014,2016-2017, Franck Villaume - TrivialDev
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
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';
require_once $gfcommon.'tracker/ArtifactStorage.class.php';
require_once $gfcommon.'tracker/EffortUnitSet.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

/**
 * Gets an ArtifactType object from the artifact type id
 *
 * @param	int		$artType_id	The ArtifactType id
 * @param	resource|bool	$res		The DB handle if passed in (optional)
 * @return	ArtifactType	The ArtifactType object
 */
function &artifactType_get_object($artType_id, $res = false) {
	global $ARTIFACTTYPE_OBJ;
	if (!isset($ARTIFACTTYPE_OBJ["_".$artType_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM artifact_group_list_vw WHERE group_artifact_id=$1',
						array($artType_id));
		}
		if (!$res || db_numrows($res) < 1) {
			$ARTIFACTTYPE_OBJ["_".$artType_id."_"] = false;
		} else {
			$data = db_fetch_array($res);
			$Group = group_get_object($data["group_id"]);
			$ARTIFACTTYPE_OBJ["_".$artType_id."_"] = new ArtifactType($Group, $data["group_artifact_id"], $data);
		}
	}
	return $ARTIFACTTYPE_OBJ["_".$artType_id."_"];
}

function artifacttype_get_groupid($artifact_type_id) {
	global $ARTIFACTTYPE_OBJ;
	if (isset($ARTIFACTTYPE_OBJ["_".$artifact_type_id."_"])) {
		return $ARTIFACTTYPE_OBJ["_".$artifact_type_id."_"]->Group->getID();
	}

	$res = db_query_params('SELECT group_id FROM artifact_group_list WHERE group_artifact_id=$1',
		array($artifact_type_id));
	if (!$res || db_numrows($res) < 1) {
		return false;
	}
	$arr = db_fetch_array($res);
	return $arr['group_id'];
}

class ArtifactType extends FFError {

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * extra_fields 3d array - the IDs and Names of the extra fields
	 *
	 * @var	array	extra_fields;
	 */
	var $extra_fields = array();

	/**
	 * extra_field[extra_field_id] array - the IDs and Names of elements on the extra fields
	 *
	 * @var	array	extra_field
	 */
	var $extra_field;

	/**
	 * Technicians db resource ID.
	 *
	 * @var	int	$technicians_res.
	 */
	var $technicians_res;

	/**
	 * Submitters db resource ID.
	 *
	 * @var	int	$submitters_res.
	 */
	var $submitters_res;

	/**
	 * Last Modifiers db resource ID.
	 *
	 * @var	int	$last_modifiers_res.
	 */
	var $last_modifiers_res;

	/**
	 * Status db resource ID.
	 *
	 * @var	int	$status_res.
	 */
	var $status_res;

	/**
	 * Canned responses resource ID.
	 *
	 * @var	int	$canned_responses_res.
	 */
	var $canned_responses_res;

	/**
	 * Array of artifact data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * Array of element names so they only have to be fetched once from db.
	 *
	 * @var	array	 $element_name.
	 */
	var $element_name;

	/**
	 * Array of element status so they only have to be fetched once from db.
	 *
	 * @var	array	$element_status.
	 */
	var $element_status;

	/**
	 * cached return value of getVoters
	 * @var	int|bool	$voters
	 */
	var $voters = false;

	/**
	 * @param	Group		$Group			The Group object.
	 * @param	int|bool	$artifact_type_id	The id # assigned to this artifact type in the db.
	 * @param	array|bool	$arr			The associative array of data.
	 */
	function __construct($Group, $artifact_type_id = false, $arr = false) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('ArtifactType: '.$Group->getErrorMessage());
			return;
		}
		$this->Group = $Group;
		if ($artifact_type_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($artifact_type_id)) {
					return;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Group_id in db result does not match Group Object');
					$this->data_array = null;
					return;
				}
			}
			//
			//  Make sure they can even access this object
			//
			if (!forge_check_perm ('tracker', $this->getID(), 'read')) {
				$this->setPermissionDeniedError();
				$this->data_array = null;
				return;
			}
		}
	}

	/**
	 * create - use this to create a new ArtifactType in the database.
	 *
	 * @param	string	$name			The type name.
	 * @param	string	$description		The type description.
	 * @param	bool	$email_all		(1) true (0) false - whether to email on all updates.
	 * @param	string	$email_address		The address to send new entries and updates to.
	 * @param	int	$due_period		Days before this item is considered overdue.
	 * @param	bool	$use_resolution		(1) true (0) false - whether the resolution box should be shown. //TODO: unused parameter. to be drop!
	 * @param	string	$submit_instructions	Free-form string that project admins can place on the submit page.
	 * @param	string	$browse_instructions	Free-form string that project admins can place on the browse page.
	 * @param	int	$datatype		(1) bug tracker, (2) Support Tracker, (3) Patch Tracker, (4) features (0) other.
	 * @return	int	id on success, false on failure.
	 */
	function create($name, $description, $email_all, $email_address,
					$due_period, $use_resolution, $submit_instructions, $browse_instructions, $datatype = 0) {

		if (!forge_check_perm('tracker_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		if (!$name || !$description || !$due_period) {
			$this->setError(_('ArtifactType')._(': ')._('Name, Description, Due Period, and Status Timeout are required'));
			return false;
		}

		if ($email_address) {
			$invalid_emails = validate_emails($email_address);
			if (count($invalid_emails) > 0) {
				$this->setError(_('E-mail address(es) appeared invalid')._(': ').implode(',', $invalid_emails));
				return false;
			}
		}

		$email_all = ((!$email_all) ? 0 : $email_all);

		db_begin();

		$res = db_query_params('INSERT INTO
			artifact_group_list
			(group_id,
			name,
			description,
			email_all_updates,
			email_address,
			due_period,
			status_timeout,
			submit_instructions,
			browse_instructions,
			datatype,
			unit_set_id)
			VALUES
			($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)',
					array($this->Group->getID(),
							htmlspecialchars($name),
							htmlspecialchars($description),
							$email_all,
							$email_address,
							$due_period*(60*60*24),
							1209600,
							htmlspecialchars($submit_instructions),
							htmlspecialchars($browse_instructions),
							$datatype,
							$this->Group->getEffortUnitSet()
					));

		$id = db_insertid($res, 'artifact_group_list', 'group_artifact_id');

		if (!$res || !$id) {
			$this->setError(_('ArtifactType')._(': ').db_error());
			db_rollback();
			return false;
		} else {
			if (!$this->fetchData($id)) {
				db_rollback();
				return false;
			} else {
				$this->Group->normalizeAllRoles();
				db_commit();
				return $id;
			}
		}
	}

	/**
	 * fetchData - re-fetch the data for this ArtifactType from the database.
	 *
	 * @param	int	$artifact_type_id	The artifact type ID.
	 * @return	boolean	success.
	 */
	function fetchData($artifact_type_id) {
		$this->voters = false;
		$this->extra_field = false;
		$this->extra_fields = false;
		$res = db_query_params('SELECT * FROM artifact_group_list_vw
			WHERE group_artifact_id=$1
			AND group_id=$2',
			array($artifact_type_id,
				$this->Group->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('ArtifactType')._(': ')._('Invalid ArtifactTypeID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getGroup - get the Group object this ArtifactType is associated with.
	 *
	 * @return	Object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getID - get this ArtifactTypeID.
	 *
	 * @return	int	The group_artifact_id #.
	 */
	function getID() {
		return $this->data_array['group_artifact_id'];
	}

	/**
	 * getOpenCount - get the count of open tracker items in this tracker type.
	 *
	 * @return	int	The count.
	 */
	function getOpenCount() {
		return $this->data_array['open_count'];
	}

	/**
	 * getTotalCount - get the total number of tracker items in this tracker type.
	 *
	 * @return	int	The total count.
	 */
	function getTotalCount() {
		return $this->data_array['count'];
	}

	/**
	 * getSubmitInstructions - get the free-form string strings.
	 *
	 * @return	string	instructions.
	 */
	function getSubmitInstructions() {
		return $this->data_array['submit_instructions'];
	}

	/**
	 * getBrowseInstructions - get the free-form string strings.
	 *
	 * @return	string	instructions.
	 */
	function getBrowseInstructions() {
		return $this->data_array['browse_instructions'];
	}

	/**
	 * emailAll - determine if we're supposed to email on every event.
	 *
	 * @return	boolean	email_all.
	 */
	function emailAll() {
		return $this->data_array['email_all_updates'];
	}

	/**
	 * emailAddress - defined email address to send events to.
	 *
	 * @return	string	email.
	 */
	function getEmailAddress() {
		return $this->data_array['email_address'];
	}

	/**
	 * getName - the name of this ArtifactType.
	 *
	 * @return	string	name.
	 */
	function getName() {
		return $this->data_array['name'];
	}

	/**
	 * getFormattedName - formatted name of this ArtifactType
	 *
	 * @return	string	formatted name
	 */
	function getFormattedName() {
		$name = preg_replace('/[^[:alnum:]]/', '', $this->getName());
		$name = strtolower($name);
		return $name;
	}

	/**
	 * getUnixName - returns the name used by email gateway
	 *
	 * @return	string	unix name
	 */
	function getUnixName() {
		return strtolower($this->Group->getUnixName()).'-'.$this->getFormattedName();
	}

	/**
	 * getReturnEmailAddress - return the return email address for notification emails
	 *
	 * @return	string	return email address
	 */
	function getReturnEmailAddress() {

		$address = '';
		if (forge_get_config('use_gateways')) {
			$address .= strtolower($this->getUnixName());
		} else {
			$address .= 'noreply';
		}
		$address .= '@'.forge_get_config('web_host');
		return $address;
	}

	/**
	 * getDescription - the description of this ArtifactType.
	 *
	 * @return	string	description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 * getDuePeriod - how many seconds until it's considered overdue.
	 *
	 * @return	int	seconds.
	 */
	function getDuePeriod() {
		return $this->data_array['due_period'];
	}

	/**
	 * getStatusTimeout - how many seconds until an item is stale.
	 *
	 * @return	int	seconds.
	 */
	function getStatusTimeout() {
		return $this->data_array['status_timeout'];
	}

	/**
	 * getCustomStatusField - return the extra_field_id of the field containing the custom status.
	 *
	 * @return	int	extra_field_id.
	 */
	function getCustomStatusField() {
		return $this->data_array['custom_status_field'];
	}

	/**
	 * setCustomStatusField - set the extra_field_id of the field containing the custom status.
	 *
	 * @param	int	$extra_field_id	The extra field id.
	 * @return	boolean	success.
	 */
	function setCustomStatusField($extra_field_id) {
		$res = db_query_params('UPDATE artifact_group_list SET custom_status_field=$1
			WHERE group_artifact_id=$2',
					array($extra_field_id,
					       $this->getID()));
		$this->fetchData($this->getID());
		return $res;
	}

	/**
	 * getAutoAssignField - get the extra_field_id of the field that triggers auto-assignment rules.
	 *
	 * @return	int	extra_field_id.
	 */
	function getAutoAssignField() {
		return $this->data_array['auto_assign_field'];
	}

	/**
	 * setAutoAssignField - set the extra_field_id of the field that triggers auto-assignment rules.
	 *
	 * @param	int	$extra_field_id	The extra field id.
	 * @return	boolean	success.
	 */
	function setAutoAssignField($extra_field_id) {
		$res = db_query_params('UPDATE artifact_group_list SET auto_assign_field=$1
			WHERE group_artifact_id=$2',
				array($extra_field_id,
				       $this->getID()));
		$this->fetchData($this->getID());
		return $res;
	}

	/**
	 * usesCustomStatuses - boolean
	 *
	 * @return	boolean	use_custom_statues.
	 */
	function usesCustomStatuses() {
		return $this->getCustomStatusField();
	}

	/**
	 * remapStatus - pass the extra_fields array and return the status_id, either open/closed
	 *
	 * @param	int	$status_id	The status_id
	 * @param	array	$extra_fields	Complex array of extra_field_data
	 * @return	int	status_id.
	 */
	function remapStatus($status_id, $extra_fields) {
		if ($this->usesCustomStatuses()) {
			//get the selected element for the extra_field_status element
			$csfield = $this->getCustomStatusField();
			if (array_key_exists($csfield, $extra_fields)) {
				$element_id = $extra_fields[$csfield];

				//convert that element_id into the status_id
				$res = db_query_params('SELECT status_id FROM artifact_extra_field_elements WHERE element_id=$1',
					array($element_id));
				if (!$res) {
					$this->setError(_('Error Remapping Status')._(': ').db_error());
					return false;
				}
				$status_id = db_result($res, 0, 'status_id');
				if ($status_id < 1 || $status_id > 4) {
					$this->setError(sprintf(_('INVALID STATUS REMAP: %d FROM SELECTED ELEMENT: %d'), $status_id, $element_id));
					return false;
				}
			} else {
				// custom status was not passed... use the first status from the database
				$res = db_query_params('SELECT status_id FROM artifact_extra_field_elements WHERE extra_field_id=$1 ORDER BY element_id ASC LIMIT 1 OFFSET 0',
					array($csfield));
				if (db_numrows($res) == 0) { // No values available
					$this->setError(_('Error Remapping Status'));
					return false;
				}
				$status_id = db_result($res, 0, 'status_id');
			}
			return $status_id;
		} else {
			return $status_id;
		}
	}

	/**
	 * getDataType - flag that is generally unused but can mark the difference between bugs, patches, etc.
	 *
	 * @return	int	The type (1) bug (2) support (3) patch (4) feature (0) other.
	 */
	function getDataType() {
		return $this->data_array['datatype'];
	}

	/**
	 * setMonitor - user can monitor this artifact.
	 *
	 * @return	bool	false - always false - always use the getErrorMessage() for feedback
	 */
	function setMonitor($user_id = -1) {
		global $feedback;
		if ($user_id == -1) {
			if (!session_loggedin()) {
				$this->setError(_('You can only monitor if you are logged in.'));
				return false;
			}
			$user_id = user_getid();
		}
		$MonitorElementObject = new MonitorElement('artifact_type');
		if (!$this->isMonitoring()) {
			if (!$MonitorElementObject->enableMonitoringByUserId($this->getID(), $user_id)) {
				$this->setError($MonitorElementObject->getErrorMessage());
				return false;
			}
			$feedback = _('Monitoring Started');
			return true;
		} else {
			if (!$MonitorElementObject->disableMonitoringByUserId($this->getID(), $user_id)) {
				$this->setError($MonitorElementObject->getErrorMessage());
				return false;
			}
			$feedback = _('Monitoring Stopped');
			return true;
		}
	}

	function isMonitoring() {
		if (!session_loggedin()) {
			return false;
		}
		$MonitorElementObject = new MonitorElement('artifact_type');
		return $MonitorElementObject->isMonitoredByUserId($this->getID(), user_getid());
	}

	/**
	 * getMonitorIds - array of id of users monitoring this Artifact.
	 *
	 * @return	array	array of id of users monitoring this Artifact.
	 */
	function getMonitorIds() {
		$MonitorElementObject = new MonitorElement('artifact_type');
		return $MonitorElementObject->getMonitorUsersIdsInArray($this->getID());
	}

	/**
	 * getExtraFields - List of possible user built extra fields
	 * set up for this artifact type.
	 *
	 * @param	array	$types
	 * @param	bool	$get_is_disabled
	 * @param	bool	$get_is_hidden_on_submit
	 * @return	array	arrays of data;
	 */
	function getExtraFields($types = array(), $get_is_disabled = false, $get_is_hidden_on_submit = true) {
		$where ='';
		$use_cache = true;
		if (!$get_is_disabled) {
			$where = ' AND is_disabled = 0';
		} else {
			$use_cache = false;
		}
		if (!$get_is_hidden_on_submit) {
			$where = ' AND is_hidden_on_submit = 0';
			$use_cache = false;
		}
		if (count($types)) {
			$filter = implode(',', $types);
			$types = explode(',', $filter);
		} else {
			$filter = '';
		}
		if (!isset($this->extra_fields[$filter]) || !$use_cache) {
			$extra_fields = array();
			if (count($types)) {
				$res = db_query_params('SELECT *
							FROM artifact_extra_field_list
							WHERE group_artifact_id=$1
							AND field_type = ANY ($2)'.
							$where.' '.
							'ORDER BY field_type ASC',
							array($this->getID(), db_int_array_to_any_clause($types)));
			} else {
				$res = db_query_params('SELECT *
							FROM artifact_extra_field_list
							WHERE group_artifact_id=$1'.
							$where.' '.
							'ORDER BY field_type ASC',
							array($this->getID()));
			}
			while ($arr = db_fetch_array($res)) {
				$extra_fields[$arr['extra_field_id']] = $arr;
			}
		}
		if (!isset($this->extra_fields[$filter])) {
			$this->extra_fields[$filter] = $extra_fields;
		}
		if ($use_cache) {
			$extra_fields = $this->extra_fields[$filter];
		}
		return $extra_fields;
	}

	/**
	 * getExtraFieldsDefaultValue - Get array of extra fields default value
	 *
	 * @param	array	$types
	 * @param	bool	$get_is_disabled
	 * @param	bool	$get_is_hidden_on_submit
	 * @return	array	arrays of data;
	 */
	function getExtraFieldsDefaultValue($types = array(), $get_is_disabled = false, $get_is_hidden_on_submit = true) {
		$extra_fields = $this->getExtraFields($types, $get_is_disabled, $get_is_hidden_on_submit);
		$efDefaultValue = array();
		foreach ($extra_fields as $efID=>$efArr) {
			$ef = new ArtifactExtraField($this, $efID);
			$defaultValue = $ef->getDefaultValues();
			if (!is_null($defaultValue)) {
				$efDefaultValue [$efID] = $defaultValue;
			} else {
				if (in_array($efArr['field_type'],unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_SINGLECHOICE))) {
					$efDefaultValue [$efID] = '';
				}
			}
		}
		return $efDefaultValue;
	}

	/**
	 * getExtraFieldsInFormula - Get array of extra fields used in formula
	 *
	 * @param	array	$types
	 * @param	bool	$get_is_disabled
	 * @param	bool	$get_is_hidden_on_submit
	 * @return	array	arrays of data;
	 */
	function getExtraFieldsInFormula($types = array(), $get_is_disabled = false, $get_is_hidden_on_submit = true) {
		$return = array();
		$extra_fields = $this->getExtraFields($types, $get_is_disabled, $get_is_hidden_on_submit);
		$res = db_query_params('SELECT string_agg(formula,chr(10)) FROM artifact_extra_field_formula NATURAL INNER JOIN artifact_extra_field_list WHERE is_disabled=0 AND group_artifact_id=$1',
				array ($this->getID()));
		if (db_numrows($res) > 0) {
			$row = db_fetch_array($res);
			if (preg_match_all("/([a-z]\w*)/m", $row[0], $matches)) {
				foreach ($extra_fields as $extra_field) {
					if (in_array($extra_field['alias'],$matches[0])) {
						$return[]=$extra_field['extra_field_id'];
					}
				}
			}
		}
		return $return;
	}

	/**
	 * getFieldsInFormula - Get array of extra fields used in formula
	 *
	 * @return	array	arrays of data;
	 */
	function getFieldsInFormula() {
		$return = array();
		if ($this->usesCustomStatuses()) {
			$fields = array('assigned_to','priority','summary','description');
		} else {
			$fields = array('assigned_to','priority','summary','description','status');
		}
		$res = db_query_params('SELECT string_agg(formula,chr(10)) FROM artifact_extra_field_formula NATURAL INNER JOIN artifact_extra_field_list WHERE is_disabled=0 AND group_artifact_id=$1',
					array($this->getID()));
		if (db_numrows($res) > 0) {
			$row = db_fetch_array($res);
			if (preg_match_all("/([a-z]\w*)/m", $row[0], $matches)) {
				foreach ($fields as $field) {
					if (in_array($field,$matches[0])) {
						$return[]=$field;
					}
				}
			}
		}
		return $return;
	}

	/**
	 * getExtraFieldsWithFormula - Get array of extra fields with formula
	 *
	 * @param	array	$types
	 * @param	bool	$get_is_disabled
	 * @param	bool	$get_is_hidden_on_submit
	 * @return	array	arrays of data;
	 */
	function getExtraFieldsWithFormula($types = array(), $get_is_disabled = false, $get_is_hidden_on_submit = true) {
		$return = array();
		$extra_fields = $this->getExtraFields($types, $get_is_disabled, $get_is_hidden_on_submit);
		$res = db_query_params('SELECT extra_field_id FROM artifact_extra_field_formula NATURAL INNER JOIN artifact_extra_field_list WHERE is_disabled=0 AND group_artifact_id=$1',
				array ($this->getID()));
		while ($arr = db_fetch_array($res)) {
			$return []= $arr['extra_field_id'];
		}
		return $return;
	}

	/**
	 * cloneFieldsFrom - clone all the fields and elements from another tracker
	 *
	 * @param	int	$clone_tracker_id	id of the cloned tracker
	 * @param	array	$id_mappings		array mapping between template objects and new project objects
	 * @return	boolean	true/false on success
	 */
	function cloneFieldsFrom($clone_tracker_id, $id_mappings = array()) {
		$at = artifactType_get_object($clone_tracker_id);
		if (!$at || !is_object($at)) {
			$this->setError(_('Could Not Get Tracker To Clone'));
			return false;
		} elseif ($at->isError()) {
			$this->setError(_('Clone Tracker Error').' '.$at->getErrorMessage());
			return false;
		}

		// Effort Unit Set
		$ef_effort = $at->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_EFFORT));
		if (!empty($ef_effort)) {
			$eus = new EffortUnitSet($at, $at->getEffortUnitSet());
			$this_eus = new EffortUnitSet($this, $this->getEffortUnitSet());
			switch ($eus->getLevel()) {
				case EFFORTUNITSET_FORGE_LEVEL:
					switch ($this_eus->getLevel()) {
						case EFFORTUNITSET_PROJECT_LEVEL:
							if (!$this_eus->isEquivalentTo($eus)) {
								// make a copy at the tracker level
								$new_eus = new EffortUnitSet($this);
								$new_eus->copy($eus);
								$this->setEffortUnitSet($new_eus->getID());
							}
							break;
						case EFFORTUNITSET_TRACKER_LEVEL:
							if (!$this_eus->isEquivalentTo($eus)) {
								$this->setError(_('Clone Tracker Error')._(':').' '._('Effort Unit Set already define and not compatible'));
								return false;
							}
							break;
					}
					break;
				case EFFORTUNITSET_PROJECT_LEVEL:
					switch ($this_eus->getLevel()) {
						case EFFORTUNITSET_FORGE_LEVEL:
							$new_eus_id = getEffortUnitSetForLevel($this, EFFORTUNITSET_PROJECT_LEVEL);
							if ($new_eus_id) {
								$new_eus = new EffortUnitSet($this, $new_eus_id);
								if (!$new_eus->isEquivalentTo($eus)) {
									$this->setEffortUnitSet($new_eus->getID());
								} else {
									// make a copy at the tracker level
									$new_eus = new EffortUnitSet($this);
									$new_eus->copy($eus);
									$this->setEffortUnitSet($new_eus->getID());
								}
							} else {
								// make a copy at the project level
								$new_eus = new EffortUnitSet($this->Group);
								$new_eus->copy($eus);
								$this->setEffortUnitSet($new_eus->getID());
							}
							break;
						case EFFORTUNITSET_PROJECT_LEVEL:
							if (!$this_eus->isEquivalentTo($eus)) {
								// make a copy at the tracker level
								$new_eus = new EffortUnitSet($this);
								$new_eus->copy($eus);
								$this->setEffortUnitSet($new_eus->getID());
							}
							break;
						case EFFORTUNITSET_TRACKER_LEVEL:
							if (!$this_eus->isEquivalentTo($eus)) {
								$this->setError(_('Clone Tracker Error')._(':').' '._('Effort Unit Set already define and not compatible'));
								return false;
							}
							break;
					}
					break;
				case EFFORTUNITSET_TRACKER_LEVEL:
					switch ($this_eus->getLevel()) {
						case EFFORTUNITSET_FORGE_LEVEL:
						case EFFORTUNITSET_PROJECT_LEVEL:
							$new_eus_id = getEffortUnitSetForLevel($this, EFFORTUNITSET_TRACKER_LEVEL);
							if ($new_eus_id) {
								$new_eus = new EffortUnitSet($this, $new_eus_id);
								if (!$new_eus->isEquivalentTo($eus)) {
									$this->setEffortUnitSet($new_eus->getID());
								} else {
									$this->setError(_('Clone Tracker Error')._(':').' '._('Effort Unit Set already define and not compatible'));
									return false;
								}
							} else {
								// make a copy at the tracker level
								$new_eus = new EffortUnitSet($this);
								$new_eus->copy($eus);
								$this->setEffortUnitSet($new_eus->getID());
							}
							break;
						case EFFORTUNITSET_TRACKER_LEVEL:
							if (!$this_eus->isEquivalentTo($eus)) {
								$this->setError(_('Clone Tracker Error')._(':').' '._('Effort Unit Set already define and not compatible'));
								return false;
							}
							break;
					}
					break;
			}
		}

		// do not filter and get disabled fields as well
		$efs = $at->getExtraFields(array(), true);

		// get current getExtraFields if any and includes disabled fields as well...
		$current_efs = $this->getExtraFields(array(), true);

		//
		//	Iterate list of extra fields
		//
		db_begin();
		$newEFIds = array();
		$newEFElIds = array();
		foreach ($efs as $ef) {
			//new field in this tracker
			$nef = new ArtifactExtraField($this);
			foreach ($current_efs as $current_ef) {
				if ($current_ef['field_name'] == $ef['field_name'] || $current_ef['field_type'] == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
					// we delete the current extra field and use the template one...
					$current_ef_todelete = new ArtifactExtraField($this, $current_ef);
					$current_ef_todelete->delete(true,true);
				}
			}
			if (!$nef->create(util_unconvert_htmlspecialchars($ef['field_name']), $ef['field_type'], $ef['attribute1'], $ef['attribute2'], $ef['is_required'], $ef['alias'], $ef['show100'], $ef['show100label'], $ef['description'], $ef['pattern'], 100, 0, $ef['is_hidden_on_submit'], $ef['is_disabled'])) {
				$this->setError(_('Error Creating New Extra Field')._(':').' '.$nef->getErrorMessage());
				db_rollback();
				return false;
			}
			$newEFIds[$ef['extra_field_id']] = $nef->getID();
			$newEFElIds[$ef['extra_field_id']] = array();

			//by default extrafield status is created with default values: 'Open' & 'Closed'
			if ($nef->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				$existingElements = $nef->getAvailableValues();
				foreach($existingElements as $existingElement) {
					$existingElement = new ArtifactExtraFieldElement($nef, $existingElement);
					$existingElement->delete();
				}
			}

			//
			//	Iterate the elements
			//
			if (in_array($ef['field_type'], unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_CHOICE))) {
				$elements = $this->getExtraFieldElements($ef['extra_field_id']);
				foreach ($elements as $el) {
					//new element
					$nel = new ArtifactExtraFieldElement($nef);
					if (!$nel->create(util_unconvert_htmlspecialchars($el['element_name']), $el['status_id'], $el['auto_assign_to'], $el['is_default'])) {
						db_rollback();
						$this->setError(_('Error Creating New Extra Field Element')._(':').' '.$nel->getErrorMessage());
						return false;
					}
					$newEFElIds[$ef['extra_field_id']][$el['element_id']] = $nel->getID();
				}
			} elseif ($ef['field_type'] == ARTIFACT_EXTRAFIELDTYPE_USER) {
				$elements = $this->getExtraFieldElements($ef['extra_field_id']);
				$newRoles = $this->getGroup()->getRoles();
				foreach ($elements as $el) {
					$oldRole = RBACEngine::getInstance()->getRoleById($el['element_name']);
					if ($oldRole && is_object($oldRole)) {
						if ($oldRole->isPublic()) {
							foreach ($newRoles as $newRole) {
								if ($oldRole->getID() == $newRole->getID()) {
									if (!$nel->create($el['element_name'])) {
										db_rollback();
										$this->setError(_('Error Creating New Extra Field Element')._(':').' '.$nel->getErrorMessage());
										return false;
									}
									$newEFElIds[$ef['extra_field_id']][$el['element_id']] = $nel->getID();
									break;
								}
							}
						} else {
							foreach ($newRoles as $newRole) {
								if ($oldRole->getName() == $newRole->getName()) {
									if (!$nel->create($newRole->getID())) {
										db_rollback();
										$this->setError(_('Error Creating New Extra Field Element')._(':').' '.$nel->getErrorMessage());
										return false;
									}
									$newEFElIds[$ef['extra_field_id']][$el['element_id']] = $nel->getID();
									break;
								}
							}
						}
					}
				}
			}
		}
		foreach ($newEFIds as $oldEFId => $newEFId) {
			$oef = new ArtifactExtraField($at, $oldEFId);
			$nef = new ArtifactExtraField($this, $newEFId);
			// clone default value
			$type = $oef->getType();
			if (in_array($type, unserialize(ARTIFACT_EXTRAFIELDTYPEGROUP_VALUE)) || $type == ARTIFACT_EXTRAFIELDTYPE_USER) {
				$default = $oef->getDefaultValues();
				if (($type==ARTIFACT_EXTRAFIELDTYPE_INTEGER && $default != 0)) {
					$nef->setDefaultValues($default);
				} elseif ($type==ARTIFACT_EXTRAFIELDTYPE_USER && $default != 100) {
					$roleEls = $this->getExtraFieldElements($newEFId);
					$defaultUser = UserManager::instance()->getUserById($default);
					foreach ($roleEls as $roleEl) {
						$role = RBACEngine::getInstance()->getRoleById($roleEl['element_name']);
						if ( $role->hasUser($defaultUser)) {
							$nef->setDefaultValues($default);
							break;
						}
					}
				} else {
					if ($default) {
						$nef->setDefaultValues($default);
					}
				}
			}
			// update Dependency between extrafield
			$oefParent = $oef->getParent();
			if (!empty($oefParent) && $oef->getParent() != 100) {
				if (!$nef->update($nef->getName(), $nef->getAttribute1(), $nef->getAttribute2(), $nef->isRequired(), $nef->getAlias(), $nef->getShow100(), $nef->getShow100label(), $nef->getDescription(), $nef->getPattern(), $newEFIds[$oef->getParent()], $nef->isAutoAssign(), $nef->isHiddenOnSubmit(), $nef->isDisabled())) {
					db_rollback();
					$this->setError(_('Error Updating New Extra Field Parent')._(':').' '.$nef->getErrorMessage());
					return false;
				}
				foreach ($newEFElIds[$oldEFId] as $oldEFElId => $newEFElId) {
					$oel = new ArtifactExtraFieldElement($oef,$oldEFElId);
					if ($oel->isError()) {
						db_rollback();
						$this->setError($oel->getErrorMessage());
						return false;
					}
					$nel = new ArtifactExtraFieldElement($nef,$newEFElId);
					if ($nel->isError()) {
						db_rollback();
						$this->setError($nel->getErrorMessage());
						return false;
					}
					$oPEls = $oel->getParentElements();
					$nPEls = array();
					foreach ($oPEls as $oPEl) {
						$nPEls[]=$newEFElIds[$oef->getParent()][$oPEl];
					}
					$nel->saveParentElements($nPEls);
					if ($nel->isError()) {
						db_rollback();
						$this->setError(_('Error Saving New Extra Field Parent Elements').' '.$nel->getErrorMessage());
						return false;
					}
				}
			}
			// update workflow
			if ($nef->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
				// update the allowed init values
				$oatw = new ArtifactWorkflow($at, $oldEFId);
				$natw = new ArtifactWorkflow($this, $newEFId);
				// template allowed init values
				$oaivs = $oatw->getNextNodes('100');
				$naivs = array();
				foreach ($oaivs as $oaiv) {
					$naivs[] = $newEFElIds[$oldEFId][$oaiv];
				}
				$natw->saveNextNodes('100', $naivs);

				//implement role based of the workflow
				if (sizeof($id_mappings) && isset($id_mappings['role'])) {
					$oefelements = $at->getExtraFieldElements($oldEFId);
					foreach ($oefelements as $oefelement) {
						// retrieve the allowed values for the old element
						$onexts = $oatw->getNextNodes($oefelement['element_id']);
						$naivs = array();
						foreach ($onexts as $onext) {
							$naivs[] = $newEFElIds[$oldEFId][$onext];
							//retrieve the allowed old roles from old element to old next value
							$oars = $oatw->getAllowedRoles($oefelement['element_id'], $onext);
							//map old roles into new roles id
							$nar = array();
							foreach ($oars as $oar) {
								if (array_key_exists($oar, $id_mappings['role'])) {
									$nar[] = $id_mappings['role'][$oar];
								}
							}
							$natw->saveAllowedRoles($newEFElIds[$oldEFId][$oefelement['element_id']], $newEFElIds[$oldEFId][$onext], $nar);
						}
						$natw->saveNextNodes($newEFElIds[$oldEFId][$oefelement['element_id']], $naivs);
					}
				}
			}
		}

		db_commit();
		return $newEFIds;
	}

	/**
	 * getExtraFieldName - Get a box name using the box ID
	 *
	 * @param	int	$extra_field_id	id of an extra field.
	 * @return	string	name of extra field.
	 */
	function getExtraFieldName($extra_field_id) {
		$arr = $this->getExtraFields();
		return $arr[$extra_field_id]['field_name'];
	}

	/**
	 * getExtraFieldElements - List of possible admin configured
	 * extra field elements. This function is used to
	 * present the boxes and choices on the main Add/Update page.
	 *
	 * @param	int	$id	id of the extra field
	 * @return	array of elements for this extra field.
	 */
	function getExtraFieldElements($id) {
//TODO validate $id
		if (!$id) {
			return false;
		}
		if (!isset($this->extra_field[$id])) {
			$this->extra_field[$id] = array();
			$ef = new ArtifactExtraField($this,$id);
			if (!$ef || $ef->isError()) {
				return false;
			}
			$efValues = $ef->getAvailableValues();
			$this->extra_field[$id] = $efValues;
		}

		return $this->extra_field[$id];
	}

	/**
	 * getElementName - get the name of a particular element.
	 *
	 * @param	$choice_id
	 * @return	string		The name.
	 */
	function getElementName($choice_id) {
		if (!$choice_id) {
			return '';
		}
		if (is_array($choice_id)) {
			$choice_id = implode(',', array_map('intval', $choice_id));
		} else {
			$choice_id = intval($choice_id);
		}
		if ($choice_id == 100) {
			return 'None';
		}
		if (!isset($this->element_name[$choice_id])) {
			$res = db_query_params('SELECT element_id, element_name
				FROM artifact_extra_field_elements
				WHERE element_id = ANY ($1)',
						array(db_int_array_to_any_clause(explode(',', $choice_id))));
			if (db_numrows($res) > 1) {
				$arr = util_result_column_to_array($res, 1);
				$this->element_name[$choice_id] = implode(',', $arr);
			} else {
				$this->element_name[$choice_id] = db_result($res, 0, 'element_name');
			}
		}
		return $this->element_name[$choice_id];
	}

	/**
	 * getElementStatusID - get the status of a particular element.
	 *
	 * @param	int|array	$choice_id
	 * @return	int		The status
	 */
	function getElementStatusID($choice_id) {
		if (!$choice_id) {
			return 0;
		}
		if (is_array($choice_id)) {
			$choice_id = implode(',',$choice_id);
		}
		if ($choice_id == 100) {
			return 0;
		}
		if (!$this->element_status[$choice_id]) {
			$res = db_query_params('SELECT element_id,extra_field_id,status_id
				FROM artifact_extra_field_elements
				WHERE element_id = ANY ($1)',
						array(db_int_array_to_any_clause(explode(',', $choice_id))));
			if (db_numrows($res) > 1) {
				$arr = util_result_column_to_array($res, 2);
				$this->element_status[$choice_id] = implode(',', $arr);
			} else {
				$this->element_status[$choice_id] = db_result($res, 0, 'status_id');
			}
		}
		return $this->element_status[$choice_id];
	}

	/**
	 * delete - delete this tracker and all its related data.
	 *
	 * @param	bool	$sure		I'm Sure.
	 * @param	bool	$really_sure	I'm REALLY sure.
	 * @return	bool	true/false;
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm ('tracker_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();
		db_query_params('DELETE FROM artifact_extra_field_data
			WHERE EXISTS (SELECT artifact_id FROM artifact
			WHERE group_artifact_id=$1
			AND artifact.artifact_id=artifact_extra_field_data.artifact_id)',
				array($this->getID()));
		db_query_params('DELETE FROM artifact_extra_field_elements
			WHERE EXISTS (SELECT extra_field_id FROM artifact_extra_field_list
			WHERE group_artifact_id=$1
			AND artifact_extra_field_list.extra_field_id = artifact_extra_field_elements.extra_field_id)',
				 array($this->getID()));
		db_query_params('DELETE FROM artifact_extra_field_list
			WHERE group_artifact_id=$1',
			array($this->getID()));
		db_query_params('DELETE FROM artifact_canned_responses
			WHERE group_artifact_id=$1',
				 array($this->getID()));
		db_query_params('DELETE FROM artifact_counts_agg
			WHERE group_artifact_id=$1',
				 array($this->getID()));

		ArtifactStorage::instance()->deleteFromQuery('SELECT id FROM artifact_file
			WHERE EXISTS (SELECT artifact_id FROM artifact
			WHERE group_artifact_id=$1
			AND artifact.artifact_id=artifact_file.artifact_id)',
				array($this->getID()));

		db_query_params('DELETE FROM artifact_file
			WHERE EXISTS (SELECT artifact_id FROM artifact
			WHERE group_artifact_id=$1
			AND artifact.artifact_id=artifact_file.artifact_id)',
				array($this->getID()));
		db_query_params('DELETE FROM artifact_message
			WHERE EXISTS (SELECT artifact_id FROM artifact
			WHERE group_artifact_id=$1
			AND artifact.artifact_id=artifact_message.artifact_id)',
				array($this->getID()));
		db_query_params('DELETE FROM artifact_history
			WHERE EXISTS (SELECT artifact_id FROM artifact
			WHERE group_artifact_id=$1
			AND artifact.artifact_id=artifact_history.artifact_id)',
				array($this->getID()));
		db_query_params('DELETE FROM artifact_monitor
			WHERE EXISTS (SELECT artifact_id FROM artifact
			WHERE group_artifact_id=$1
			AND artifact.artifact_id=artifact_monitor.artifact_id)',
				array($this->getID()));
		db_query_params('DELETE FROM artifact
			WHERE group_artifact_id=$1',
				array($this->getID()));
		db_query_params('DELETE FROM artifact_group_list
			WHERE group_artifact_id=$1',
				array($this->getID()));
		$MonitorElementObject = new MonitorElement('artifact_type');
		$MonitorElementObject->clearMonitor($this->getID());

		db_commit();
		ArtifactStorage::instance()->commit();

		$this->Group->normalizeAllRoles();

		return true;
	}

	/**
	 * getSubmitters - returns a result set of submitters.
	 *
	 * @return	resource	database result set.
	 */
	function getSubmitters() {
		if (!isset($this->submitters_res)) {
			$this->submitters_res = db_query_params('SELECT DISTINCT submitted_by, submitted_realname
				FROM artifact_vw
				WHERE group_artifact_id=$1
				ORDER BY submitted_realname',
				array($this->getID()));
		}
		return $this->submitters_res;
	}

	/**
	 * getLastModifiers - returns a result set of last modifiers.
	 *
	 * @return	resource	database result set.
	 */
	function getLastModifiers() {
		if (!isset($this->last_modifiers_res)) {
			$this->last_modifiers_res = db_query_params('SELECT DISTINCT last_modified_by, last_modified_realname
				FROM artifact_vw
				WHERE group_artifact_id=$1
				ORDER BY last_modified_realname',
					array($this->getID()));
		}
		return $this->last_modifiers_res;
	}

	/**
	 * getCannedResponses - returns a result set of canned responses.
	 *
	 * @return	resource	database result set.
	 */
	function getCannedResponses() {
		if (!isset($this->cannedresponses_res)) {
			$this->cannedresponses_res = db_query_params('SELECT id,title
				FROM artifact_canned_responses
				WHERE group_artifact_id=$1',
								      array($this->getID()));
		}
		return $this->cannedresponses_res;
	}

	/**
	 * getStatuses - returns a result set of statuses.
	 *
	 * These statuses are either the default open/closed or any number of
	 * custom statuses that are stored in the extra fields. On insert/update
	 * to an artifact the status_id is remapped from the extra_field_element_id to
	 * the standard open/closed id.
	 *
	 * @return	resource	database result set.
	 */
	function getStatuses() {
		if (!isset($this->status_res)) {
			$this->status_res = db_query_params('SELECT * FROM artifact_status', array());
		}
		return $this->status_res;
	}

	/**
	 * getStatusName - returns the name of this status.
	 *
	 * @param	int	$id	The status ID.
	 * @return	string	name.
	 */
	function getStatusName($id) {
		$result = db_query_params('select status_name from artifact_status WHERE id=$1',
						array($id));
		if ($result && db_numrows($result) > 0) {
			return db_result($result, 0, 'status_name');
		} else {
			return 'Error: Not Found';
		}
	}

	/**
	 * update - use this to update this ArtifactType in the database.
	 *
	 * @param	string	$name			The item name.
	 * @param	string	$description		The item description.
	 * @param	bool	$email_all		(1) true (0) false - whether to email on all updates.
	 * @param	string	$email_address		The address to send new entries and updates to.
	 * @param	int	$due_period		Days before this item is considered overdue.
	 * @param	int	$status_timeout		 Days before stale items time out.
	 * @param	bool	$use_resolution		(1) true (0) false - whether the resolution box should be shown. //TODO: unused parameter. to be drop!
	 * @param	string	$submit_instructions	Free-form string that project admins can place on the submit page.
	 * @param	string	$browse_instructions	Free-form string that project admins can place on the browse page.
	 * @return	bool	true on success, false on failure.
	 */
	function update($name, $description, $email_all, $email_address,
					$due_period, $status_timeout, $use_resolution, $submit_instructions, $browse_instructions) {

		if (!forge_check_perm ('tracker_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		if ($this->getDataType()) {
			$name=$this->getName();
			$description=$this->getDescription();
		}

		if (!$name || !$description || !$due_period || !$status_timeout) {
			$this->setError(_('ArtifactType')._(': ')._('Name, Description, Due Period, and Status Timeout are required'));
			return false;
		}

		$result = db_query_params('SELECT count(*) AS count FROM artifact_group_list WHERE group_id=$1 AND name=$2 AND group_artifact_id!=$3',
			array($this->Group->getID(), $name, $this->getID()));
		if (!$result) {
			$this->setError('ArtifactType::Update(): '.db_error());
			return false;
		}
		if (db_result($result, 0, 'count')) {
			$this->setError(_('Tracker name already used'));
			return false;
		}

		if ($email_address) {
			$invalid_emails = validate_emails($email_address);
			if (count($invalid_emails) > 0) {
				$this->setError(_('E-mail address(es) appeared invalid')._(': ').implode(',', $invalid_emails));
				return false;
			}
		}

		$email_all = ((!$email_all) ? 0 : $email_all);

		$res = db_query_params('UPDATE artifact_group_list SET
			name=$1,
			description=$2,
			email_all_updates=$3,
			email_address=$4,
			due_period=$5,
			status_timeout=$6,
			submit_instructions=$7,
			browse_instructions=$8
			WHERE group_artifact_id=$9 AND group_id=$10',
					 array(
						 htmlspecialchars($name),
						 htmlspecialchars($description),
						 $email_all,
						 $email_address,
						 $due_period * (60*60*24),
						 $status_timeout * (60*60*24),
						 htmlspecialchars($submit_instructions),
						 htmlspecialchars($browse_instructions),
						 $this->getID(),
						 $this->Group->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('ArtifactType::Update(): '.db_error());
			return false;
		} else {
			$this->fetchData($this->getID());
			return true;
		}
	}

	/**
	 * getBrowseList - get the list of columns in browse page.
	 *
	 * @return	string	instructions.
	 */
	function getBrowseList() {
		$list = $this->data_array['browse_list'];

		// remove status_id in the browse list if a custom status exists
		if (count($this->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS))) > 0) {
			$arr = explode(',', $list);
			$idx = array_search('status_id', $arr);
			if ($idx !== False) {
				array_splice($arr, $idx, 1);
			}
			return join(',', $arr);
		}

		return $list;
	}

	/**
	 * setBrowseList - set the list of columns in browse page.
	 *
	 * @param	string	$list	string comma separated of ids of custom field and names of internal fields.
	 * @return	boolean	success.
	 */
	function setBrowseList($list) {
		$res = db_query_params('UPDATE artifact_group_list
			SET browse_list=$1
			WHERE group_artifact_id=$2',
			array($list,
				$this->getID()));
		$this->fetchData($this->getID());
		return $res;
	}

	/**
	 * canVote - check whether the current user can vote on
	 *		items in this tracker
	 *
	 * @return	bool	true if they can
	 */
	function canVote() {
		return forge_check_perm('tracker', $this->getID(), 'vote');
	}

	/**
	 * getVoters - get IDs of users that may vote on
	 *		items in this tracker
	 *
	 * @return	array	list of user IDs
	 */
	function getVoters() {
		if ($this->voters !== false) {
			return $this->voters;
		}

		$this->voters = array();
		if (($engine = RBACEngine::getInstance())
			&& ($voters = $engine->getUsersByAllowedAction('tracker', $this->getID(), 'vote'))
			&& (count($voters) > 0)) {
			foreach ($voters as $voter) {
				$voter_id = $voter->getID();
				$this->voters[$voter_id] = $voter_id;
			}
		}
		return $this->voters;
	}


	/**
	 *
	 * @param	integer	$unit_set_id	the effort unit set id
	 * @return	bool
	 */
	function setEffortUnitSet($unit_set_id) {
		db_begin();
		$res = db_query_params ('UPDATE artifact_group_list SET unit_set_id=$1 WHERE group_artifact_id=$2',
				array($unit_set_id, $this->getID()));
		if ($res) {
			$this->data_array['unit_set_id'] = $unit_set_id;
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 * getEffortUnitSet - Get the effort unit set id.
	 *
	 * @return	integer	The id of the effort unit set.
	 */
	function getEffortUnitSet() {
		return $this->data_array['unit_set_id'];
	}

	/**
	 * getSettings - Get all parameters of this tracker
	 *
	 * @return	array	all parameters into an multidimensional array
	 */
	function getSettings() {
		// Get list of extra fields for this artifact
		$extrafields = array();
		$tmpextrafields = $this->getExtraFields(array(), true);
		foreach ($tmpextrafields as $extrafield) {
			$aefobj = new ArtifactExtraField($this, $extrafield["extra_field_id"]);

			// array of available values
			$avtmp = $aefobj->getAvailableValues();
			$avs = array();
			for ($j=0; $j < count($avtmp); $j++) {
				$avs[$j]['auto_assign_to']	= $avtmp[$j]['auto_assign_to'];
				$avs[$j]['element_id']		= $avtmp[$j]['element_id'];
				$avs[$j]['element_name']	= $avtmp[$j]['element_name'];
				$avs[$j]['is_default']		= $avtmp[$j]['is_default'];
				$avs[$j]['status_id']		= $avtmp[$j]['status_id'];
			}

			$extrafields[] = array(
				'alias'			=> $aefobj->getAlias(),
				'attribute1'		=> $aefobj->getAttribute1(),
				'attribute2'		=> $aefobj->getAttribute2(),
				'autoassign'		=> $aefobj->isAutoAssign(),
				'available_values'	=> $avs,
				'description'		=> $aefobj->getDescription(),
				'extra_field_id'	=> $aefobj->getID(),
				'field_name'		=> $aefobj->getName(),
				'field_type'		=> $aefobj->getType(),
				'is_disabled'		=> $aefobj->isDisabled(),
				'is_hidden_on_submit'	=> $aefobj->isHiddenOnSubmit(),
				'is_required'		=> $aefobj->isRequired(),
				'name'			=> $aefobj->getName(),
				'pattern'		=> $aefobj->getPattern(),
				'parent'		=> $aefobj->getParent(),
				'show100'		=> $aefobj->getShow100(),
				'show100label'		=> $aefobj->getShow100label()
							);
		}

		$return = array(
				'browse_list'		=> $this->getBrowseList(),
				'browse_instructions'	=> $this->getBrowseInstructions(),
				'custom_status_field'	=> $this->getCustomStatusField(),
				'datatype'		=> $this->getDataType(),
				'description'		=> $this->getDescription(),
				'due_period'		=> $this->getDuePeriod(),
				'email_address'		=> $this->getEmailAddress(),
				'email_all_updates'	=> $this->emailAll(),
				'extra_fields'		=> $extrafields,

				'group_artifact_id'	=> $this->getID(),
				'group_id'		=> $this->getGroup()->getID(),
				'name'			=> $this->getName(),
				'status_timeout'	=> $this->getStatusTimeout(),
				'submit_instructions'	=> $this->getSubmitInstructions(),
				'unit_set_id'		=> $this->getEffortUnitSet(),
				'use_tracker_widget_display' => $this->getWidgetLayoutConfig());
		return $return;
	}

	function getWidgetLayoutConfig() {
		$lm = new WidgetLayoutManager();
		return $lm->getLayout($this->getID(), WidgetLayoutManager::OWNER_TYPE_TRACKER);
	}

	function getExtraFieldByAlias($alias){
		$return = false;
		$efs = $this->getExtraFields();
		foreach ($efs as $ef) {
			if ($alias == $ef['alias']) {
				$return = $ef;
				break;
			}
		}
		return $return;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
