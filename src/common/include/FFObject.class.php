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
require_once $gfcommon.'include/utils_crossref.php';

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

	/**
	 *
	 * @var int	$associationCounter
	 */
	var $associationCounter = 0;

	function __construct($id = false, $objectType = false) {
		parent::__construct();
		if (forge_get_config('use_object_associations') && $id && $objectType) {
			$res = db_query_params('SELECT to_object_type, to_ref_id, to_id FROM fusionforge_object_assiociation WHERE from_id = $1 AND from_object_type = $2',
						array($id, $objectType));
			if ($res && db_numrows($res)) {
				while ($arr = db_fetch_array($res)) {
					$this->associatedToArray[$arr[0]][$arr[1]][] = $arr[2];
					$this->associationCounter++;
				}
			}
			$res = db_query_params('SELECT from_object_type, from_ref_id, from_id FROM fusionforge_object_assiociation WHERE to_id = $1 AND to_object_type = $2',
						array($id, $objectType));
			if ($res && db_numrows($res)) {
				while ($arr = db_fetch_array($res)) {
					$this->associatedFromArray[$arr[0]][$arr[1]][] = $arr[2];
					$this->associationCounter++;
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

	function getAssociationCounter() {
		return $this->associationCounter;
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
			case 'FRSRelease':
				return $object->FRSPackage->getID();
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
			case 'FRSRelease':
				return $object->FRSPackage->Group->getID();
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
			case 'FRSRelease':
				return 'FRSRelease';
				break;
		}
	}

	function getLinkObject($objectId, $objectRefId, $objectType) {
		switch ($objectType) {
			case 'Document':
				return _documentid2url($objectId, $objectRefId);
				break;
			case 'Artifact':
				return _artifactid2url($objectId);
				break;
			case 'FRSRelease':
				return _frsreleaseid2url($objectId);
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
			case 'FRSRelease':
				return forge_check_perm('frs', $objectRefId, 'file');
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
				} elseif (preg_match('/^[Rr][0-9]+/', $objectRef)) {
					//Artifact Ref.
					$frsreleaseid = substr($objectRef, 1);
					$frsreleaseObject = frsrelease_get_object($frsreleaseid);
					if (is_object($frsreleaseObject)) {
						$statusArr[] = $this->addAssociationTo($frsreleaseObject);
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
			if ((isset($this->associatedToArray[$objectType][$objectRefId]) && !in_array($object->getID(), $this->associatedToArray[$objectType][$objectRefId]))
				|| !isset($this->associatedToArray[$objectType][$objectRefId])) {
				$res = db_query_params('INSERT INTO fusionforge_object_assiociation (from_id, from_object_type, from_ref_id, to_object_type, to_id, to_ref_id)
								VALUES ($1, $2, $3, $4, $5, $6)',
							array($this->getID(), $this->getRealClass($this), $this->getRefID($this), $objectType, $object->getID(), $objectRefId));
				if ($res) {
					$this->associatedToArray[$objectType][$objectRefId][] = $object->getID();
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

	function removeAssociationTo($objectId, $objectRefId, $objectType) {
		if (isset($this->associatedToArray[$objectType][$objectRefId]) && in_array($objectId, $this->associatedToArray[$objectType][$objectRefId])) {
			$res = db_query_params('DELETE FROM fusionforge_object_assiociation WHERE from_id = $1 AND from_object_type = $2
							AND to_object_type = $3 AND to_id = $4',
						array($this->getID(), $this->getRealClass($this), $objectType, $objectId));
			if ($res) {
				unset($this->associatedToArray[$objectType][$objectRefId][$objectId]);
				return true;
			} else {
				$this->setError(_('Cannot delete association')._(': ').db_error());
			}
		} else {
			$this->setError(_('Association To does not existing'));
		}
		return false;
	}

	function removeAssociationFrom($objectId, $objectRefId, $objectType) {
		if (isset($this->associatedFromArray[$objectType][$objectRefId]) && in_array($objectId, $this->associatedFromArray[$objectType][$objectRefId])) {
			$res = db_query_params('DELETE FROM fusionforge_object_assiociation WHERE from_id = $1 AND from_object_type = $2
							AND to_object_type = $3 AND to_id = $4',
						array($objectId, $objectType, $this->getRealClass($this), $this->getID()));
			if ($res) {
				unset($this->associatedFromArray[$objectType][$objectRefId][$objectId]);
				return true;
			} else {
				$this->setError(_('Cannot delete association')._(': ').db_error());
			}
		} else {
			$this->setError(_('Association From does not existing'));
		}
		return false;
	}

	function removeAllAssociations() {
		$res = db_query_params('DELETE FROM fusionforge_object_assiociation WHERE (from_id = $1 AND from_object_type = $2)
											OR (to_id = $1 AND to_object_type = $2)',
					array($this->getID(), get_class($this)));
		if ($res) {
			$this->associatedToArray = array();
			$this->associatedFromArray = array();
			return true;
		} else {
			$this->setError(_('Unable to remove all associations')._(': ').db_error());
		}
		return false;
	}

	function showAssociations($url = false) {
		global $HTML;
		$displayHeader = false;
		$content = '';
		if (count($this->getAssociatedTo()) > 0) {
			foreach ($this->getAssociatedTo() as $objectType => $objectRefIds) {
				foreach ($objectRefIds as $objectRefId => $objectIds) {
					if ($this->checkPermWrapper($objectType, $objectRefId)) {
						if (!$displayHeader) {
							$tabletop = array('', _('Associated Object'), _('Associated Object ID'));
							$classth = array('', '', '');
							if ($url !== false) {
								$content .= html_e('p', array(), _('Remove all associations')._(': ').util_make_link($url.'&link=any', $HTML->getDeletePic(_('Drop all associated from and to objects.'))));
								$tabletop[] = _('Actions');
								$classth[] = 'unsortable';
							}
							$content .= $HTML->listTableTop($tabletop, array(), 'sortable', 'sortable_association', $classth);
							$displayHeader = true;
						}
						foreach ($objectIds as $objectId) {
							$cells = array();
							$cells[][] = _('To');
							$cells[][] = $objectType;
							$cells[][] = $this->getLinkObject($objectId, $objectRefId, $objectType);
							if ($url !== false) {
								$cells[][] = util_make_link($url.'&link=to&objecttype='.$objectType.'&objectrefid='.$objectRefId.'&objectid='.$objectId, $HTML->getDeletePic(_('Remove this association'), _('Remove this association')));
							}
							$content .= $HTML->multiTableRow(array(), $cells);
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
								$content .= html_e('p', array(), _('Remove all associations')._(': ').util_make_link($url.'&link=any', $HTML->getDeletePic(_('Remove all associations'), _('Remove all associations'))));
								$tabletop[] = _('Actions');
								$classth[] = 'unsortable';
							}
							$content .= $HTML->listTableTop($tabletop, array(), 'sortable', 'sortable_association', $classth);
							$displayHeader = true;
						}
						foreach ($objectIds as $objectId) {
							$cells = array();
							$cells[][] = _('From');
							$cells[][] = $objectType;
							$cells[][] = $this->getLinkObject($objectId, $objectRefId, $objectType);
							if ($url !== false) {
								$cells[][] = util_make_link($url.'&link=from&objecttype='.$objectType.'&objectrefid='.$objectRefId.'&objectid='.$objectId, $HTML->getDeletePic(_('Remove this association'), _('Remove this association')));
							}
							$content .= $HTML->multiTableRow(array(), $cells);
						}
					}
				}
			}
		}
		if ($displayHeader) {
			$content .= $HTML->listTableBottom();
		} else {
			$content .= $HTML->information(_('No associated object.'));
		}
		return $content;
	}

	function showAddAssociations($url = false) {
		global $HTML;
		$content = html_ao('span', array()). _('Add new associate object')._(':');
		if ($url !== false) {
			$content .= $HTML->openForm(array('action' => $url, 'method' => 'post'));
		}
		$content .= html_e('input', array('type' => 'text', 'value' => '', 'name' => 'newobjectsassociation', 'title' => _('Use standard reference such #nnn, Dnnn, to add object association. Comma separeted')));
		if ($url !== false) {
			$content .= html_e('input', array('type' => 'submit', 'value' => _('Add')));
			$content .= $HTML->closeForm();
		}
		$content .= html_ac(html_ap() -1);
		return $content;
	}
}
