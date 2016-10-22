<?php
/**
 * FusionForge base Object class
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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

/**
 * Any FusionForge object can be associate to another object.
 * It helps to follow tracks of relation between elements.
 * e.g: a document can be associate to a tracker.
 *      a artifact can be associate to a release.
 *
 * This class brings generic functions to add/remove/get associations of any object.
 */

require_once $gfcommon.'include/FFError.class.php';

class FFObject extends FFError {

	/**
	 * store id & objectType of association.
	 * array[objectType] = array(id, id, ...)
	 *
	 * @var	array	$associatedToArray
	 */
	var $associatedToArray = array();

	/**
	 *
	 * @var	array	$associatedFromArray
	 */
	var $associatedFromArray = array();

	function __construct($id = false, $objectType = false) {
		parent::__construct();
		if (forge_get_config('use_object_associations') && $id && $objectType) {
			$res = db_query_params('SELECT to_object_type, to_ref_id, to_id FROM fusionforge_object_assiociation WHERE from_id = $1 AND from_object_type = $2',
						array($id, $objectType));
			if ($res && db_numrows($res)) {
				while ($arr = db_fetch_array($res)) {
					$this->associatedToArray[$arr[0]][$arr[1]][] = $arr[2];
				}
			}
			$res = db_query_params('SELECT from_object_type, from_ref_id, from_id FROM fusionforge_object_assiociation WHERE to_id = $1 AND to_object_type = $2',
						array($id, $objectType));
			if ($res && db_numrows($res)) {
				while ($arr = db_fetch_array($res)) {
					$this->associatedFromArray[$arr[0]][$arr[1]][] = $arr[2];
				}
			}
		}
		return;
	}

	function getAssociatedTo() {
		return $this->associatedToArray;
	}

	function getAssociatedFrom() {
		return $this->associatedFromArray;
	}

	function getRefID($object) {
		switch (get_class($object)) {
			case 'Document':
				return $object->Group->getID();
				break;
			case 'Artifact':
			case 'ArtifactHtml':
				return $object->ArtifactType->getID();
				break;
		}
	}

	function getGroupID($object) {
		switch (get_class($object)) {
			case 'Document':
				return $object->Group->getID();
				break;
			case 'Artifact':
			case 'ArtifactHtml':
				return $object->ArtifactType->Group->getID();
				break;
		}
	}

	function getRealClass($object) {
		switch (get_class($object)) {
			case 'Document':
				return 'Document';
				break;
			case 'Artifact':
			case 'ArtifactHtml':
				return 'Artifact';
				break;
		}
	}

	function getRefObject($objectId, $objectRefId, $objectType) {
		switch ($objectType) {
			case 'Document':
				return document_get_object($objectId, $objectRefId);
				break;
			case 'Artifact':
				return artifact_get_object($objectId);
				break;
		}
	}

	function checkPermWrapper($objectType, $objectRefId) {
		switch($objectType) {
			case 'Document':
				return forge_check_perm('docman', $objectRefId, 'read');
				break;
			case 'Artifact':
			case 'ArtifactHtml':
				return forge_check_perm('tracker', $objectRefId, 'read');
				break;
		}
	}

	function addAssociations($objectrefs = '') {
		$objectRefArr = explode(',', $objectrefs);
		$status = false;
		if (count($objectRefArr) > 0) {
			$statusArr = array();
			foreach ($objectRefArr as $objectRef) {
				if (preg_match('/^[Dd][0-9]+/', $objectRef)) {
					//Document Ref.
					$documentId = substr($objectRef, 1);
					$documentObject = document_get_object($documentId, $this->getGroupID($this));
					if (is_object($documentObject)) {
						$statusArr[] = $this->addAssociationTo($documentObject);
					} else {
						$this->setError(_('Unable to retrieve object ref')._(': ').$objectRef);
						$statusArr[] = false;
					}
				} elseif (preg_match('/^#[0-9]+/', $objectRef)) {
					//Artifact Ref.
					$artifactId = substr($objectRef, 1);
					$artifactObject = artifact_get_object($artifactId);
					if (is_object($artifactObject)) {
						$statusArr[] = $this->addAssociationTo($artifactObject);
					} else {
						$this->setError(_('Unable to retrieve object ref')._(': ').$objectRef);
						$statusArr[] = false;
					}
				} else {
					$this->setError(_('No associate ref object found')._(': ').$objectRef);
					$statusArr[] = false;
				}
			}
		}
		if (!in_array(false, $statusArr)) {
			$status = true;
		}
		return $status;
	}

	function addAssociationTo($object) {
		if (is_object($object)) {
			$objectType = get_class($object);
			$objectRefId = $this->getRefID($object);
			if (!isset($this->associateArray[$objectType][$objectRefId][$object->getID()])) {
				$res = db_query_params('INSERT INTO fusionforge_object_assiociation (from_id, from_object_type, from_ref_id, to_object_type, to_id, to_ref_id)
								VALUES ($1, $2, $3, $4, $5, $6)',
							array($this->getID(), $this->getRealClass($this), $this->getRefID($this), $objectType, $object->getID(), $objectRefId));
				if ($res) {
					$this->associateArray[$objectType][$objectRefId][] = $object->getID();
					return true;
				} else {
					$this->setError(_('Cannot insert association')._(': ').db_error());
				}
			} else {
				$this->setError(_('Association already existing'));
			}
		} else {
			$this->setError(_('Cannot set association to a non-object'));
		}
		return false;
	}

	function removeAssociationTo($object) {
		if (is_object($object)) {
			$objectType = get_class($object);
			$objectRefId = $this->getRefID($object);
			if (isset($this->associateArray[$objectType][$objectRefId][$object->getID()])) {
				$res = db_query_params('DELETE FROM fusionforge_object_assiociation WHERE from_id = $1 AND from_object_type = $2
								AND to_object_type = $3 AND to_id = $4',
							array($this->getID(), $this->getRealClass($this), $objectType, $object->getID()));
				if ($res) {
					unset($this->associateArray[$objectType][$objectRefId][$object->getID()]);
					return true;
				} else {
					$this->setError(_('Cannot delete association')._(': ').db_error());
				}
			} else {
				$this->setError(_('Association does not existing'));
			}
		} else {
			$this->setError(_('Cannot remove association to a non-object'));
		}
		return false;
	}

	function removeAssociationFrom($object) {
		if (is_object($object)) {
			$objectType = get_class($object);
			$objectRefId = $this->getRefID($object);
			if (isset($this->associateArray[$objectType][$objectRefId][$object->getID()])) {
				$res = db_query_params('DELETE FROM fusionforge_object_assiociation WHERE from_id = $1 AND from_object_type = $2
								AND to_object_type = $3 AND to_id = $4',
							array($objectType, $object->getID(), $this->getID(), get_class($this)));
				if ($res) {
					unset($this->associateArray[$objectType][$objectRefId][$object->getID()]);
					return true;
				} else {
					$this->setError(_('Cannot delete association')._(': ').db_error());
				}
			} else {
				$this->setError(_('Association does not existing'));
			}
		} else {
			$this->setError(_('Cannot remove association to a non-object'));
		}
		return false;
	}

	function removeAllAssociations() {
		$res = db_query_params('DELETE FROM fusionforge_object_assiociation WHERE (from_id = $1 AND from_object_type = $2)
											OR (to_id = $1 AND to_object_type $ 4)',
					array($this->getID(), get_class(), $this->getID(), get_class()));
		if ($res) {
			$this->associateToArray = array();
			$this->associateFromArray = array();
			return true;
		} else {
			$this->setError(_('Unable to remove all associations')._(': ').db_error());
		}
		return false;
	}

	function showAssociations($url = false) {
		global $HTML;
		$displayHeader = false;
		if (count($this->getAssociatedTo()) > 0) {
			foreach ($this->getAssociatedTo() as $objectType => $objectRefIds) {
				foreach ($objectRefIds as $objectRefId => $objectIds) {
					if ($this->checkPermWrapper($objectType, $objectRefId)) {
						if (!$displayHeader) {
							$tabletop = array('', _('Associated Object'), _('Associated Object ID'));
							$classth = array('', '', '');
							if ($url !== false) {
								echo html_e('p', array(), _('Remove all association action'));
								$tabletop[] = _('Actions');
								$classth[] = 'unsortable';
							}
							echo $HTML->listTableTop($tabletop, array(), 'sortable', 'sortable_association', $classth);
							$displayHeader = true;
						}
						foreach ($objectIds as $objectId) {
							$object = $this->getRefObject($objectId, $objectRefId, $objectType);
							$cells = array();
							$cells[][] = _('To');
							$cells[][] = $objectType;
							$cells[][] = util_make_link($object->getPermalink(), $objectId);
							if ($url !== false) {
								$cells[][] = _('Remove action');
							} else {
								$cells[][] = '';
							}
							echo $HTML->multiTableRow(array(), $cells);
						}
					}
				}
			}
		}
		if (count($this->getAssociatedFrom()) > 0) {
			foreach ($this->getAssociatedFrom() as $objectType => $objectRefIds) {
				foreach ($objectRefIds as $objectRefId => $objectIds) {
					if ($this->checkPermWrapper($objectType, $objectRefId)) {
						if (!$displayHeader) {
							$tabletop = array('', _('Associated Object'), _('Associated Object ID'));
							$classth = array('', '', '');
							if ($url !== false) {
								echo html_e('p', array(), _('Remove all association action'));
								$tabletop[] = _('Actions');
								$classth[] = 'unsortable';
							}
							echo $HTML->listTableTop($tabletop, array(), 'sortable', 'sortable_association', $classth);
							$displayHeader = true;
						}
						foreach ($objectIds as $objectId) {
							$object = $this->getRefObject($objectId, $objectRefId, $objectType);
							$cells = array();
							$cells[][] = _('From');
							$cells[][] = $objectType;
							$cells[][] = util_make_link($object->getPermalink(), $objectId);
							if ($url !== false) {
								$cells[][] = _('Remove action');
							} else {
								$cells[][] = '';
							}
							echo $HTML->multiTableRow(array(), $cells);
						}
					}
				}
			}
		}
		if ($displayHeader) {
			echo $HTML->listTableBottom();
		} else {
			echo $HTML->information(_('No associated object.'));
		}
	}

	function showAddAssociations($url = false) {
		global $HTML;
		echo _('Add new associate object')._(':');
		if ($url !== false) {
			echo $HTML->openForm(array('action' => $url, 'method' => 'post'));
		}
		echo html_e('input', array('type' => 'text', 'value' => '', 'name' => 'newobjectsassociation', 'title' => _('Use standard reference such #nnn, Dnnn, to add object association. Comma separeted')));
		if ($url !== false) {
			echo html_e('input', array('type' => 'submit', 'value' => _('Add')));
			echo $HTML->closeForm();
		}
	}
}
