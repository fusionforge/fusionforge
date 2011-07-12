<?php
/**
 * The ArtifactWorkflow class manages workflow for trackers.
 *
 * The workflow is attached to custom status field only.
 *
 * Associated tables are:
 * - artifact_workflow_event  : to track allowed events.
 * - artifact_workflow_roles  : to track roles allowed to perform an event.
 * - artifact_workflow_notify : to track notification associated to an event (not implemented).
 *
 * An event is a transition from one value to another.
 *
 * NOTE: Code should be improved to manage any kind of custom fields not only of type
 * 'Status' but maybe also for the 'select' also.
 *
 * 2008 : Alain Peyrat <alain.peyrat@alcatel-lucent.com>
 *
 * NOTES:
 * @todo: the getAllowedRoles should be replaced by getRealAllowedRoles code. (to be tested).
 * @todo: Some code could use a db direct to array func instead of the while.
 *
 */
require_once $gfcommon.'include/Error.class.php';

class ArtifactWorkflow extends Error {

	var $ath;
	var $artifact_id;
	var $field_id;

	function ArtifactWorkflow($artifact, $field_id) {
		$this->ath = $artifact;
		$this->artifact_id = (int)$artifact->getID();
		$this->field_id = (int)$field_id;
		return true;
	}

	// Check if the following event is allowed or not.
	// return true is allowed, false if not.
	function checkEvent($from, $to) {
		if ($from === $to)
			return true;


		$res = db_query_params ('SELECT event_id FROM artifact_workflow_event
				WHERE group_artifact_id=$1
				AND field_id=$2
				AND from_value_id=$3
				AND to_value_id=$4',
			array($this->artifact_id,
				$this->field_id,
				$from,
				$to));
		$event_id = db_result($res, 0, 'event_id');
		if ($event_id) {
			// No role based checks for the initial transition.
			if ($from == 100)
				return true;

			// There is a transition, now check if current role is allowed.
			$rids = array () ;
			$available_roles = RBACEngine::getInstance()->getAvailableRoles() ;
			$project_role_ids = $this->ath->Group->getRolesId () ;
			foreach ($available_roles as $role) {
				if (in_array($role->getID(),$project_role_ids)) {
					$rids[] = $role->getID() ;
				}
			}

			$res = db_query_params ('SELECT event_id
					FROM artifact_workflow_roles
					WHERE group_id=$1
					AND event_id=$2
					AND role_id=ANY($3)',
						array ($this->ath->Group->getID(),
						       $event_id,
						       db_int_array_to_any_clause($rids)));
			return db_result($res, 0, 'event_id') ? true : false;
		}
		return false;
	}

	function getNotifyFromWorkFlow() {

	}

	/*
	 * When a new element is created, add all the new events in the workflow.
	 */
	function addNode($element_id) {
		$elearray = $this->ath->getExtraFieldElements($this->field_id);
		foreach ($elearray as $e) {
			if ($element_id !== $e['element_id']) {
				$this->_addEvent($e['element_id'], $element_id);
				$this->_addEvent($element_id, $e['element_id']);
			}
		}

		// Allow the new element for the Submit form (Initial values).
		$this->_addEvent('100', $element_id);
	}

	/*
	 * When a new element is removed, remove all the events in the workflow.
	 */
	function removeNode($element_id) {
		$elearray = $this->ath->getExtraFieldElements($this->field_id);
		foreach ($elearray as $e) {
			if ($element_id !== $e['element_id']) {
				$this->_removeEvent($e['element_id'], $element_id);
				$this->_removeEvent($element_id, $e['element_id']);
			}
		}

		// Allow the new element for the Submit form (Initial values).
		$this->_removeEvent('100', $element_id);
	}

	// Returns all the possible following nodes (no roles involved).
	function getNextNodes($from) {

		$res = db_query_params ('SELECT to_value_id FROM artifact_workflow_event
				WHERE group_artifact_id=$1
				AND field_id=$2
				AND from_value_id=$3',
			array($this->artifact_id,
				$this->field_id,
				(int)$from));
		$values = array();
		while($arr = db_fetch_array($res)) {
			$values[] = $arr['to_value_id'];
		}
		return $values;

	}


	function saveNextNodes($from, $nodes) {

		// Get All possible nodes.
		$current = $this->getNextNodes($from);

		// Remove events no longer present.
		foreach ($current as $node) {
			if (!in_array($node, $nodes)) {
				if ($from != $node) {
					$this->_removeEvent($from, $node);
				}
			}
		}

		// Add missing events.
		foreach ($nodes as $node) {
			if (!in_array($node, $current)) {
				$this->_addEvent($from, $node);
			}
		}
		return true;
	}

	function getAllowedRoles($from, $to) {
		$values = $this->_getRealAllowedRoles($from, $to);

		// If no values, then no roles defined, all roles are allowed.
		if (empty($values)) {
			$roles = $this->ath->Group->getRoles() ;
			sortRoleList($roles, $this->ath->Group) ;
			foreach ($roles as $r) {
				$values[] = $r->getID() ;
			}
		}
		return $values;
	}


	function saveAllowedRoles($from, $to, $roles) {

		$event_id = $this->_getEventId($from, $to);

		// Get All possible roles.
		$current = $this->_getRealAllowedRoles($from, $to);

		// Remove roles no longer present.
		foreach ($current as $role) {
			if (!in_array($role, $roles)) {
				$this->_removeRole($event_id, $role);
			}
		}

		// Add missing roles.
		foreach ($roles as $role) {
			if (!in_array($role, $current)) {
				$this->_addRole($event_id, $role);
			}
		}
		return true;
	}

	function _getEventId($from, $to) {

		$res = db_query_params ('SELECT event_id FROM artifact_workflow_event
				WHERE group_artifact_id=$1
				AND field_id=$2
				AND from_value_id=$3
				AND to_value_id=$4',
			array($this->artifact_id,
				$this->field_id,
				$from,
				$to));
		if (!$res) {
			$this->setError('Unable to get Event Id ($from, $to): '.db_error());
			return false;
		}
		return db_result($res, 0, 'event_id');

	}


	function _addEvent($from, $to) {

		$res = db_query_params ('INSERT INTO artifact_workflow_event
				(group_artifact_id, field_id, from_value_id, to_value_id)
				VALUES ($1, $2, $3, $4)',
			array($this->artifact_id,
				$this->field_id,
				$from,
				$to));
		if (!$res) {
			$this->setError('Unable to add Event($from, $to): '.db_error());
			return false;
		}

		$event_id = $this->_getEventId($from, $to);
		if ($event_id) {
			// By default, all roles are allowed on a new event.
			foreach ($this->ath->Group->getRoles() as $r) {
				$this->_addRole($event_id, $r->getID());
			}
		}

		return true;
	}


	function _removeEvent($from, $to) {
		$event_id = $this->_getEventId($from, $to);


		$res = db_query_params ('DELETE FROM artifact_workflow_event
				WHERE group_artifact_id=$1
				AND field_id=$2
				AND from_value_id=$3
				AND to_value_id=$4',
			array($this->artifact_id,
				$this->field_id,
				$from,
				$to));
		if (!$res) {
			$this->setError('Unable to remove Event($from, $to): '.db_error());
			return false;
		}

		return true;
	}

	function _getRealAllowedRoles($from, $to) {

		$res = db_query_params ('SELECT role_id
				FROM artifact_workflow_roles, artifact_workflow_event
				WHERE artifact_workflow_roles.event_id = artifact_workflow_event.event_id
				AND group_artifact_id=$1
				AND field_id=$2
				AND from_value_id=$3
				AND to_value_id=$4',
			array($this->artifact_id,
				$this->field_id,
				$from,
				$to));
		$values = array();
		while($arr = db_fetch_array($res)) {
			$values[] = $arr['role_id'];
		}
		return $values;
	}

	function _addRole($event_id, $role_id) {

		$res = db_query_params ('INSERT INTO artifact_workflow_roles
				(event_id, role_id)
				VALUES ($1, $2)',
			array($event_id,
				$role_id));
		if (!$res) {
			$this->setError('Unable to add Role ($role_id): '.db_error());
			return false;
		}
		return true;

	}

	function _removeRole($event_id, $role_id) {

		$res = db_query_params ('DELETE FROM artifact_workflow_roles
				WHERE event_id=$1 AND role_id=$2',
			array($event_id,
				$role_id));
		if (!$res) {
			$this->setError('Unable to remove Event($from, $to): '.db_error());
			return false;
		}
		return true;

	}

}

/*
 * Update the required information in the workflow when a new role is created.
 * In this case, for all the defined events, add the role as allowed.
 */
function workflow_add_new_role ($role_id, $group) {

	$res = db_query_params ('INSERT INTO artifact_workflow_roles
			SELECT event_id, $1 as role_id
					FROM artifact_workflow_event, artifact_group_list
					WHERE artifact_workflow_event.group_artifact_id=artifact_group_list.group_artifact_id
					AND artifact_group_list.group_id=$2',
			array($role_id,
				$group->getID()));
	if (!$res) {
		$this->setError('Unable to register new role in workflows: '.db_error());
		return false;
	}
	return true;
}

?>
