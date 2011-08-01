<?php
/**
 * FusionForge roles
 *
 * Copyright 2004, GForge, LLC
 * Copyright 2009-2010, Roland Mas
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

require_once $gfcommon.'include/rbac_texts.php' ;
require_once $gfcommon.'include/RBAC.php' ;

/**
 * TODO: FusionForge roles - Enter description here ...
 *
 */
class Role extends RoleExplicit implements PFO_RoleExplicit {

	var $data_array;
	var $setting_array;
	var $perms_array ;
	var $Group;

	/**
	 * Role($group,$id) - CONSTRUCTOR.
	 *
	 * @param	object	The Group object.
	 * @param	int	The role_id.
	 */
	function Role($Group, $role_id = false) {
		$this->BaseRole();
		if (!$Group || !is_object($Group) || $Group->isError()) {
			$Group = NULL;
		}
		$this->Group =& $Group;

		$hook_params = array();
		$hook_params['role'] =& $this;
		plugin_hook("role_get", $hook_params);

		if (isset ($GLOBALS['default_roles'])) {
			$this->defaults = array_merge_recursive($this->defaults,
								$GLOBALS['default_roles']);
			foreach ($this->defaults as $k => $v) {
				if (!array_key_exists($GLOBALS['default_roles'], $k)) {
					unset($this->defaults[$k]);
				}
			}
		}

		if (!$role_id) {
			//setting up an empty object
			//probably going to call create()
			return true;
		}
		return $this->fetchData($role_id);
	}

	/**
	 * setName - set the name of this role.
	 *
	 * @param	string	The new name of this role.
	 * @return	boolean	True if updated OK
	 */
	function setName($role_name) { // From the PFO spec
		if ($role_name == '') {
			$this->setError('Cannot set a role name to empty');
			return false;
		}
		if ($this->getName() != stripslashes($role_name)) {
			db_begin();
			if ($this->Group == NULL) {
				$res = db_query_params('SELECT role_name FROM pfo_role WHERE home_group_id IS NULL AND role_name=$1',
						       array(htmlspecialchars($role_name)));
				if (db_numrows($res)) {
					$this->setError('Cannot create a role with this name (already used)');
					db_rollback();
					return false;
				}
			} else {
				$res = db_query_params('SELECT role_name FROM pfo_role WHERE home_group_id=$1 AND role_name=$2',
						       array($this->Group->getID(), htmlspecialchars($role_name)));
				if (db_numrows($res)) {
					$this->setError('Cannot create a role with this name (already used)');
					db_rollback();
					return false;
				}
			}
			$res = db_query_params('UPDATE pfo_role SET role_name=$1 WHERE role_id=$2',
					       array(htmlspecialchars($role_name),
						     $this->getID()));
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError('update::name::'.db_error());
				return false;
			}
			db_commit();
		}
		return true;
	}

	/**
	 * isPublic - is this role public (accessible from projects
	 * other than its home project)?
	 *
	 * @return	boolean True if public
	 */
	function isPublic() {	// From the PFO spec
		return $this->data_array['is_public'];
	}

	/**
	 * setPublic - set the public flag for this role.
	 *
	 * @param	boolean	The new value of the flag.
	 * @return	boolean	True if updated OK
	 */
	function setPublic($flag) { // From the PFO spec
		$res = db_query_params('UPDATE pfo_role SET is_public=$1 WHERE role_id=$2',
					array($flag?'true':'false',
					       $this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('update::is_public::'.db_error());
			return false;
		}
		return true;
	}

	function getHomeProject() { // From the PFO spec
		return $this->Group;
	}

	/**
	 * create - create a new role in the database.
	 *
	 * @param	string	The name of the role.
	 * @param	array	A multi-dimensional array of data in this format: $data['section_name']['ref_id']=$val
	 * @return	integer	The id on success or false on failure.
	 */
	function create($role_name, $data, $newproject=false) {
		if ($this->Group == NULL) {
			if (!forge_check_global_perm ('forge_admin')) {
				$this->setPermissionDeniedError();
				return false;
			}
		}
		if ($newproject) {
			if (!forge_check_global_perm ('approve_projects')) {
				$this->setPermissionDeniedError();
				return false;
			}
		} elseif (!forge_check_perm ('project_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		if ($role_name == '') {
			$this->setError('Cannot create a role with an empty name');
			return false;
		}

		db_begin();
		if ($this->Group == NULL) {
			$res = db_query_params('SELECT role_name FROM pfo_role WHERE home_group_id IS NULL AND role_name=$1',
					       array (htmlspecialchars($role_name)));
			if (db_numrows($res)) {
				$this->setError('Cannot create a role with this name (already used)');
				db_rollback () ;
				return false;
			}
		} else {
			$res = db_query_params('SELECT role_name FROM pfo_role WHERE home_group_id=$1 AND role_name=$2',
					       array ($this->Group->getID(), htmlspecialchars($role_name)));
			if (db_numrows($res)) {
				$this->setError('Cannot create a role with this name (already used)');
				db_rollback () ;
				return false;
			}
		}

		if ($this->Group == NULL) {
			$res = db_query_params ('INSERT INTO pfo_role (role_name) VALUES ($1)',
						array (htmlspecialchars($role_name))) ;
		} else {
			$res = db_query_params ('INSERT INTO pfo_role (home_group_id, role_name) VALUES ($1, $2)',
						array ($this->Group->getID(),
						       htmlspecialchars($role_name))) ;
		}
		if (!$res) {
			$this->setError('create::'.db_error());
			db_rollback();
			return false;
		}
		$role_id=db_insertid($res,'pfo_role','role_id');
		if (!$role_id) {
			$this->setError('create::db_insertid::'.db_error());
			db_rollback();
			return false;
		}
		$this->data_array['role_id'] = $role_id ;
		$this->data_array['role_name'] = $role_name ;

		$this->update ($role_name, $data) ;

		$this->normalizeData () ;

		if (!$this->fetchData($role_id)) {
			db_rollback();
			return false;
		}
		db_commit();
		return $role_id;
	}

	/**
	 * TODO: Enter description here ...
	 * @param unknown_type $name
	 * @return Ambigous <number, boolean, contents>|boolean
	 */
	function createDefault($name) {
		if ($this->Group == NULL) {
			return $this->create($name,array(),true);
		}

		if (array_key_exists ($name, $this->defaults)) {
			$arr =& $this->defaults[$name];
		} else {
			$arr = array () ;
		}

		$data = array();
		foreach ($arr as $k => $v) {
			$data[$k][$this->Group->getID()]= $v;

			if ($k == 'new_forum') {
				$res = db_query_params('SELECT group_forum_id FROM forum_group_list WHERE group_id=$1',
							array ($this->Group->getID())) ;
				if (!$res) {
					$this->setError('Error: Forum'.db_error());
					return false;
				}
				for ($j=0; $j<db_numrows($res); $j++) {
					$data['forum'][db_result($res, $j, 'group_forum_id')]= $v;
				}
			} elseif ($k == 'new_pm') {
				$res = db_query_params('SELECT group_project_id FROM project_group_list WHERE group_id=$1',
							array ($this->Group->getID())) ;
				if (!$res) {
					$this->setError('Error: TaskMgr'.db_error());
					return false;
				}
				for ($j=0; $j<db_numrows($res); $j++) {
					$data['pm'][db_result($res, $j, 'group_project_id')]= $v;
				}
			} elseif ($k == 'new_tracker') {
				$res = db_query_params('SELECT group_artifact_id FROM artifact_group_list WHERE group_id=$1',
						       array ($this->Group->getID())) ;
				if (!$res) {
					$this->setError('Error: Tracker'.db_error());
					return false;
				}
				for ($j=0; $j<db_numrows($res); $j++) {
					$data['tracker'][db_result($res, $j, 'group_artifact_id')]= $v;
				}
			}
		}

		return $this->create($name,$data,false);
	}

	/**
	 * delete - delete a role in the database.
	 *
	 * @return	boolean	True on success or false on failure.
	 */
	function delete() {
		if ($this->Group == NULL) {
			if (!forge_check_global_perm ('forge_admin')) {
				$this->setPermissionDeniedError();
				return false;
			}
		} elseif (!forge_check_perm('project_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		$res=db_query_params('SELECT user_id FROM pfo_user_role WHERE role_id=$1',
				     array($this->getID()));
		assert($res);
		if (db_numrows($res) > 0) {
			$this->setError(_('Cannot remove a non empty role.'));
			return false;
		}

		$res=db_query_params('DELETE FROM pfo_user_role WHERE role_id=$1',
				     array($this->getID())) ;
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('delete::name::'.db_error());
			db_rollback();
			return false;
		}

		$res=db_query_params('DELETE FROM role_project_refs WHERE role_id=$1',
				     array($this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('delete::name::'.db_error());
			db_rollback();
			return false;
		}

		$res=db_query_params('DELETE FROM pfo_role_setting WHERE role_id=$1',
				     array($this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('delete::name::'.db_error());
			db_rollback();
			return false;
		}

		$res=db_query_params('DELETE FROM pfo_role WHERE role_id=$1',
				     array($this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('delete::name::'.db_error());
			db_rollback();
			return false;
		}

	}

	/**
	 * setUser -
	 * @param	int	User ID
	 *
	 * @return	boolean	True on success or false on failure.
	 */
	function setUser($user_id) {
		global $SYS;

		if ($this->Group == NULL) {
			if (!forge_check_global_perm ('forge_admin')) {
				$this->setPermissionDeniedError();
				return false;
			}
		} elseif (!forge_check_perm ('project_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		return $this->addUser (user_get_object($user_id)) ;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
