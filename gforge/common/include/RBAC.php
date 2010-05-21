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

if (file_exists ('/tmp/fusionforge-use-pfo-rbac')) {
	define ('USE_PFO_RBAC', true) ;
} else {
	define ('USE_PFO_RBAC', false) ;
}

// Code shared between classes

abstract class BaseRole extends Error {
	var $role_values;
	var $defaults;
	var $global_settings;

	public function BaseRole() {
		if (USE_PFO_RBAC) {
			$this->role_values = array (
				'forge_admin' => array (0,1),
				'approve_projects' => array (0,1),
				'approve_news' => array (0,1),
				'forge_stats' => array (0,1,2),

				'project_read' => array (0,1),
				'project_admin' => array (0,1),

				'tracker_admin' => array (0,1),
				'pm_admin' => array (0,1),
				'forum_admin' => array (0,1),
				
				'tracker' => array (0,1,3,5,7),
				'pm' => array (0,1,3,5,7),
				'forum' => array (0,1,2,3,4),

				'new_tracker' => array (0,1,3,5,7),
				'new_pm' => array (0,1,3,5,7),
				'new_forum' => array (0,1,2,3,4),

				'scm' => array (0,1,2),
				'docman' => array (0,1,2,3,4),
				'frs' => array (0,1,2,3),

				'webcal' => array (0,1,2),
				);

			$this->global_settings = array (
				'forge_admin',
				'approve_projects',
				'approve_news',
				'forge_stats'
				);

			$this->defaults = array(
				'Admin' => array(            'project_admin'=> 1,
							     'project_read' => 1,
							     'frs' => 2,
							     'scm' => 2,
							     'docman' => 3,
							     'forum_admin' => 1,
							     'new_forum' => 3,
							     'tracker_admin' => 1,
							     'new_tracker' => 7,
							     'pm_admin' => 1,
							     'new_pm' => 7,
							     'webcal' => 2,
					),
				'Senior Developer' => array( 'project_read' => 1,
							     'frs' => 2,
							     'scm' => 2,
							     'docman' => 3,
							     'forum_admin' => 1,
							     'new_forum' => 3,
							     'tracker_admin' => 1,
							     'new_tracker' => 7,
							     'pm_admin' => 1,
							     'new_pm' => 7,
							     'webcal' => 2,
					),
				'Junior Developer' => array( 'project_read' => 1,
							     'frs' => 2,
							     'scm' => 2,
							     'docman' => 2,
							     'new_forum' => 3,
							     'new_tracker' => 3,
							     'new_pm' => 3,
							     'webcal' => 2,
					),
				'Doc Writer' => array(       'project_read' => 1,
							     'frs' => 2,
						       	     'docman' => 4,
						       	     'new_forum' => 3,
						       	     'new_tracker' => 1,
						       	     'new_pm' => 1,
						       	     'webcal' => 2,
					),
				'Support Tech' => array(     'project_read' => 1,
							     'frs' => 2,
							     'docman' => 1,
							     'new_forum' => 3,
							     'tracker_admin' => 1,
							     'new_tracker' => 3,
							     'pm_admin' => 1,
							     'new_pm' => 7,
							     'webcal' => 2,
					),
				);
		} else {
			$this->role_values = array(
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
			
			$this->defaults = array(
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

		}
	}

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
	 *   getLinkedProjects - List of projects referencing that role
	 *
	 *   Includes the home project (for roles that have one)
	 *
	 *   @return array Array of Group objects
	 */
	public function getLinkedProjects () {
		$ids = array () ;

		$hp = $this->getHomeProject () ;
		if ($hp != NULL) {
			$ids[] = $hp->getID() ;
		}

		$res = db_query_params ('SELECT group_id FROM pfo_role_project_refs WHERE role_id=$1',
					array ($this->getID())) ;
		if ($res) {
			while ($arr = db_fetch_array ($res)) {
				$ids[] = $arr['group_id'] ;
			}
		}

		return group_get_objects (array_unique ($ids)) ;
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

		if (USE_PFO_RBAC) {
			$res = db_query_params ('SELECT * FROM pfo_role WHERE role_id=$1',
						array ($role_id)) ;
			if (!$res || db_numrows($res) < 1) {
				$this->setError('BaseRole::fetchData()::'.db_error());
				return false;
			}
			$this->data_array =& db_fetch_array($res);
			
			$res = db_query_params ('SELECT section_name, ref_id, perm_val FROM pfo_role_setting WHERE role_id=$1',
						array ($role_id)) ;
			if (!$res) {
				$this->setError('BaseRole::fetchData()::'.db_error());
				return false;
			}
			$this->perms_array=array();
			while ($arr =& db_fetch_array($res)) {
				$this->perms_array[$arr['section_name']][$arr['ref_id']] = $arr['perm_val'];
			}
		} else {
			$res = db_query_params ('SELECT * FROM role WHERE role_id=$1',
						array ($role_id)) ;
			if (!$res || db_numrows($res) < 1) {
				$this->setError('BaseRole::fetchData()::'.db_error());
				return false;
			}
			$this->data_array =& db_fetch_array($res);

			// Load pre-PFO RBAC settings...
			$res = db_query_params ('SELECT * FROM role_setting WHERE role_id=$1',
						array ($role_id)) ;
			if (!$res) {
				$this->setError('BaseRole::fetchData()::'.db_error());
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

	function setSetting ($section, $reference, $value) {
		$role_id = $this->getID () ;

		$res = db_query_params ('DELETE FROM pfo_role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
					array ($role_id,
					       $section,
					       $reference)) ;

		if ($value == 0) {
			return ;
		}

		$res = db_query_params ('INSERT INTO pfo_role_setting (role_id, section_name, ref_id, perm_val) VALUES ($1, $2, $3, $4)',
						array ($role_id,
						       $section,
						       $reference,
						       $value)) ;
	}

	function getSettingsForProject ($project) {
		$result = array () ;
		$group_id = $project->getID() ;

		if (USE_PFO_RBAC) {
			$sections = array ('project_read', 'project_admin', 'frs', 'scm', 'docman', 'tracker_admin', 'new_tracker', 'forum_admin', 'new_forum', 'pm_admin', 'new_pm', 'webcal') ;
			foreach ($sections as $section) {
				$result[$section][$group_id] = $this->getVal ($section, $group_id) ;
			}
		} else {
			$sections = array ('projectadmin', 'frs', 'scm', 'docman', 'trackeradmin', 'newtracker', 'forumadmin', 'newforum', 'pmadmin', 'newpm', 'webcal') ;
			foreach ($sections as $section) {
				$result[$section][0] = $this->getVal ($section, 0) ;
			}
		}

		$atf = new ArtifactTypeFactory ($project) ;
		$trackers = $atf->getArtifactTypes () ;
		foreach ($trackers as $t) {
			$result['tracker'][$t->getID()] = $this->getVal ('tracker', $t->getID()) ;
		}

		$ff = new ForumFactory ($project) ;
		$forums = $ff->getForums () ;
		foreach ($forums as $f) {
			$result['forum'][$t->getID()] = $this->getVal ('forum', $f->getID()) ;
		}

		$pgf = new ProjectGroupFactory ($project) ;
		$pgs = $pgf->getProjectGroups () ;
		foreach ($pgs as $p) {
			$result['tracker'][$p->getID()] = $this->getVal ('tracker', $p->getID()) ;
		}

		return $result ;
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
		case 'new_forum':
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
		case 'new_tracker':
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
		case 'new_pm':
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
	private $_role_id ;
	public static function getInstance() {
		if (isset(self::$_instance)) {
			return self::$_instance ;
		}

		$c = __CLASS__ ;
		self::$_instance = new $c ;
		
		$res = db_query_params ('SELECT r.role_id FROM pfo_role r, pfo_role_class c WHERE r.role_class = c.class_id AND c.class_name = $1',
					array ('PFO_RoleAnonymous')) ;
		if (!$res || !db_numrows($res)) {
			throw new Exception ("No PFO_RoleAnonymous role in the database") ;
		}
		self::$_instance->_role_id = db_result ($res, 0, 'role_id') ;

		return self::$_instance ;
	}

	public function getID () {
		return $this->_role_id ;
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
	private $_role_id ;
	public static function getInstance() {
		if (isset(self::$_instance)) {
			return self::$_instance ;
		}

		$c = __CLASS__ ;
		self::$_instance = new $c ;
		
		$res = db_query_params ('SELECT r.role_id FROM pfo_role r, pfo_role_class c WHERE r.role_class = c.class_id AND c.class_name = $1',
					array ('PFO_RoleLoggedIn')) ;
		if (!$res || !db_numrows($res)) {
			throw new Exception ("No PFO_RoleLoggedIn role in the database") ;
		}
		self::$_instance->_role_id = db_result ($res, 0, 'role_id') ;

		return self::$_instance ;
	}

	public function getID () {
		return $this->_role_id ;
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
