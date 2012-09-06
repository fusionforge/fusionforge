<?php
/**
 * FusionForge trackers
 *
 * Copyright 2011, Alcatel-Lucent
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
 * "The Roadmap ("Contribution") has not been tested and/or
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

require_once $gfcommon.'include/Error.class.php';

class Roadmap extends Error {

	var $group;
	var $group_id;

	var $is_admin;

	var $roadmap_id;
	var $name;
	var $enable;
	var $release_order;
	var $is_default;

	var $data_array;

	function __construct($group, $roadmap_id=0, $data=false) {
		$this->Error();

		if (is_object($group)) {
			if ($group->isError()) {
				$this->setError('in Roadmap, '.$group->getErrorMessage());
				return false;
			}
			$this->group = $group;
			$this->group_id = $group->getID();
		}
		else {
			$this->setError('No Valid Group');
			return false;
		}

		/*if (! $this->userCanView()) {
			$this->setPermissionDeniedError();
			$this->data_array = null;
			return false;
		}*/

		$this->roadmap_id = $roadmap_id;
		$this->name = '';
		$this->enable = 1;
		$this->release_order = false;
		$this->is_default = 0;

		$this->data_array = array();
		return $this->_fetchData($data);
	}


	// Public methods

	public function create($name) {
		if (! $this->_isAdmin()) return false;

		$result = db_query_params ('SELECT * FROM roadmap WHERE group_id=$1 AND name=$2',
					array ($this->group_id, $name));
		if ($result && db_numrows($result)) {
				$this->setError(sprintf(_('"%s" roadmap already exists'), $name));
				return false;
		}

		$result = db_query_params('INSERT INTO roadmap (group_id, name, enable, release_order, is_default) VALUES ($1, $2, $3, $4, $5)',
					array ($this->group_id,
						$name,
						$this->enable,
						$this->release_order,
						$this->is_default));
		if (! $result) {
			$this->setError('in create, '.db_error());
			return false;
		}
		$this->roadmap_id = db_insertid($result, 'roadmap', 'roadmap_id');
		$this->name = $name;
		return true;
	}

	public function delete() {
		if (! $this->_isAdmin()) return false;

		$result = db_query_params('DELETE FROM roadmap_list WHERE roadmap_id=$1',
					array ($this->roadmap_id));
		if (! $result) {
			$this->setError('in delete, '.db_error());
			return false;
		}
		$result = db_query_params('DELETE FROM roadmap WHERE roadmap_id=$1',
					array ($this->roadmap_id));
		if (! $result) {
			$this->setError('in delete, '.db_error());
			return false;
		}
		$this->roadmap_id = 0;
		$this->name = '';
		$this->enable = 0;
		$this->release_order = false;
		$this->is_default = 0;
		$this->data_array = array();
		return true;
	}

	public function getID() {
		return $this->roadmap_id;
	}

	public function rename($name) {
		if (! $this->_isAdmin()) return false;

		$result = db_query_params('UPDATE roadmap SET name=$1 WHERE roadmap_id=$2',
					array ($name,
						$this->roadmap_id));
		if (! $result) {
			$this->setError('in rename, '.db_error());
			return false;
		}
		$this->name = $name;
		return true;
	}

	public function getName() {
		return $this->name;
	}

	public function enable() {
		if (! $this->_isAdmin()) return false;

		return $this->_setState(1);
	}

	public function disable() {
		if (! $this->_isAdmin()) return false;

		return $this->_setState(0);
	}

	public function setState($state) {
		if (! $this->_isAdmin()) return false;

		switch ($state) {
			case 0:
			case false:
				$result = $this->disable();
				break;

			case 1:
			case true:
				$result = $this->enable();
				break;

			default:
				$result = false;
				break;
		}

		if (! $result) {
			$this->setError('in setState, '.db_error());
		}

		return $result;
	}

	public function getState() {
		return $this->enable;
	}

	public function setReleaseOrder($release_order) {
		if (! $this->_isAdmin()) return false;

		$result = db_query_params('UPDATE roadmap SET release_order=$1 WHERE roadmap_id=$2',
					array (serialize($release_order),
						$this->roadmap_id));
		if (! $result) {
			$this->setError('in setReleaseOrder, '.db_error());
			return false;
		}
		$this->release_order = $release_order;
		return true;
	}

	public function getReleaseOrder() {
		return $this->release_order;
	}

	public function setDefault($default) {
		if (! $this->_isAdmin()) return false;

		$result = db_query_params('UPDATE roadmap SET is_default=$1 WHERE roadmap_id=$2',
					array ($default,
						$this->roadmap_id));
		if (! $result) {
			$this->setError('in setDefault, '.db_error());
			return false;
		}
		$this->is_default = $default;
		return true;
	}

	public function isDefault($default=-1) {
		if ($default == -1) {
			return $this->is_default;
		}
		else {
			return $this->setDefault($default);
		}
	}

	public function setList($arg1, $arg2=false) {
		if (! $this->_isAdmin()) return false;

		if (is_array($arg1)) {
			db_begin();
			foreach ($arg1 as $artifact_type_id => $field_id) {
				$result = $this->_setList($artifact_type_id, $field_id);
				if (!$result) {
					db_rollback();
					$this->_fetchData();
					$this->setError('in setList, '.db_error());
					return false;
				}
			}
			db_commit();
		}
		else {
			$result = $this->_setList($arg1, $arg2);
			if (!$result) {
				$this->_fetchData();
				$this->setError('in setList, '.db_error());
				return false;
			}
		}

		return true;
	}

	public function getList($artifact_type_id=false) {
		if ($artifact_type_id === false) {
			return $this->data_array;
		}
		else {
			if (array_key_exists($artifact_type_id, $this->data_array)) {
				return $this->data_array[$artifact_type_id];
			}
		}

		return false;
	}

	public function getReleases() {
		$atf = new ArtifactTypeFactory($this->group);
		if (!$atf || !is_object($atf) || $atf->isError()) {
			$this->setError('in getReleases, could not get ArtifactTypeFactory');
		}

		$at_arr = $atf->getArtifactTypes();
		if (!$at_arr || count($at_arr) < 1) {
			return false;
		}

		$artifact_type_list = $this->getList();
		$release_order = $this->getReleaseOrder();

		$releases = array();

		if (is_array($release_order)) {
			foreach ($release_order as $release_value) {
				$releases[$release_value] = false;
			}
		}

		$releases2add = array();
		foreach ($at_arr as $artifact_type) {
			if (!is_object($artifact_type)) {
				//just skip it
			} elseif ($artifact_type->isError()) {
				$this->setError($artifact_type->getErrorMessage());
				// But continue?
			} else {

				if (! array_key_exists($artifact_type->getID(), $artifact_type_list) || ! $artifact_type_list[$artifact_type->getID()]) {
					// This tracker is not used for the roadmap
					continue;
				}
				$field_id = $artifact_type_list[$artifact_type->getID()];

				$ath = new ArtifactTypeHtml($this->group, $artifact_type->getID());

				if (!forge_check_perm ('tracker', $artifact_type->getID(), 'read')) {
					continue;
				}

				$ef_elements = $ath->getExtraFieldElements($field_id);
				if (is_array($ef_elements)) {
					foreach ($ef_elements as $ef_element) {
						$releases2add[] = $ef_element['element_name'];
					}
				}
			}
		}

		usort($releases2add, 'version_compare');
		foreach ($releases2add as $release_value) {
			$releases[$release_value] = true;
		}

		// Remove release values of the release_order field (from 'roadmap' db table) that are no longer used
		if (is_array($release_order)) {
			foreach ($release_order as $release_value) {
				if ($releases[$release_value] === false) {
					unset($releases[$release_value]);
				}
			}
		}

		return array_keys($releases);
	}


	// Private methods

	private function _fetchData($data=false) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				switch ((string)$key) {
					case 'release_order':
						if ($value) {
							$this->release_order = unserialize($value);
						}
						else {
							$this->release_order = array();
						}
						break;

					case 'roadmap_id':
					case 'group_id':
					case 'name':
					case 'enable':
					case 'is_default':
						$this->{$key} = $value;
						break;
				}
			}
			$this->data_array = $this->getList();
		}
		elseif (isset($this->roadmap_id) && $this->roadmap_id) {
			$result = db_query_params ('SELECT * FROM roadmap WHERE roadmap_id=$1',
						array ($this->roadmap_id));
			if (! $result) {
				$this->setError('in _fetchData, '.db_error());
				return false;
			}
			if (db_numrows($result)) {
				$this->name = db_result($result, 0, 'name');
				$this->enable = db_result($result, 0, 'enable');
				$tmp = db_result($result, 0, 'release_order');
				if ($tmp) $this->release_order = unserialize($tmp);
				$this->is_default = db_result($result, 0, 'is_default');
				db_free_result($result);
			}
		}

		if ($this->roadmap_id) {
			$result = db_query_params ('SELECT * FROM roadmap_list WHERE roadmap_id=$1 ORDER BY artifact_type_id',
							array ($this->roadmap_id));
			if (! $result) {
				$this->setError('in _fetchData, '.db_error());
				return false;
			}
			while($entry = db_fetch_array($result)) {
				$this->data_array[$entry['artifact_type_id']] = $entry['field_id'];
			}
			db_free_result($result);
		}

		return true;
	}

	private function _setState($state) {
		$result = db_query_params('UPDATE roadmap SET enable=$1 WHERE group_id=$2 AND name=$3',
					array ($state,
						$this->group_id,
						$this->name));

		if (! $result) {
			return false;
		}
		$this->enable = $state;
		return true;
	}

	private function _insertList($artifact_type_id, $field_id) {
		$result = db_query_params('INSERT INTO roadmap_list (roadmap_id, artifact_type_id, field_id) VALUES ($1, $2, $3)',
					array ($this->roadmap_id,
						$artifact_type_id,
						$field_id));
		if (! $result) {
			return false;
		}
		$this->data_array[$artifact_type_id] = $field_id;
		return true;
	}

	private function _updateList($artifact_type_id, $field_id) {
		$result = db_query_params('UPDATE roadmap_list SET field_id=$1 WHERE roadmap_id=$2 AND artifact_type_id=$3',
					array ($field_id,
						$this->roadmap_id,
						$artifact_type_id));
		if (! $result) {
			return false;
		}
		$this->data_array[$artifact_type_id] = $field_id;
		return true;
	}

	private function _deleteList($artifact_type_id) {
		$result = db_query_params('DELETE FROM roadmap_list WHERE roadmap_id=$1 AND artifact_type_id=$2',
					array ($this->roadmap_id,
						$artifact_type_id));
		if (! $result) {
			return false;
		}
		$this->data_array[$artifact_type_id] = 0;
		return true;
	}

	private function _setList($artifact_type_id, $field_id) {
		if (! $field_id) {
			$result = $this->_deleteList($artifact_type_id);
		}
		elseif (array_key_exists($artifact_type_id, $this->data_array)) {
			$result = $this->_updateList($artifact_type_id, $field_id);
		}
		else {
			$result = $this->_insertList($artifact_type_id, $field_id);
		}

		return $result;
	}

	private function _isAdmin() {
		if (isset($this->is_admin)) {
			if ($this->is_admin) {
				return true;
			}
			else {
				$this->setPermissionDeniedError();
				return false;
			}
		}
		$perm =& $this->group->getPermission();

		if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}

		return true;
	}
}
