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
 * 2008 : Alain Peyrat <alain.peyrat@alcatel-lucent.fr>
 * 
 * NOTES:
 * @todo: the getAllowedRoles should be replaced by getRealAllowedRoles code. (to be tested).
 * @todo: Some code could use a db direct to array func instead of the while.
 * 
 */
require_once 'common/include/Error.class.php';

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

		$sql = "SELECT event_id FROM artifact_workflow_event 
				WHERE group_artifact_id=".$this->artifact_id."
				AND field_id=".$this->field_id."
				AND from_value_id=".$from."
				AND to_value_id=".$to;
		$res = db_query($sql);
		$event_id = db_result($res, 0, 'event_id');
		if ($event_id) {
			// No role based checks for the initial transition.
			if ($from == 100) 
				return true;

			// There is a transition, now check if current role is allowed.
			$sql = "SELECT event_id 
					FROM user_group, artifact_workflow_roles 
					WHERE user_id=".user_getid()."
					AND group_id=".$this->ath->Group->getID()."
					AND event_id=$event_id 
					AND user_group.role_id=artifact_workflow_roles.role_id";
			return db_result(db_query($sql), 0, 'event_id') ? true : false;
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
	
	// Returns all the possible following nodes (no roles involved).
	function getNextNodes($from) {
		$sql = "SELECT to_value_id FROM artifact_workflow_event 
				WHERE group_artifact_id=".$this->artifact_id."
				AND field_id=".$this->field_id."
				AND from_value_id=".(int)$from;
		$res = db_query($sql);
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
	}
	
	function getAllowedRoles($from, $to) {
		$values = $this->_getRealAllowedRoles($from, $to);
				
		// If no values, then no roles defined, all roles are allowed.
		if (empty($values)) {
			$res=db_query("SELECT role_id 
			FROM role WHERE group_id='".$this->ath->Group->getID()."' ORDER BY role_name");
			while($arr = db_fetch_array($res)) {
				$values[] = $arr['role_id'];
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
	}
	
	function _getEventId($from, $to) {
		$sql = "SELECT event_id FROM artifact_workflow_event 
				WHERE group_artifact_id=".$this->artifact_id."
				AND field_id=".$this->field_id."
				AND from_value_id=".$from."
				AND to_value_id=".$to;
		$res = db_query($sql);
		if (!$res) {
			$this->setError('Unable to get Event Id ($from, $to): '.db_error());
			return false;
		}
		return db_result($res, 0, 'event_id');
		
	}

	
	function _addEvent($from, $to) {
		$sql = "INSERT INTO artifact_workflow_event
				(group_artifact_id, field_id, from_value_id, to_value_id)
				VALUES (".$this->artifact_id.", ".$this->field_id.", $from, $to)";
		$res = db_query($sql);
		if (!$res) {
			$this->setError('Unable to add Event($from, $to): '.db_error());
			return false;
		}
		
		$event_id = $this->_getEventId($from, $to);
		if ($event_id) {
			// By default, all roles are allowed on a new event.
			$res=db_query("SELECT role_id 
				FROM role WHERE group_id='".$this->ath->Group->getID()."' ORDER BY role_name");
			while($arr = db_fetch_array($res)) {
				$this->_addRole($event_id, $arr['role_id']);
			}
		}

		return true;
	}

	
	function _removeEvent($from, $to) {
		$event_id = $this->_getEventId($from, $to);
		
		$sql = "DELETE FROM artifact_workflow_event
				WHERE group_artifact_id=".$this->artifact_id."
				AND field_id=".$this->field_id."
				AND from_value_id=".$from."
				AND to_value_id=".$to;
		$res = db_query($sql);
		if (!$res) {
			$this->setError('Unable to remove Event($from, $to): '.db_error());
			return false;
		}
		
		return true;
	}

	function _getRealAllowedRoles($from, $to) {
		$sql = "SELECT role_id
				FROM artifact_workflow_roles, artifact_workflow_event
				WHERE artifact_workflow_roles.event_id = artifact_workflow_event.event_id
				AND group_artifact_id=".$this->artifact_id."
				AND field_id=".$this->field_id."
				AND from_value_id=".$from."
				AND to_value_id=".$to;
		$res = db_query($sql);
		$values = array();
		while($arr = db_fetch_array($res)) {
			$values[] = $arr['role_id'];
		}
		return $values;
	}

	function _addRole($event_id, $role_id) {
		$sql = "INSERT INTO artifact_workflow_roles
				(event_id, role_id)
				VALUES ($event_id, $role_id)";
		$res = db_query($sql);
		if (!$res) {
			$this->setError('Unable to add Role ($role_id): '.db_error());
			return false;
		}
		return true;
		
	}
	
	function _removeRole($event_id, $role_id) {
		$sql = "DELETE FROM artifact_workflow_roles
				WHERE event_id=$event_id AND role_id=$role_id";
		$res = db_query($sql);
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
	$sql = "INSERT INTO artifact_workflow_roles 
			SELECT event_id, $role_id as role_id 
					FROM artifact_workflow_event, artifact_group_list
					WHERE artifact_workflow_event.group_artifact_id=artifact_group_list.group_artifact_id 
					AND artifact_group_list.group_id=".$group->getID();
	$res = db_query($sql);
	if (!$res) {
		$this->setError('Unable to register new role in workflows: '.db_error());
		return false;
	}
	return true;
}

?>
