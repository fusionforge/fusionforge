<?php
/**
 * FusionForge role-based access control
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

require "PFO-RBAC.interface.php" ;

define ('USE_PFO_RBAC', false) ;

// Code shared between classes

abstract class BaseRole extends Error {
	public function getUsers() {
		throw new Exception ("Not implemented") ;
	}
	public function hasUser($user) {
		throw new Exception ("Not implemented") ;
	}
        function hasGlobalPermission($section, $action = NULL) {
		return $this->hasPermission ($section, -1, $action) ;
	}
	public function getSettings() {
		throw new Exception ("Not implemented") ;
	}
	public function setSettings($data) {
		throw new Exception ("Not implemented") ;
	}
	public function getLinkedProjects () {
		throw new Exception ("Not implemented") ;
	}
	public function linkProject ($project) {
		throw new Exception ("Not implemented") ;
	}
	public function unlinkProject ($project) {
		throw new Exception ("Not implemented") ;
	}
	public function normalizeData () {
		throw new Exception ("Not implemented") ;
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
		} else {
			// Load pre-PFO RBAC settings...
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

			// ...and map section names and values to the new values

			if ($this->data_array['group_id'] == forge_get_config ('stats_group')) {
				$this->perms_array['forge_stats'][-1] = 1 ;
			}

			$this->perms_array=array();
			$tohandle = array () ;
			$gid = $this->data_array['group_id'] ;
			foreach ($this->setting_array as $oldsection => $t) {
				switch ($oldsection) {
				case 'projectadmin':
					$tohandle[] = array ('project_admin', $gid) ;
					if ($this->data_array['group_id'] == 1 && $t[0] == 'A') {
						$tohandle[] = array ('forge_admin', -1) ;
					}
					if ($this->data_array['group_id'] == forge_get_config ('news_group') && $t[0] == 'A') {
						$tohandle[] = array ('forge_admin', -1) ;
					}
					if ($this->data_array['group_id'] == forge_get_config ('stats_group') && $t[0] == 'A') {
						$tohandle[] = array ('forge_stats', -1) ;
					}
					break ;
				case 'trackeradmin':
					$tohandle[] = array ('tracker_admin', $gid) ;
					break ;
				case 'pmadmin':
					$tohandle[] = array ('pm_admin', $gid) ;
					break ;
				case 'forumadmin':
					$tohandle[] = array ('forum_admin', $gid) ;
					break ;

				case 'newtracker':
					$tohandle[] = array ('new_tracker', $gid) ;
					break ;
				case 'newpm':
					$tohandle[] = array ('new_pm', $gid) ;
					break ;
				case 'newforum':
					$tohandle[] = array ('new_forum', $gid) ;
					break ;
					
				default:
					foreach ($t as $oldreference => $oldvalue) {
						$tohandle[] = array ($oldsection, $oldreference) ;
						break ;
					}
				}
			}

			foreach ($tohandle as $t) {
				$nsec = $t[0] ;
				$nref = $t[1] ;

				$res = db_query_params ('SELECT pfo_rbac_permissions_from_old($1,$2,$3)',
							array ($role_id, $nsec, $nref)) ;
				if ($res) {
					$arr =& db_fetch_array($res) ;
					$this->perms_array[$nsec][$nref] = $arr[0] ;
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
		
		case 'forge_stats':
			switch ($action) {
			case 'read':
				$min = 1 ;
				break ;
			case 'admin':
				$min = 2 ;
				break ;
			}
			if (($value >= $min)
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
			case 'read_public':
				$min = 1 ;
				break ;
			case 'read_private':
				$min = 2 ;
				break ;
			case 'write':
				$min = 3 ;
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
			case 'unmoderated_post':
				$min = 3 ;
				break ;
			case 'moderate':
				$min = 4 ;
				break ;
			}
			if (($value >= $min)
			    || $this->hasPermission ('forum_admin', $reference)) {
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
}

// Actual classes

abstract class RoleExplicit extends BaseRole implements PFO_RoleExplicit {
	public function addUsers($users) {
		throw new Exception ("Not implemented") ;
	}
	public function removeUsers($users) {
		throw new Exception ("Not implemented") ;
	}
	public function getUsers() {
		throw new Exception ("Not implemented") ;
	}
}

class RoleAnonymous extends BaseRole implements PFO_RoleAnonymous {
	// This role is implemented as a singleton
	private static $_instance ;
	public static function getInstance() {
		$c = __CLASS__ ;
		if (!isset(self::$_instance)) {
			self::$_instance = new $c ;
		}
		return self::$_instance ;
	}

	public function getID () {
		return -PFO_ROLE_ANONYMOUS ;
	}
	public function isPublic () {
		return true ;
	}
	public function setPublic ($flag) {
		throw new Exception ("Can't setPublic() on RoleAnonymous") ;
	}
	public function getHomeProject () {
		return NULL ;
	}
	public function getName () {
		return _('Anonymous/not logged in') ;
	}
	public function setName ($name) {
		throw new Exception ("Can't setName() on RoleAnonymous") ;
	}
}

class RoleLoggedIn extends BaseRole implements PFO_RoleLoggedIn {
	// This role is implemented as a singleton
	private static $_instance ;
	public static function getInstance() {
		$c = __CLASS__ ;
		if (!isset(self::$_instance)) {
			self::$_instance = new $c ;
		}
		return self::$_instance ;
	}

	public function getID () {
		return -PFO_ROLE_LOGGEDIN ;
	}
	public function isPublic () {
		return true ;
	}
	public function setPublic ($flag) {
		throw new Exception ("Can't setPublic() on RoleLoggedIn") ;
	}
	public function getHomeProject () {
		return NULL ;
	}
	public function getName () {
		return _('Any user logged in') ;
	}
	public function setName ($name) {
		throw new Exception ("Can't setName() on RoleLoggedIn") ;
	}
}

abstract class RoleUnion extends BaseRole implements PFO_RoleUnion {
	public function addRole ($role) {
		throw new Exception ("Not implemented") ;
	}
	public function removeRole ($role) {
		throw new Exception ("Not implemented") ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
