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

define ('USE_PFO_RBAC', false) ;

class Role extends RoleExplicit implements PFO_RoleExplicit {

	var $data_array;
	var $setting_array;
	var $perms_array ;
	var $role_vals;
	var $Group;
	var $role_values = array(
		'projectadmin' => array ('0','A'),
		'frs'	       => array ('0','1'),
		'scm'	       => array ('-1','0','1'),
		'docman'       => array ('0','1'),
		'forumadmin'   => array ('0','2'),
		'forum'	       => array ('-1','0','1','2'),
		'newforum'     => array ('-1','0','1','2'),
		'trackeradmin' => array ('0','2'),
		'tracker'      => array ('-1','0','1','2','3'),
		'newtracker'   => array ('-1','0','1','2','3'),
		'pmadmin'      => array ('0','2'),
		'pm'	       => array ('-1','0','1','2','3'),
		'newpm'	       => array ('-1','0','1','2','3'),
		'webcal'       => array ('0','1','2'));
	
	var $defaults = array(
		'Admin'		  => array( 'projectadmin'=>'A',
					    'frs'=>'1',
					    'scm'=>'1',
					    'docman'=>'1',
					    'forumadmin'=>'2',
					    'forum'=>'2',
					    'newforum'=>'2',
					    'trackeradmin'=>'2',
					    'tracker'=>'2',
					    'newtracker'=>'2',
					    'pmadmin'=>'2',
					    'pm'=>'2',
					    'newpm'=>'2',
					    'webcal'=>'1' ),
		'Senior Developer'=> array( 'projectadmin'=>'0',
					    'frs'=>'1',
					    'scm'=>'1',
					    'docman'=>'1',
					    'forumadmin'=>'2',
					    'forum'=>'2',
					    'newforum'=>'2',
					    'trackeradmin'=>'2',
					    'tracker'=>'2',
					    'newtracker'=>'2',
					    'pmadmin'=>'2',
					    'pm'=>'2',
					    'newpm'=>'2',
					    'webcal'=>'2' ),
		'Junior Developer'=> array( 'projectadmin'=>'0',
					    'frs'=>'0',
					    'scm'=>'1',
					    'docman'=>'0',
					    'forumadmin'=>'0',
					    'forum'=>'1',
					    'newforum'=>'1',
					    'trackeradmin'=>'0',
					    'tracker'=>'1',
					    'newtracker'=>'1',
					    'pmadmin'=>'0',
					    'pm'=>'1',
					    'newpm'=>'1',
					    'webcal'=>'2' ),
		'Doc Writer'	  => array( 'projectadmin'=>'0',
					    'frs'=>'0',
					    'scm'=>'0',
					    'docman'=>'1',
					    'forumadmin'=>'0',
					    'forum'=>'1',
					    'newforum'=>'1',
					    'trackeradmin'=>'0',
					    'tracker'=>'0',
					    'newtracker'=>'0',
					    'pmadmin'=>'0',
					    'pm'=>'0' ,
					    'newpm'=>'0' ,
					    'webcal'=>'2'),
		'Support Tech'	  => array( 'projectadmin'=>'0',
					    'frs'=>'0',
					    'scm'=>'0',
					    'docman'=>'1',
					    'forumadmin'=>'0',
					    'forum'=>'1',
					    'newforum'=>'1',
					    'trackeradmin'=>'0',
					    'tracker'=>'2',
					    'newtracker'=>'2',
					    'pmadmin'=>'0',
					    'pm'=>'0' ,
					    'newpm'=>'0' ,
					    'webcal'=>'2')
		);

	/**
	 *  Role($group,$id) - CONSTRUCTOR.
	 *
	 *  @param  object	 The Group object.
	 *  @param  int	 The role_id.
	 */
	function Role ($Group,$role_id=false) {
		$this->Error();
		if (!$Group || !is_object($Group) || $Group->isError()) {
			$this->setError('Role::'.$Group->getErrorMessage());
			return false;
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
	 *	getID - get the ID of this role.
	 *
	 *	@return	integer	The ID Number.
	 */
	function getID() {	// From the PFO spec
		return $this->data_array['role_id'];
	}

	/**
	 *	getName - get the name of this role.
	 *
	 *	@return	string	The name of this role.
	 */
	function getName() {	// From the PFO spec
		return $this->data_array['role_name'];
	}

	/**
	 *	setName - set the name of this role.
	 *
	 *	@param	string	The new name of this role.
	 *      @return boolean True if updated OK
	 */
	function setName ($role_name) { // From the PFO spec
		if ($this->getName() != stripslashes($role_name)) {
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
		$res = db_query_params ('UPDATE role SET is_public=$1 WHERE role_id=$1',
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

	function getLinkedProjects () { // From the PFO spec
		return $this->Group ;

		$res = db_query_params('SELECT group_id FROM role_project_refs WHERE role_id=$1',
				       array ($this->getID()));
		
		$result = array () ;
	
		while ($arr =& db_fetch_array($res)) {
			$result[] = group_get_object ($arr['group_id']) ;
		}
		return $result ;
	}

	function linkProject ($project) { // From the PFO spec
		if ($project->getID() == $this->getHomeProject()->getID()) {
			$this->setError ("Can't link to home project") ;
			return false ;
		}

		$res = db_query_params('SELECT group_id FROM role_project_refs WHERE role_id=$1 AND group_id=$2',
				       array ($this->getID(),
					      $project->getID()));

		if (db_numrows($res)) {
			return true ;
		}
		$res = db_query_params('INSERT INTO role_project_refs (role_id, group_id) VALUES ($1, $2)',
				       array ($this->getID(),
					      $project->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('linkProject('.$project->getID().') '.db_error());
			return false;
		}

		return true ;
	}

	function unlinkProject ($project) { // From the PFO spec
		if ($project->getID() == $this->getHomeProject()->getID()) {
			$this->setError ("Can't unlink from home project") ;
			return false ;
		}

		$res = db_query_params('DELETE FROM role_project_refs WHERE role_id=$1 AND group_id=$2',
				       array ($this->getID(),
					      $project->getID()));
		if (!$res) {
			$this->setError('unlinkProject('.$project->getID().') '.db_error());
			return false;
		}

		return true ;
	}

	/**
	 *	create - create a new role in the database.
	 *
	 *	@param	string	The name of the role.
	 *	@param	array	A multi-dimensional array of data in this format: $data['section_name']['ref_id']=$val
	 *	@return integer	The id on success or false on failure.
	 */
	function create($role_name,$data) {
		$perm =& $this->Group->getPermission( session_get_user() );
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
		if (!$this->fetchData($role_id)) {
			db_rollback();
			return false;
		}
		db_commit();
		return $role_id;
	}

	function createDefault($name) {
//echo '<html><body><pre>';
//echo $name;
//print_r($this->defaults);
		$arr =& $this->defaults[$name];
		$keys = array_keys($arr);
		$data = array();

//print_r($keys);
//print_r($arr);
//db_rollback();
//exit;
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
//print_r($data);
//db_rollback();
//exit;
		return $this->create($name,$data);
	}

	/**
	 *  fetchData - May need to refresh database fields.
	 *
	 *  If an update occurred and you need to access the updated info.
	 *
	 *  @return boolean success;
	 */
	function fetchData($role_id) {
		unset($this->data_array);
		unset($this->setting_array);
		unset($this->perms_array);

		$res = db_query_params ('SELECT * FROM role WHERE role_id=$1',
					array ($role_id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Role::fetchData()::'.db_error());
			return false;
		}
		$this->data_array =& db_fetch_array($res);

		$res = db_query_params ('SELECT * FROM role_setting WHERE role_id=$1',
					array ($role_id)) ;
		if (!$res) {
			$this->setError('Role::fetchData()::'.db_error());
			return false;
		}
		$this->setting_array=array();
		while ($arr =& db_fetch_array($res)) {
			$this->setting_array[$arr['section_name']][$arr['ref_id']] = $arr['value'];
		}

		if (USE_PFO_RBAC) {
			$res = db_query_params ('SELECT section, reference, value FROM role_perms WHERE role_id=$1',
						array ($role_id)) ;
			if (!$res) {
				$this->setError('Role::fetchData()::'.db_error());
				return false;
			}
			$this->perms_array=array();
			while ($arr =& db_fetch_array($res)) {
				$this->perms_array[$arr['section']][$arr['reference']] = $arr['value'];
			}
		} else { 	// Map pre-PFO RBAC section names and values to the new values
			$this->perms_array=array();
			foreach ($this->setting_array as $oldsection => $t) {
				switch ($oldsection) {
				case 'projectadmin':
					$newsection = 'project_admin' ;
					break ;
				case 'trackeradmin':
					$newsection = 'tracker_admin' ;
					break ;
				case 'pmadmin':
					$newsection = 'pm_admin' ;
					break ;
				case 'forumadmin':
					$newsection = 'forum_admin' ;
					break ;

				default:
					$newsection = $oldsection ;
				}

				foreach ($t as $oldreference => $oldvalue) {
					$newvalue = 0 ;
					$newreference = $oldreference ;
					switch ($newsection) {
					case 'project_admin':
						$newreference = $this->Group->getID() ;
						switch ($oldvalue) {
						case '0': $newvalue = 0 ; break ;
						case 'A': $newvalue = 1 ; break ;
						}
					break;
					
					case 'tracker_admin':
					case 'pm_admin':
					case 'forum_admin':
						$newreference = $this->Group->getID() ;
						switch ($oldvalue) {
						case '0': $newvalue = 0 ; break ;
						case '2': $newvalue = 1 ; break ;
						}
					break;
					
					case 'tracker':
					case 'pm':
						switch ($oldvalue) {
						case '-1': $newvalue = 0 ; break ;
						case '0': $newvalue = 1 ; break ;
						case '1': $newvalue = 3 ; break ;
						case '2': $newvalue = 7 ; break ;
						case '3': $newvalue = 5 ; break ;
						}
					break ;

					case 'docman':
						$newreference = $this->Group->getID() ;
						switch ($oldvalue) {
						case '0': $newvalue = 1 ; break ;
						case '1': $newvalue = 4 ; break ;
						}
						break ;

					case 'frs':
						$newreference = $this->Group->getID() ;
						switch ($oldvalue) {
						case '0': $newvalue = 1 ; break ;
						case '1': $newvalue = 3 ; break ;
						}
						break ;

					case 'scm':
						$newreference = $this->Group->getID() ;
						switch ($oldvalue) {
						case '-1': $newvalue = 0 ; break ;
						case '0': $newvalue = 1 ; break ;
						case '1': $newvalue = 2 ; break ;
						}
						break ;

					default:
						$newvalue = $oldvalue ;
						$newreference = $oldreference ;
					}

					$this->perms_array[$newsection][$newreference] = $newvalue ;
				}
			}
		}

		return true;
	}

        function hasPermission($section, $reference, $action = NULL) {
		$result = false ;
                if (isset ($this->perms_array[$section][$reference])) {
			$value = $this->perms_array[$section][$reference] ;
		} else {
			$value = 0 ;
		}
		$min = PHP_INT_MAX ;
		$mask = 0 ;
		
		switch ($section) {
		case 'forge_admin':
			if ($value == 1) {
				return true ;
			}
			break ;
			
		case 'forge_read':
		case 'approve_projects':
		case 'approve_news':
			if (($value == 1)
			    || $this->hasGlobalPermission('forge_admin')) {
				return true ;
			}
		break ;
		
		case 'project_admin':
			if (($value == 1)
			    || $this->hasGlobalPermission('forge_admin')) {
				return true ;
			}
			break ;
			
		case 'project_read':
		case 'tracker_admin':
		case 'pm_admin':
		case 'forum_admin':
			if (($value == 1)
			    || $this->hasPermission ('project_admin', $reference)) {
				return true ;
			}
		break ;
		
		case 'scm':
			switch ($action) {
			case 'read':
				$min = 1 ;
				break ;
			case 'write':
				$min = 2 ;
				break ;
			}
			if (($value >= $min)
			    || $this->hasPermission ('project_admin', $reference)) {
				return true ;
			}
			break ;
			
		case 'docman':
			switch ($action) {
			case 'read':
				$min = 1 ;
				break ;
			case 'submit':
				$min = 2 ;
				break ;
			case 'approve':
				$min = 3 ;
				break ;
			case 'admin':
				$min = 4 ;
				break ;
			}
			if (($value >= $min)
			    || $this->hasPermission ('project_admin', $reference)) {
				return true ;
			}
			break ;
			
		case 'frs':
			switch ($action) {
			case 'read':
				$min = 1 ;
				break ;
			case 'write':
				$min = 2 ;
				break ;
			}
			if (($value >= $min)
			    || $this->hasPermission ('project_admin', $reference)) {
				return true ;
			}
			break ;
			
		case 'forum':
			switch ($action) {
			case 'read':
				$min = 1 ;
				break ;
			case 'post':
				$min = 2 ;
				break ;
			case 'moderate':
				$min = 3 ;
				break ;
			}
			if (($value >= $min)
			    || $this->hasPermission ('project_admin', $reference)) {
				return true ;
			}
			break ;
			
		case 'tracker':
			switch ($action) {
			case 'read':
				$mask = 1 ;
				break ;
			case 'tech':
				$mask = 2 ;
				break ;
			case 'manager':
				$mask = 4 ;
				break ;
			}
			$o = artifactType_get_object ($reference) ;
			if (!$o or $o->isError()) {
				return false ;
			}

			if (($value & $mask)
			    || $this->hasPermission ('tracker_admin', $o->Group->getID())
			    || $this->hasPermission ('project_admin', $o->Group->getID())) {
				return true ;
			}
			break ;

		case 'pm':
			switch ($action) {
			case 'read':
				$mask = 1 ;
				break ;
			case 'tech':
				$mask = 2 ;
				break ;
			case 'manager':
				$mask = 4 ;
				break ;
			}
			$o = projectgroup_get_object ($reference) ;
			if (!$o or $o->isError()) {
				return false ;
			}

			if (($value & $mask)
			    || $this->hasPermission ('pm_admin', $o->Group->getID())
			    || $this->hasPermission ('project_admin', $o->Group->getID())) {
				return true ;
			}
			break ;
		}
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

	function normalizeData () { // From the PFO spec
		db_begin () ;
		$this->fetchData ($this->getID()) ;
		
		$new_sa = array () ;
		
		// Add missing settings
		// ...project-wide settings
		$arr = array ('projectadmin', 'frs', 'scm', 'docman', 'forumadmin', 'trackeradmin', 'newtracker', 'pmadmin', 'newpm', 'webcal') ;
		foreach ($arr as $section) {
			$this->normalizeDataForSection ($new_sa, $section) ;
		}

		$hook_params = array ();
		$hook_params['role'] =& $this;
		$hook_params['new_sa'] =& $new_sa ; 
		plugin_hook ("role_normalize", $hook_params);
		
		// ...tracker-related settings
		$new_sa['tracker'] = array () ;
		$res = db_query_params ('SELECT group_artifact_id FROM artifact_group_list WHERE group_id=$1',
					array ($this->Group->getID())) ;
		if (!$res) {
			$this->setError('Error: Tracker '.db_error());
			return false;
		}
		for ($j=0; $j<db_numrows($res); $j++) {
			$tid = db_result ($res,$j,'group_artifact_id') ;
			if (array_key_exists ('tracker', $this->setting_array)
			    && array_key_exists ($tid, $this->setting_array['tracker']) ) {
				$new_sa['tracker'][$tid] = $this->setting_array['tracker'][$tid] ;
			} else {
				$new_sa['tracker'][$tid] = $new_sa['newtracker'][0] ;
			}
		}
		
		// ...forum-related settings
		$new_sa['forum'] = array () ;
		$res = db_query_params ('SELECT group_forum_id FROM forum_group_list WHERE group_id=$1',
					array ($this->Group->getID())) ;
		if (!$res) {
			$this->setError('Error: Forum '.db_error());
			return false;
		}
		for ($j=0; $j<db_numrows($res); $j++) {
			$tid = db_result ($res,$j,'group_forum_id') ;
			if (array_key_exists ('forum', $this->setting_array)
			    && array_key_exists ($tid, $this->setting_array['forum']) ) {
				$new_sa['forum'][$tid] = $this->setting_array['forum'][$tid] ;
			} else {
				$new_sa['forum'][$tid] = $new_sa['newforum'][0] ;
			}
		}

		// ...subproject-related settings
		$new_sa['pm'] = array () ;
		$res = db_query_params ('SELECT group_project_id FROM project_group_list WHERE group_id=$1',
					array ($this->Group->getID())) ;
		if (!$res) {
			$this->setError('Error: Subproject '.db_error());
			return false;
		}
		for ($j=0; $j<db_numrows($res); $j++) {
			$tid = db_result ($res,$j,'group_project_id') ;
			if (array_key_exists ('pm', $this->setting_array)
			    && array_key_exists ($tid, $this->setting_array['pm']) ) {
				$new_sa['pm'][$tid] = $this->setting_array['pm'][$tid] ;
			} else {
				$new_sa['pm'][$tid] = $new_sa['newpm'][0] ;
			}
		}

		// Delete extra settings
		db_query_params ('DELETE FROM role_setting WHERE role_id=$1 AND section_name <> ALL ($2)',
				 array ($this->getID(),
					db_string_array_to_any_clause (array_keys ($this->role_values)))) ;
		db_query_params ('DELETE FROM role_setting WHERE role_id=$1 AND section_name = $2 AND ref_id <> ALL ($3)',
				 array ($this->getID(),
					'tracker',
					db_int_array_to_any_clause (array_keys ($new_sa['tracker'])))) ;
		db_query_params ('DELETE FROM role_setting WHERE role_id=$1 AND section_name = $2 AND ref_id <> ALL ($3)',
				 array ($this->getID(),
					'forum',
					db_int_array_to_any_clause (array_keys ($new_sa['forum'])))) ;
		db_query_params ('DELETE FROM role_setting WHERE role_id=$1 AND section_name = $2 AND ref_id <> ALL ($3)',
				 array ($this->getID(),
					'pm',
					db_int_array_to_any_clause (array_keys ($new_sa['pm'])))) ;
		
		db_commit () ;

		// Save
		$this->update ($this->data_array['role_name'], $new_sa) ;

		return true;
	}

	/**
	 *  &getRoleVals - get all the values and language text strings for this section.
	 *
	 *  @return array	Assoc array of values for this section.
	 */
	function &getRoleVals($section) {
		global $role_vals, $rbac_permission_names;
		setup_rbac_strings () ;

		//
		//	Optimization - save array so it is only built once per page view
		//
		if (!isset($role_vals[$section])) {

			for ($i=0; $i<count($this->role_values[$section]); $i++) {
				//
				//	Build an associative array of these key values + localized description
				//
				$role_vals[$section][$this->role_values[$section][$i]]=$rbac_permission_names["$section".$this->role_values[$section][$i]];
			}
		}
		return $role_vals[$section];
	}

	/**
	 *	getVal - get a value out of the array of settings for this role.
	 *
	 *	@param	string	The name of the role.
	 *	@param	integer	The ref_id (ex: group_artifact_id, group_forum_id) for this item.
	 *	@return integer	The value of this item.
	 */
	function getVal($section,$ref_id) {
		global $role_default_array;
		if (!$ref_id) {
			$ref_id=0;
		}
		if (array_key_exists ($section, $this->setting_array)) {
			return $this->setting_array[$section][$ref_id];
		} else {
			return 0 ;
		}
	}

	function setVal($section, $ref_id, $value) {
		$this->setting_array[$section][$ref_id] = $value;
		return $this->update( $this->getName(), $this->setting_array);
	}

	/**
	 *	delVal - delete a value out of the array of settings for this role.
	 *
	 *	@param	string	The name of the role.
	 *	@param	integer	The ref_id (ex: group_artifact_id, group_forum_id) for this item.
	 */
	function delVal($section, $ref_id) {
		unset($this->setting_array[$section][$ref_id]);

		$sql = 'DELETE FROM role_setting
				WHERE role_id=$1
				AND section_name=$2
				AND ref_id=$3';
		$res=db_query_params($sql, array($this->getID(), $section, $ref_id));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('delVal($section, $ref_id)'.db_error());
			return false;
		}
		return true;
	}

	/**
	 *	update - update a new in the database.
	 *
	 *	@param	string	The name of the role.
	 *	@param	array	A multi-dimensional array of data in this format: $data['section_name']['ref_id']=$val
	 *	@return	boolean	True on success or false on failure.
	 */
	function update($role_name,$data) {
		global $SYS;
		//
		//	Cannot update role_id=1
		//
		if ($this->getID() == 1) {
			$this->setError('Cannot Update Default Role');
			return false;
		}
		$perm =& $this->Group->getPermission( session_get_user() );
		if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		}

		db_begin();

		if (! $this->setName($role_name)) {
			db_rollback();
			return false;
		}

////$data['section_name']['ref_id']=$val
		$arr1 = array_keys($data);
		for ($i=0; $i<count($arr1); $i++) {	
		//	array_values($Report->adjust_days)
			$arr2 = array_keys($data[$arr1[$i]]);
			for ($j=0; $j<count($arr2); $j++) {
				$usection_name=$arr1[$i];
				$uref_id=$arr2[$j];
				$uvalue=$data[$usection_name][$uref_id];
				if (!$uref_id) {
					$uref_id=0;
				}
				if (!$uvalue) {
					$uvalue=0;
				}
				//
				//	See if this setting changed. If so, then update it
				//
//				if ($this->getVal($usection_name,$uref_id) != $uvalue) {
					$res = db_query_params ('UPDATE role_setting SET value=$1 WHERE role_id=$2 AND section_name=$3 AND ref_id=$4',
								array ($uvalue,
								       $this->getID(),
								       $usection_name,
								       $uref_id)) ;
					if (!$res || db_affected_rows($res) < 1) {
						$res = db_query_params ('INSERT INTO role_setting (role_id, section_name, ref_id, value) VALUES ($1, $2, $3, $4)',
									array ($this->getID(),
									       $usection_name,
									       $uref_id,
									       $uvalue)) ;
						if (!$res) {
							$this->setError('update::rolesettinginsert::'.db_error());
							db_rollback();
							return false;
						}
					}
					if ($usection_name == 'frs') {
						$update_usergroup=true;
					} elseif ($usection_name == 'scm') {
						//$update_usergroup=true;

						//iterate all users with this role
						$res = db_query_params ('SELECT user_id	FROM user_group WHERE role_id=$1',
									array ($this->getID())) ;
						for ($z=0; $z<db_numrows($res); $z++) {

							//TODO - Shell should be separate flag
							//  If user acquired admin access to CVS,
							//  one to be given normal shell on CVS machine,
							//  else - restricted.
							//
							$cvs_flags=$data['scm'][0];
							$res2 = db_query_params ('UPDATE user_group SET cvs_flags=$1 WHERE user_id=$2',
										 array ($cvs_flags,
											db_result($res,$z,'user_id')));
							if (!$res2) {
								$this->setError('update::scm::'.db_error());
								db_rollback();
								return false;
							}
							// I have doubt the following is usefull
							// This is probably buggy if used
							if ($cvs_flags>1) {
								if (!$SYS->sysUserSetAttribute(db_result($res,$z,'user_id'),"debGforgeCvsShell","/bin/bash")) {
									$this->setError($SYS->getErrorMessage());
									db_rollback();
									return false;
								}
							} else {
								if (!$SYS->sysUserSetAttribute(db_result($res,$z,'user_id'),"debGforgeCvsShell","/bin/cvssh")) {
									$this->setError($SYS->getErrorMessage());
									db_rollback();
									return false;
								}
							}

							//
							//  If user acquired at least commit access to CVS,
							//  one to be promoted to CVS group, else, demoted.
							//
							if ($uvalue>0) {
								if (!$SYS->sysGroupAddUser($this->Group->getID(),db_result($res,$z,'user_id'),1)) {
									$this->setError($SYS->getErrorMessage());
									db_rollback();
									return false;
								}
							} else {
								if (!$SYS->sysGroupRemoveUser($this->Group->getID(),db_result($res,$z,'user_id'),1)) {
									$this->setError($SYS->getErrorMessage());
									db_rollback();
									return false;
								}
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
//		if ($update_usergroup) {
			$keys = array ('forumadmin', 'pmadmin', 'trackeradmin', 'docman', 'scm', 'frs', 'projectadmin') ;
			foreach ($keys as $k) {
				if (!array_key_exists ($k, $data)) {
					$data[$k] = array(0);
				}
			}
			$res = db_query_params ('UPDATE user_group
                               SET admin_flags=$1,
   				   forum_flags=$2,
   				   project_flags=$3,
   				   doc_flags=$4,
   				   cvs_flags=$5,
   				   release_flags=$6,
   				   artifact_flags=$7
   				WHERE role_id=$8',
   						array ($data['projectadmin'][0],
						       $data['forumadmin'][0],
						       $data['pmadmin'][0],
						       $data['docman'][0],
						       $data['scm'][0],
						       $data['frs'][0],
						       $data['trackeradmin'][0],
						       $this->getID())) ;
			if (!$res) {
				$this->setError('::update::usergroup::'.db_error());
				db_rollback();
				return false;
			}

//		}

		$hook_params = array ();
		$hook_params['role'] =& $this;
		$hook_params['role_id'] = $this->getID();
		$hook_params['data'] = $data;
		plugin_hook ("role_update", $hook_params);


		db_commit();
		$this->fetchData($this->getID());
		return true;
	}

	function setUser($user_id) {
		global $SYS;
		$perm =& $this->Group->getPermission( session_get_user() );
		if (!$perm || !is_object($perm) || $perm->isError() || !$perm->isAdmin()) {
			$this->setPermissionDeniedError();
			return false;
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
