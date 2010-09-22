<?php
/**
 * FusionForge roles
 *
 * Copyright 2004, GForge, LLC
 * Copyright 2009-2010, Roland Mas
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

require_once $gfcommon.'include/rbac_texts.php' ;
require_once $gfcommon.'include/RBAC.php' ;

class Role extends RoleExplicit implements PFO_RoleExplicit {

	var $data_array;
	var $setting_array;
	var $perms_array ;
	var $Group;

	/**
	 *  Role($group,$id) - CONSTRUCTOR.
	 *
	 *  @param  object	 The Group object.
	 *  @param  int	 The role_id.
	 */
	function Role ($Group,$role_id=false) {
		$this->BaseRole();
		if (USE_PFO_RBAC) {
			if (!$Group || !is_object($Group) || $Group->isError()) {
				$Group = NULL ;
			}
		} else {
			if (!$Group || !is_object($Group) || $Group->isError()) {
				$this->setError('Role::'.$Group->getErrorMessage());
				return false;
			}
		}
		$this->Group =& $Group;

		$hook_params = array ();
		$hook_params['role'] =& $this;
		plugin_hook ("role_get", $hook_params);

		if (isset ($GLOBALS['default_roles'])) {
			$this->defaults = array_merge_recursive ($this->defaults,
								 $GLOBALS['default_roles']) ;
			foreach ($this->defaults as $k => $v) {
				if (!array_key_exists ($GLOBALS['default_roles'], $k)) {
					unset ($this->defaults[$k]) ;
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
	 *	setName - set the name of this role.
	 *
	 *	@param	string	The new name of this role.
	 *      @return boolean True if updated OK
	 */
	function setName ($role_name) { // From the PFO spec
		if ($this->getName() != stripslashes($role_name)) {
			if (USE_PFO_RBAC) {
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
				$res = db_query_params ('UPDATE pfo_role SET role_name=$1 WHERE role_id=$2',
							array (htmlspecialchars($role_name),
							       $this->getID())) ;
				if (!$res || db_affected_rows($res) < 1) {
					$this->setError('update::name::'.db_error());
					return false;
				}
				db_commit();
			} else {
				// Check if role_name is not already used.
				$res = db_query_params('SELECT role_name FROM role WHERE group_id=$1 AND role_name=$2',
						       array ($this->Group->getID(), htmlspecialchars($role_name)));
				if (db_numrows($res)) {
					$this->setError('Cannot create a role with this name (already used)');
					return false;
				}
				
				$res = db_query_params ('UPDATE role SET role_name=$1 WHERE group_id=$2 AND role_id=$3',
							array (htmlspecialchars($role_name),
							       $this->Group->getID(),
							       $this->getID())) ;
				if (!$res || db_affected_rows($res) < 1) {
					$this->setError('update::name::'.db_error());
					return false;
				}
			}
		}
		return true ;
	}

	/**
	 *	isPublic - is this role public (accessible from projects
	 *      other than its home project)?
	 *
	 *	@return	boolean True if public
	 */
	function isPublic() {	// From the PFO spec
		return $this->data_array['is_public'];
	}

	/**
	 *	setPublic - set the public flag for this role.
	 *
	 *	@param	boolean	The new value of the flag.
	 *      @return boolean True if updated OK
	 */
	function setPublic ($flag) { // From the PFO spec
		$res = db_query_params ('UPDATE pfo_role SET is_public=$1 WHERE role_id=$1',
					array ($flag,
					       $this->getID())) ;
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('update::is_public::'.db_error());
			return false;
		}
		return true;
	}

	function getHomeProject () { // From the PFO spec
		return $this->Group ;
	}

	/**
	 *	create - create a new role in the database.
	 *
	 *	@param	string	The name of the role.
	 *	@param	array	A multi-dimensional array of data in this format: $data['section_name']['ref_id']=$val
	 *	@return integer	The id on success or false on failure.
	 */
	function create($role_name,$data) {
		if (USE_PFO_RBAC) {
			if ($this->Group == NULL) {
				if (!forge_check_global_perm ('forge_admin')) {
					$this->setPermissionDeniedError();
					return false;
				}
			} elseif (!forge_check_perm ('project_admin', $this->Group->getID())) {
				$this->setPermissionDeniedError();
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
		} else {
		$perm =& $this->Group->getPermission ();
		if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}

		// Check if role_name is not already used.
		$res = db_query_params('SELECT role_name FROM role WHERE group_id=$1 AND role_name=$2',
			array ($this->Group->getID(), htmlspecialchars($role_name)));
		if (db_numrows($res)) {
			$this->setError('Cannot create a role with this name (already used)');
			return false;
		}

		db_begin();
		$res = db_query_params ('INSERT INTO role (group_id, role_name) VALUES ($1, $2)',
					array ($this->Group->getID(),
					       htmlspecialchars($role_name))) ;
		if (!$res) {
			$this->setError('create::'.db_error());
			db_rollback();
			return false;
		}
		$role_id=db_insertid($res,'role','role_id');
		if (!$role_id) {
			$this->setError('create::db_insertid::'.db_error());
			db_rollback();
			return false;
		}

		$arr1 = array_keys($data);
		for ($i=0; $i<count($arr1); $i++) {	
		//	array_values($Report->adjust_days)
			$arr2 = array_keys($data[$arr1[$i]]);
			for ($j=0; $j<count($arr2); $j++) {
				$usection_name=$arr1[$i];
				$uref_id=$arr2[$j];
				$uvalue=$data[$arr1[$i]][$arr2[$j]];
				if (!$uref_id) {
					$uref_id=0;
				}
				if (!$uvalue) {
					$uvalue=0;
				}
				$res = db_query_params ('INSERT INTO role_setting (role_id,section_name,ref_id,value) VALUES ($1,$2,$3,$4)',
							array ($role_id,
							       $usection_name,
							       $uref_id,
							       $uvalue)) ;
				if (!$res) {
					$this->setError('create::insertsetting::'.db_error());
					db_rollback();
					return false;
				}
			}
		}
		}
		if (!$this->fetchData($role_id)) {
			db_rollback();
			return false;
		}
		db_commit();
		return $role_id;
	}

	function createDefault($name) {
		if ($this->Group == NULL) {
			return $this->create($name,array());
		}
		
		if (array_key_exists ($name, $this->defaults)) {
			$arr =& $this->defaults[$name];
		} else {
			$arr = array () ;
		}
		$keys = array_keys($arr);
		$data = array();
		for ($i=0; $i<count($keys); $i++) {

			if ($keys[$i] == 'forum') {
				$res = db_query_params ('SELECT group_forum_id FROM forum_group_list WHERE group_id=$1',
							array ($this->Group->getID())) ;
				if (!$res) {
					$this->setError('Error: Forum'.db_error());
					return false;
				}
				for ($j=0; $j<db_numrows($res); $j++) {
					$data[$keys[$i]][db_result($res,$j,'group_forum_id')]= $arr[$keys[$i]];
				}
			} elseif ($keys[$i] == 'pm') {
				$res = db_query_params ('SELECT group_project_id FROM project_group_list WHERE group_id=$1',
							array ($this->Group->getID())) ;
				if (!$res) {
					$this->setError('Error: TaskMgr'.db_error());
					return false;
				}
				for ($j=0; $j<db_numrows($res); $j++) {
					$data[$keys[$i]][db_result($res,$j,'group_project_id')]= $arr[$keys[$i]];
				}
			} elseif ($keys[$i] == 'tracker') {
				$res = db_query_params ('SELECT group_artifact_id FROM artifact_group_list WHERE group_id=$1',
							array ($this->Group->getID())) ;
				if (!$res) {
					$this->setError('Error: Tracker'.db_error());
					return false;
				}
				for ($j=0; $j<db_numrows($res); $j++) {
					$data[$keys[$i]][db_result($res,$j,'group_artifact_id')]= $arr[$keys[$i]];
				}
			} else {
				$data[$keys[$i]][0]= $arr[$keys[$i]];
			}
		}

		return $this->create($name,$data);
	}
	
	function normalizeDataForSection (&$new_sa, $section) {
		if (array_key_exists ($section, $this->setting_array)) {
			$new_sa[$section][0] = $this->setting_array[$section][0] ;
		} elseif (array_key_exists ($this->data_array['role_name'], $this->defaults)
			  && array_key_exists ($section, $this->defaults[$this->data_array['role_name']])) {
			$new_sa[$section][0] = $this->defaults[$this->data_array['role_name']][$section] ;
		} else {
			$new_sa[$section][0] = 0 ;
		}
		return $new_sa ;
	}

	function normalizePermsForSection (&$new_pa, $section, $refid) {
		if (array_key_exists ($section, $this->perms_array)
		    && array_key_exists ($refid, $this->perms_array[$section])) {
			$new_pa[$section][$refid] = $this->perms_array[$section][$refid] ;
		} elseif (array_key_exists ($this->data_array['role_name'], $this->defaults)
			  && array_key_exists ($section, $this->defaults[$this->data_array['role_name']])) {
			$new_pa[$section][$refid] = $this->defaults[$this->data_array['role_name']][$section] ;
		} else {
			$new_pa[$section][$refid] = 0 ;
		}
		return $new_pa ;
	}

	function normalizeData () { // From the PFO spec
		$this->fetchData ($this->getID()) ;

		$projects = $this->getLinkedProjects() ;		
		$new_sa = array () ;
		$new_pa = array () ;
		
		// Add missing settings
		// ...project-wide settings
		if (USE_PFO_RBAC) {
			$arr = array ('project_read', 'project_admin', 'frs', 'scm', 'docman', 'tracker_admin', 'new_tracker', 'forum_admin', 'new_forum', 'pm_admin', 'new_pm', 'webcal') ;
			foreach ($projects as $p) {
				foreach ($arr as $section) {
					$this->normalizePermsForSection ($new_pa, $section, $p->getID()) ;
				}
			}
			$this->normalizePermsForSection ($new_pa, 'forge_admin', -1) ;
			$this->normalizePermsForSection ($new_pa, 'approve_projects', -1) ;
			$this->normalizePermsForSection ($new_pa, 'approve_news', -1) ;
			$this->normalizePermsForSection ($new_pa, 'forge_stats', -1) ;
		} else {
			$arr = array ('projectadmin', 'frs', 'scm', 'docman', 'forumadmin', 'trackeradmin', 'newtracker', 'pmadmin', 'newpm', 'webcal') ;
			foreach ($arr as $section) {
				$this->normalizeDataForSection ($new_sa, $section) ;
			}
		}

		$hook_params = array ();
		$hook_params['role'] =& $this;
		$hook_params['new_sa'] =& $new_sa ; 
		$hook_params['new_pa'] =& $new_pa ; 
		plugin_hook ("role_normalize", $hook_params);

		// ...tracker-related settings
		$new_sa['tracker'] = array () ;
		$new_pa['tracker'] = array () ;
		foreach ($projects as $p) {
			$atf = new ArtifactTypeFactory ($p) ;
			$trackers = $atf->getArtifactTypes () ;
			foreach ($trackers as $t) {
				if (USE_PFO_RBAC) {
					if (array_key_exists ('tracker', $this->perms_array)
					    && array_key_exists ($t->getID(), $this->perms_array['tracker']) ) {
						$new_pa['tracker'][$t->getID()] = $this->perms_array['tracker'][$t->getID()] ;
					} elseif (array_key_exists ('new_tracker', $this->perms_array)
					    && array_key_exists ($p->getID(), $this->perms_array['new_tracker']) ) {
						$new_pa['tracker'][$t->getID()] = $new_pa['new_tracker'][$p->getID()] ;
					}
				} else {
					if (array_key_exists ('tracker', $this->setting_array)
					    && array_key_exists ($t->getID(), $this->setting_array['tracker']) ) {
						$new_sa['tracker'][$t->getID()] = $this->setting_array['tracker'][$t->getID()] ;
					} else {
						$new_sa['tracker'][$t->getID()] = $new_sa['newtracker'][0] ;
					}
				}
			}
		}
		
		// ...forum-related settings
		$new_sa['forum'] = array () ;
		$new_pa['forum'] = array () ;
		foreach ($projects as $p) {
			$ff = new ForumFactory ($p) ;
			$forums = $ff->getForums () ;
			foreach ($forums as $f) {
				if (USE_PFO_RBAC) {
					if (array_key_exists ('forum', $this->perms_array)
					    && array_key_exists ($f->getID(), $this->perms_array['forum']) ) {
						$new_pa['forum'][$f->getID()] = $this->perms_array['forum'][$f->getID()] ;
					} elseif (array_key_exists ('new_forum', $this->perms_array)
					    && array_key_exists ($p->getID(), $this->perms_array['new_forum']) ) {
						$new_pa['forum'][$f->getID()] = $new_pa['new_forum'][$p->getID()] ;
					}
				} else {
					if (array_key_exists ('forum', $this->setting_array)
					    && array_key_exists ($f->getID(), $this->setting_array['forum']) ) {
						$new_sa['forum'][$f->getID()] = $this->setting_array['forum'][$f->getID()] ;
					} else {
						$new_sa['forum'][$f->getID()] = $new_sa['newforum'][0] ;
					}
				}
			}
		}
		
		// ...pm-related settings
		$new_sa['pm'] = array () ;
		$new_pa['pm'] = array () ;
		foreach ($projects as $p) {
			$pgf = new ProjectGroupFactory ($p) ;
			$pgs = $pgf->getProjectGroups () ;
			foreach ($pgs as $g) {
				if (USE_PFO_RBAC) {
					if (array_key_exists ('pm', $this->perms_array)
					    && array_key_exists ($g->getID(), $this->perms_array['pm']) ) {
						$new_pa['pm'][$g->getID()] = $this->perms_array['pm'][$g->getID()] ;
					} elseif (array_key_exists ('new_pm', $this->perms_array)
					    && array_key_exists ($p->getID(), $this->perms_array['new_pm']) ) {
						$new_pa['pm'][$g->getID()] = $new_pa['new_pm'][$p->getID()] ;
					}
				} else {
					if (array_key_exists ('pm', $this->setting_array)
					    && array_key_exists ($g->getID(), $this->setting_array['pm']) ) {
						$new_sa['pm'][$g->getID()] = $this->setting_array['pm'][$g->getID()] ;
					} else {
						$new_sa['pm'][$g->getID()] = $new_sa['newpm'][0] ;
					}
				}
			}
		}
		
		// Save
		if (USE_PFO_RBAC) {
			$this->update ($this->data_array['role_name'], $new_pa) ;
		} else {
			$this->update ($this->data_array['role_name'], $new_sa) ;
		}
		return true;
	}

	/**
	 *	delete - delete a role in the database.
	 *
	 *	@return	boolean	True on success or false on failure.
	 */
	function delete() {
		if (USE_PFO_RBAC) {
			if ($this->Group == NULL) {
				if (!forge_check_global_perm ('forge_admin')) {
					$this->setPermissionDeniedError();
					return false;
				}
			} elseif (!forge_check_perm ('project_admin', $this->Group->getID())) {
				$this->setPermissionDeniedError();
				return false;
			}
			
			$res=db_query_params('SELECT user_id FROM pfo_user_role WHERE role_id=$1',
					     array($this->getID()));
			assert($res);
			if (db_numrows($res) > 0) {
				$this->setError('Cannot remove a non empty role.');
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
					     array($this->getID())) ;
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError('delete::name::'.db_error());
				db_rollback();
				return false;
			}
			
			$res=db_query_params('DELETE FROM pfo_role_setting WHERE role_id=$1',
					     array($this->getID())) ;
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError('delete::name::'.db_error());
				db_rollback();
				return false;
			}
			
			$res=db_query_params('DELETE FROM pfo_role WHERE role_id=$1',
					     array($this->getID())) ;
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError('delete::name::'.db_error());
				db_rollback();
				return false;
			}
		} else {
			if (!is_numeric($this->getID())) {
				$this->setError('Role::delete() role_id is not an integer');
				return false;
			}
			
			//	Cannot delete role_id=1
			if ($this->getID() == 1) {
				$this->setError('Cannot Delete Default Role.');
				return false;
			}
			$perm =& $this->Group->getPermission();
			if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
				$this->setPermissionDeniedError();
				return false;
			}
			
			$res=db_query_params('SELECT user_id FROM user_group WHERE role_id=$1',
					     array($this->getID()));
			assert($res);
			if (db_numrows($res) > 0) {
				$this->setError('Cannot remove a non empty role.');
				return false;
			}
		
			db_begin();
			
			$res=db_query_params('DELETE FROM role WHERE group_id=$1 AND role_id=$2',
					     array($this->Group->getID(), $this->getID())) ;
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError('delete::name::'.db_error());
				db_rollback();
				return false;
			}
			
			db_commit();
			
			return true;
		}
	}

	function setUser($user_id) {
		global $SYS;
		if (USE_PFO_RBAC) {
			if ($this->Group == NULL) {
				if (!forge_check_global_perm ('forge_admin')) {
					$this->setPermissionDeniedError();
					return false;
				}
			} elseif (!forge_check_perm ('project_admin', $this->Group->getID())) {
				$this->setPermissionDeniedError();
				return false;
			}
		} else {
			$perm =& $this->Group->getPermission ();
			if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
				$this->setPermissionDeniedError();
				return false;
			}
		}

		db_begin();

		//
		//	See if role is actually changing
		//
		$res = db_query_params ('SELECT role_id FROM user_group WHERE user_id=$1 AND group_id=$2',
					array ($user_id,
					       $this->Group->getID())) ;
		$old_roleid=db_result($res,0,0);
		if ($this->getID() == $old_roleid) {
			db_commit();
			return true;
		}
		//
		//	Get the old role so we can compare new values to old
		//
		$oldrole= new Role($this->Group,$old_roleid);
		if (!$oldrole || !is_object($oldrole) || $oldrole->isError()) {
			$this->setError($oldrole->getErrorMessage());
			db_rollback();
			return false;
		}

		//
		//	Iterate each setting to see if it's changing
		//	If not, no sense updating it
		//
		$arr1 = array_keys($this->setting_array);
		for ($i=0; $i<count($arr1); $i++) {	
		//	array_values($Report->adjust_days)
			$arr2 = array_keys($this->setting_array[$arr1[$i]]);
			for ($j=0; $j<count($arr2); $j++) {
				$usection_name=$arr1[$i];
				$uref_id=$arr2[$j];
				$uvalue=$this->setting_array[$usection_name][$uref_id];
				if (!$uref_id) {
					$uref_id=0;
				}
				if (!$uvalue) {
					$uvalue=0;
				}
				//
				//	See if this setting changed. If so, then update it
				//
	//			if (($this->getVal($usection_name,$uref_id) != $oldrole->getVal($usection_name,$uref_id)) || ($old_roleid == 1)) {
					if ($usection_name == 'frs') {
						$update_usergroup=true;
					} elseif ($usection_name == 'scm') {
						//TODO - Shell should be separate flag
						//  If user acquired admin access to CVS,
						//  one to be given normal shell on CVS machine,
						//  else - restricted.
						//
						$cvs_flags=$this->getVal('scm',0);
						$res2 = db_query_params ('UPDATE user_group SET cvs_flags=$1 WHERE user_id=$2 AND group_id=$3',
									 array ($cvs_flags,
										$user_id,
										$this->Group->getID())) ;
						if (!$res2) {
							$this->setError('update::scm::'.db_error());
							db_rollback();
							return false;
						}
						// I have doubt the following is usefull
						// This is probably buggy if used
						if ($cvs_flags>1) {
							if (!$SYS->sysUserSetAttribute($user_id,"debGforgeCvsShell","/bin/bash")) {
								$this->setError($SYS->getErrorMessage());
								db_rollback();
								return false;
							}
						} else {
							if (!$SYS->sysUserSetAttribute($user_id,"debGforgeCvsShell","/bin/cvssh")) {
								$this->setError($SYS->getErrorMessage());
								db_rollback();
								return false;
							}
						}

						//
						//  If user acquired at least commit access to CVS,
						//  one to be promoted to CVS group, else, demoted.
						//  When we add the user we also check he has a shell as a group member
						//  When we remove we only check for SCM (cvs_only=1)
						//
						if ($uvalue>0) {
//echo "<h3>Role::setUser SYS->sysGroupAddUser(".$this->Group->getID().",$user_id,1)</h3>";
							if (!$SYS->sysGroupAddUser($this->Group->getID(),$user_id,0)) {
								$this->setError($SYS->getErrorMessage());
								db_rollback();
								return false;
							}
						} else {
//echo "<h3>Role::setUser SYS->sysGroupRemoveUser(".$this->Group->getID().",$user_id,1)</h3>";
							if (!$SYS->sysGroupRemoveUser($this->Group->getID(),$user_id,1)) {
								$this->setError($SYS->getErrorMessage());
								db_rollback();
								return false;
							}
						}

					} elseif ($usection_name == 'docman') {
						$update_usergroup=true;
					} elseif ($usection_name == 'forumadmin') {
						$update_usergroup=true;
					} elseif ($usection_name == 'trackeradmin') {
						$update_usergroup=true;
					} elseif ($usection_name == 'projectadmin') {
						$update_usergroup=true;
					} elseif ($usection_name == 'pmadmin') {
						$update_usergroup=true;
					}
	//			}
			}
		}
	//	if ($update_usergroup) {
			$res = db_query_params ('UPDATE user_group
                               SET admin_flags=$1,
   				   forum_flags=$2,
   				   project_flags=$3,
   				   doc_flags=$4,
   				   cvs_flags=$5,
   				   release_flags=$6,
   				   artifact_flags=$7,
   				   role_id=$8
                               WHERE user_id=$9 AND group_id=$10',
   						array ($this->getVal('projectadmin',0),
						       $this->getVal('forumadmin',0),
						       $this->getVal('pmadmin',0),
						       $this->getVal('docman',0),
						       $this->getVal('scm',0),
						       $this->getVal('frs',0),
						       $this->getVal('trackeradmin',0),
						       $this->getID(),
						       $user_id,
						       $this->Group->getID()));
			if (!$res) {
				$this->setError('::update::usergroup::'.db_error());
				db_rollback();
				return false;
			}

	//	}

		$hook_params = array ();
		$hook_params['role'] =& $this;
		$hook_params['role_id'] = $this->getID();
		$hook_params['user_id'] = $user_id;
		plugin_hook ("role_setuser", $hook_params);


		db_commit();
		return true;

	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
