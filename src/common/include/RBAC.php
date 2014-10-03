<?php
/**
 * FusionForge role-based access control
 *
 * Copyright 2004, GForge, LLC
 * Copyright 2009-2010, Roland Mas
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * Copyright 2013, French Ministry of National Education
 * Copyright 2014, Inria (Sylvain Beucler)
 * http://fusionforge.org
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

require 'PFO-RBAC.interface.php';

// Code shared between classes

/**
 * TODO: RBAC::BaseRole Enter description here ...
 *
 */
abstract class BaseRole extends Error {
	/**
	 * TODO: Enter description here ...
	 * @var unknown_type
	 */
	var $role_values;
	/**
	 * TODO: Enter description here ...
	 * @var unknown_type
	 */
	var $defaults;
	/**
	 * TODO: Enter description here ...
	 * @var unknown_type
	 */
	var $global_settings;

	// var $perms_array;
	// var $setting_array;

	public function BaseRole() {
		// TODO: document these tables
		// $gfcommon.'include/rbac_texts.php' may provide some hints...
		$this->role_values = array(
			'forge_admin' => array(0, 1),
			'approve_projects' => array(0, 1),
			'approve_news' => array(0, 1),
			'forge_stats' => array(0, 1, 2),

			'project_read' => array(0, 1),
			'project_admin' => array(0, 1),

			'tracker_admin' => array(0, 1),
			'pm_admin' => array(0, 1),
			'forum_admin' => array(0, 1),

			'tracker' => array(0, 1, 9, 11, 13, 15, 25, 27, 29, 31),
			'pm' => array(0, 1, 3, 5, 7),
			'forum' => array(0, 1, 2, 3, 4),

			'new_tracker' => array(0, 1, 9, 11, 13, 15, 25, 27, 29, 31),
			'new_pm' => array(0, 1, 3, 5, 7),
			'new_forum' => array(0, 1, 2, 3, 4),

			'scm' => array (0, 1, 2),
			'docman' => array (0, 1, 2, 3, 4),
			'frs' => array (0, 1, 2, 3),

			);

		// Global permissions
		$this->global_settings = array(
			'forge_admin', // “God mode”: all actions allowed
			'approve_projects', // Ability to approve pending projects
			'approve_news', // Ability to approve news bits to the forge front page
			'forge_stats'
			);

		// TODO: document these	(Project-related permissions ?)
		$this->defaults = array(
			'Admin' => array(            'project_admin'=> 1,
						     'project_read' => 1,
						     'frs' => 2,
						     'scm' => 2,
						     'docman' => 3,
						     'forum_admin' => 1,
						     'new_forum' => 3,
						     'tracker_admin' => 1,
						     'new_tracker' => 15,
						     'pm_admin' => 1,
						     'new_pm' => 7,
				),
			'Senior Developer' => array( 'project_read' => 1,
						     'frs' => 2,
						     'scm' => 2,
						     'docman' => 3,
						     'forum_admin' => 1,
						     'new_forum' => 3,
						     'tracker_admin' => 1,
						     'new_tracker' => 15,
						     'pm_admin' => 1,
						     'new_pm' => 7,
				),
			'Junior Developer' => array( 'project_read' => 1,
						     'frs' => 2,
						     'scm' => 2,
						     'docman' => 2,
						     'new_forum' => 3,
						     'new_tracker' => 11,
						     'new_pm' => 3,
				),
			'Doc Writer' => array(       'project_read' => 1,
						     'frs' => 2,
						     'docman' => 4,
						     'new_forum' => 3,
						     'new_tracker' => 9,
						     'new_pm' => 1,
				),
			'Support Tech' => array(     'project_read' => 1,
						     'frs' => 2,
						     'docman' => 1,
						     'new_forum' => 3,
						     'tracker_admin' => 1,
						     'new_tracker' => 11,
						     'pm_admin' => 1,
						     'new_pm' => 7,
				),
			);
	}

	public function getUsers() {
		return array () ;
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
	public function delete () {
		throw new Exception ("Not implemented") ;
	}

	/**
	 * getLinkedProjects - List of projects referencing that role
	 *
	 * Includes the home project (for roles that have one)
	 *
	 * @return	array	Array of Group objects
	 */
	public function getLinkedProjects() {
		$ids = array();

		$hp = $this->getHomeProject();
		if ($hp != NULL) {
			$ids[] = $hp->getID();
		}

		$res = db_query_params('SELECT group_id FROM role_project_refs WHERE role_id=$1',
					array($this->getID()));
		if ($res) {
			while ($arr = db_fetch_array ($res)) {
				$ids[] = $arr['group_id'];
			}
		}

		return group_get_objects(array_unique($ids));
	}

	function linkProject ($project) { // From the PFO spec
		global $SYS;
		$hp = $this->getHomeProject();
		if ($hp != NULL && $hp->getID() == $project->getID()) {
			$this->setError(_("Cannot link to home project"));
			return false;
		}

		$res = db_query_params('SELECT group_id FROM role_project_refs WHERE role_id=$1 AND group_id=$2',
				       array($this->getID(),
					     $project->getID()));

		if (db_numrows($res)) {
			return true ;
		}
		$res = db_query_params('INSERT INTO role_project_refs (role_id, group_id) VALUES ($1, $2)',
				       array($this->getID(),
					     $project->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('linkProject('.$project->getID().') '.db_error());
			return false;
		}

		$this->normalizeData();

		foreach ($this->getUsers() as $u) {
			if (!$SYS->sysCheckCreateUser($u->getID())) {
				$this->setError($SYS->getErrorMessage());
				return false;
			}
		}

		return true ;
	}

	function unlinkProject($project) { // From the PFO spec
		global $SYS;
		$hp = $this->getHomeProject();
		if ($hp != NULL && $hp->getID() == $project->getID()) {
			$this->setError (_("Cannot unlink from home project"));
			return false;
		}

		$res = db_query_params('DELETE FROM role_project_refs WHERE role_id=$1 AND group_id=$2',
				       array($this->getID(),
					     $project->getID()));
		if (!$res) {
			$this->setError('unlinkProject('.$project->getID().') '.db_error());
			return false;
		}

		$this->removeObsoleteSettings ();

		foreach ($this->getUsers() as $u) {
			if (!$SYS->sysCheckCreateUser($u->getID())) {
				$this->setError($SYS->getErrorMessage());
				return false;
			}
		}
		
		$hook_params = array();
		$hook_params['role'] =& $this;
		$hook_params['project'] =& $project;
		plugin_hook ("role_unlink_project", $hook_params);

		return true ;
	}

	/**
	 * fetchData - May need to refresh database fields.
	 *
	 * If an update occurred and you need to access the updated info.
	 *
	 * @param	int	$role_id
	 * @return	bool	success
	 */
	function fetchData($role_id) {
		unset($this->data_array);
		unset($this->setting_array);
		unset($this->perms_array);

		$res = db_query_params('SELECT * FROM pfo_role WHERE role_id=$1',
				       array ($role_id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('BaseRole::fetchData()::'.db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		if ($this->data_array['is_public'] == 't') {
			$this->data_array['is_public'] = true;
		} else {
			$this->data_array['is_public'] = false;
		}
		$res = db_query_params('SELECT section_name, ref_id, perm_val FROM pfo_role_setting WHERE role_id=$1',
				       array($role_id));
		if (!$res) {
			$this->setError('BaseRole::fetchData()::'.db_error());
			return false;
		}
		// TODO: document perms_array
		$this->perms_array=array();
		while ($arr = db_fetch_array($res)) {
			$this->perms_array[$arr['section_name']][$arr['ref_id']] = intval($arr['perm_val']);
		}

		return true;
	}

	function setSetting ($section, $reference, $value) {
		$cur = $this->getSettingRaw($section, $reference);
		if (($value == $cur) && ($cur != NULL)) {
			return;
		}

		$role_id = $this->getID () ;

		db_query_params ('DELETE FROM pfo_role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
					array ($role_id,
					       $section,
					       $reference)) ;

		db_query_params ('INSERT INTO pfo_role_setting (role_id, section_name, ref_id, perm_val) VALUES ($1, $2, $3, $4)',
						array ($role_id,
						       $section,
						       $reference,
						       $value)) ;
		$this->perms_array[$section][$reference] = $value;
	}

	function getSettingsForProject ($project) {
		$result = array();
		$group_id = $project->getID();

		$sections = array ('project_read', 'project_admin', 'frs', 'scm', 'docman', 'tracker_admin', 'new_tracker') ;
		foreach ($sections as $section) {
			$result[$section][$group_id] = $this->getVal ($section, $group_id) ;
		}

		if ($project->usesTracker()) {
			$atf = new ArtifactTypeFactory ($project) ;
			if (!$atf->isError()) {
				$tids = $atf->getAllArtifactTypeIds () ;
				foreach ($tids as $tid) {
					$result['tracker'][$tid] = $this->getVal ('tracker', $tid) ;
				}
			}
			array_push ($sections,'tracker');
		}

		/*XXX merge from Branch_5_1: maybe this also only if usesForum? */
		$sections_forum = array('forum_admin', 'new_forum');
		foreach ($sections_forum as $section_forum) {
			$result[$section_forum][$group_id] = $this->getVal ($section_forum, $group_id) ;
		}
		$sections = array_merge($sections, $sections_forum);

		if ($project->usesForum()) {
			$ff = new ForumFactory ($project) ;
			if (!$ff->isError()) {
				$fids = $ff->getAllForumIdsWithNews () ;
				foreach ($fids as $fid) {
					$result['forum'][$fid] = $this->getVal ('forum', $fid) ;
				}
			}
			array_push ($sections,'forum');
		}

		/*XXX see above, maybe only if usesPM? */
		$sections_pm = array('pm_admin', 'new_pm');
		foreach ($sections_pm as $section_pm) {
			$result[$section_pm][$group_id] = $this->getVal ($section_pm, $group_id) ;
		}
		$sections = array_merge($sections, $sections_pm);

		if ($project->usesPM()) {
			$pgf = new ProjectGroupFactory ($project) ;
			if (!$pgf->isError()) {
				$pgids = $pgf->getAllProjectGroupIds () ;
				foreach ($pgids as $pgid) {
					$result['pm'][$pgid] = $this->getVal ('pm', $pgid) ;
				}
			}
			array_push ($sections,'pm') ;
		}

		// Add settings not yet listed so far (probably plugins)
		// Currently handled:
		// - global settings (ignored here)
		// - project-wide settings (core and plugins)
		// - settings for multiple-instance tools coming from the core (trackers/pm/forums)
		// TODO:
		// - settings for multiple-instance tools from plugins
		foreach (array_keys ($this->perms_array) as $section) {
			if (!in_array ($section, $sections)) {
				if (!in_array ($section, $this->global_settings)) {
					$result[$section][$group_id] = $this->getVal ($section, $group_id) ;
				}
			}
		}

		return $result ;
	}

	/**
	 * getGlobalSettings - get the permissions for global settings
	 *
	 * The following sections are global : forge_admin, forge_stats, approve_projects, approve_news
	 *
	 * @return	array	array of permission for global settings
	 */
	function getGlobalSettings () {
		$result = array();

		$sections = array ('forge_admin', 'forge_stats', 'approve_projects', 'approve_news') ;
		foreach ($sections as $section) {
			$result[$section][-1] = $this->getVal($section, -1) ;
		}
		// Add settings not yet listed so far (probably plugins)
		foreach (array_keys ($this->perms_array) as $section) {
			if (!in_array ($section, $sections)) {
				if (in_array ($section, $this->global_settings)) {
					$result[$section][-1] = $this->getVal ($section, -1) ;
				}
			}
		}

		return $result ;
	}

	/**
	 * getSetting - TODO: Enter description here ...
	 *
	 * @param	string	$section
	 * @param	unknown_type	$reference
	 * @return number|boolean
	 */
	function getSetting($section, $reference) {
		$value = $this->getSettingRaw($section, $reference);
		if ($value == NULL) {
			$value = 0;
		}

		$min = PHP_INT_MAX ;
		$mask = 0 ;

		switch ($section) {
		case 'forge_admin':
			return $value ;
			break ;

		case 'forge_read':
		case 'approve_projects':
		case 'approve_news':
			if ($this->hasGlobalPermission('forge_admin')) {
				return 1 ;
			}
			return $value ;
			break ;

		case 'forge_stats':
			if ($this->hasGlobalPermission('forge_admin')) {
				return 2 ;
			}
			return $value ;
			break ;

		case 'project_admin':
			if ($this->hasGlobalPermission('forge_admin')) {
				return 1 ;
			}
			return $value ;
			break ;

		case 'project_read':
			if ($this->hasPermission('project_admin', $reference)) {
				return 1;
			}
			return $value;
			break;
		case 'tracker_admin':
		case 'pm_admin':
		case 'forum_admin':
			if ($this->hasPermission('project_admin', $reference)) {
				return 1 ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;

		case 'scm':
			if ($this->hasPermission('project_admin', $reference)) {
				return 2 ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;

		case 'docman':
			if ($this->hasPermission('project_admin', $reference)) {
				return 4 ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;

		case 'frs':
			if ($this->hasPermission('project_admin', $reference)) {
				return 3 ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;

		case 'forum':
			if ($this->hasPermission('forum_admin', forum_get_groupid($reference))) {
				return 4 ;
			} elseif (!$this->hasPermission('project_read', forum_get_groupid($reference))) {
				return 0;
			}
			return $value ;
			break ;
		case 'new_forum':
			if ($this->hasPermission('forum_admin', $reference)) {
				return 4 ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;

		case 'tracker':
			if ($this->hasPermission('tracker_admin', artifacttype_get_groupid($reference))) {
				return 5 | $value ;
			} elseif (!$this->hasPermission('project_read', artifacttype_get_groupid($reference))) {
				return 0;
			}
			return $value ;
			break ;
		case 'new_tracker':
			if ($this->hasPermission('tracker_admin', $reference)) {
				return 5 | $value ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;

		case 'pm':
			if ($this->hasPermission('pm_admin', projectgroup_get_groupid($reference))) {
				return 5 | $value ;
			} elseif (!$this->hasPermission('project_read', projectgroup_get_groupid($reference))) {
				return 0;
			}
			return $value ;
			break ;
		case 'new_pm':
			if ($this->hasPermission('pm_admin', $reference)) {
				return 5 | $value ;
			} elseif (!$this->hasPermission('project_read', $reference)) {
				return 0;
			}
			return $value ;
			break ;
		default:
			$hook_params = array ();
			$hook_params['role'] = $this ;
			$hook_params['section'] = $section ;
			$hook_params['reference'] = $reference ;
			$hook_params['value'] = $value ;
			$hook_params['result'] = NULL ;
			plugin_hook_by_reference ("role_get_setting", $hook_params);
			return $hook_params['result'] ;
			break ;
		}
	}

	function getSettingRaw($section, $reference) {
		if (isset ($this->perms_array[$section][$reference])) {
			return $this->perms_array[$section][$reference] ;
		}
	}

	/**
	 * getVal - get a value out of the array of settings for this role.
	 *
	 * @param	string	$section	The name of the role.
	 * @param	integer	$ref_id		The ref_id (ex: group_artifact_id, group_forum_id) for this item.
	 * @return integer	The value of this item.
	 */
	function getVal($section, $ref_id) {
		if (!$ref_id) {
			$ref_id=0;
		}
		return $this->getSetting($section, $ref_id) ;
	}

	/**
	 * &getRoleVals - get all the values and language text strings for this section.
	 *
	 * @param	string	$section
	 * @return	array	Assoc array of values for this section.
	 */
	function &getRoleVals($section) {
		global $role_vals, $rbac_permission_names;
		setup_rbac_strings();

		//
		//	Optimization - save array so it is only built once per page view
		//
		if (!isset($role_vals[$section])) {

			for ($i=0; $i<count($this->role_values[$section]); $i++) {
				//
				//	Build an associative array of these key values + localized description
				//
				$role_vals[$section][$this->role_values[$section][$i]] =
				    util_ifsetor($rbac_permission_names["$section".$this->role_values[$section][$i]],
				    _('UNKNOWN (internal error, report bug to FusionForge)'));
			}
		}
		return $role_vals[$section];
	}

        function hasPermission($section, $reference, $action = NULL) {
		$result = false ;

		$value = $this->getSetting ($section, $reference) ;
		$min = PHP_INT_MAX ;
		$mask = 0 ;

		switch ($section) {
		case 'forge_admin':
		case 'forge_read':
		case 'approve_projects':
		case 'approve_news':
		case 'project_admin':
		case 'project_read':
		case 'tracker_admin':
		case 'pm_admin':
		case 'forum_admin':
			return ($value >= 1) ;
			break ;

		case 'forge_stats':
			switch ($action) {
			case 'read':
				return ($value >= 1) ;
				break ;
			case 'admin':
				return ($value >= 2) ;
				break ;
			}
			break ;

		case 'scm':
			switch ($action) {
			case 'read':
				return ($value >= 1) ;
				break ;
			case 'write':
				return ($value >= 2) ;
				break ;
			}
			break ;

		case 'docman':
			switch ($action) {
			case 'read':
				return ($value >= 1) ;
				break ;
			case 'submit':
				return ($value >= 2) ;
				break ;
			case 'approve':
				return ($value >= 3) ;
				break ;
			case 'admin':
				return ($value >= 4) ;
				break ;
			}
			break ;

		case 'frs':
			switch ($action) {
			case 'read_public':
				return ($value >= 1) ;
				break ;
			case 'read_private':
				return ($value >= 2) ;
				break ;
			case 'write':
				return ($value >= 3) ;
				break ;
			}
			break ;

		case 'forum':
		case 'new_forum':
			switch ($action) {
			case 'read':
				return ($value >= 1) ;
				break ;
			case 'post':
				return ($value >= 2) ;
				break ;
			case 'unmoderated_post':
				return ($value >= 3) ;
				break ;
			case 'moderate':
				return ($value >= 4) ;
				break ;
			}
			break ;

		case 'tracker':
		case 'new_tracker':
			switch ($action) {
			case 'read':
				return (($value & 1) != 0) ;
				break ;
			case 'tech':
				return (($value & 2) != 0) ;
				break ;
			case 'manager':
				return (($value & 4) != 0) ;
				break ;
			case 'submit':
				return (($value & 8) != 0) ;
				break ;
			case 'vote':
				return (($value & 16) != 0);
				break;
			}
			break ;

		case 'pm':
		case 'new_pm':
			switch ($action) {
			case 'read':
				return (($value & 1) != 0) ;
				break ;
			case 'tech':
				return (($value & 2) != 0) ;
				break ;
			case 'manager':
				return (($value & 4) != 0) ;
				break ;
			}
			break ;
		default:
			$hook_params = array ();
			$hook_params['section'] = $section ;
			$hook_params['reference'] = $reference ;
			$hook_params['action'] = $action ;
			$hook_params['value'] = $value ;
			$hook_params['result'] = false ;
			plugin_hook_by_reference ("role_has_permission", $hook_params);
			return $hook_params['result'] ;
			break ;
		}
	}

	/**
	 * update - update a role in the database.
	 *
	 * @param	string	$role_name	The name of the role.
	 * @param	array	$data		A multi-dimensional array of data in this format: $data['section_name']['ref_id']=$val
	 * @param	boolean	$check_perms	Perform permission checking
	 * @param	boolean	$update_sys	Update system users & groups membership
	 * @return	boolean	True on success or false on failure.
	 */
	function update($role_name,$data,$check_perms=true,$update_sys=true) {
		global $SYS;
		if ($check_perms) {
			if ($this->getHomeProject() == NULL) {
				if (!forge_check_global_perm ('forge_admin')) {
					$this->setPermissionDeniedError();
					return false;
				}
			} elseif (!forge_check_perm ('project_admin', $this->getHomeProject()->getID())) {
				$this->setPermissionDeniedError();
				return false;
			}
		}

		db_begin();

		$role_id = $this->getID () ;

		if ($role_name != $this->getName()) {
			$this->setName($role_name) ;
		}

		db_prepare ('INSERT INTO pfo_role_setting (role_id, section_name, ref_id, perm_val) VALUES ($1, $2, $3, $4)',
			    'insert_into_pfo_role_setting');
		db_prepare ('DELETE FROM pfo_role_setting WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
			    'delete_from_pfo_role_setting');
		db_prepare ('UPDATE pfo_role_setting SET perm_val=$4 WHERE role_id=$1 AND section_name=$2 AND ref_id=$3',
			    'update_pfo_role_setting');

		// Don't remove unknown permissions (e.g. forums permissions while forums are currently disabled)
		//foreach ($this->perms_array as $sect => &$refs)
		//	foreach ($refs as $refid => $value)
		//		if (!isset($data[$sect][$refid]) or $data[$sect][$refid] != $value)
		//			db_execute('delete_from_pfo_role_setting', array($role_id, $sect, $refid));

		// Insert new/changed permissions
		foreach ($data as $sect => &$refs) {
			foreach ($refs as $refid => $value) {
				if (!isset($this->perms_array[$sect][$refid])) {
					// new permission
					db_execute('insert_into_pfo_role_setting',
						   array($role_id, $sect, $refid, $value));
				} else if ($this->perms_array[$sect][$refid] != $value) {
					// changed permission
					db_execute('update_pfo_role_setting',
						   array($role_id, $sect, $refid, $value));
				}
			}
		}

		db_unprepare ('insert_into_pfo_role_setting');
		db_unprepare ('delete_from_pfo_role_setting');
		db_unprepare ('update_pfo_role_setting');

		$hook_params = array ();
		$hook_params['role'] =& $this;
		$hook_params['role_id'] = $this->getID();
		$hook_params['data'] = $data;
		plugin_hook ("role_update", $hook_params);

		db_commit();
		$this->fetchData($this->getID());

		if ($update_sys) {
			foreach ($this->getUsers() as $u) {
				if (!$SYS->sysCheckCreateUser($u->getID())) {
					$this->setError($SYS->getErrorMessage());
					return false;
				}
			}
		}

		return true;
	}

	function getDisplayableName($group = NULL) {
		if ($this->getHomeProject() == NULL) {
			return sprintf (_('%s (global role)'),
					$this->getName ()) ;
		} elseif ($group == NULL
			  || $this->getHomeProject()->getID() != $group->getID()) {
			return sprintf (_('%s (in project %s)'),
					$this->getName (),
					$this->getHomeProject()->getPublicName()) ;
		} else {
			return $this->getName () ;
		}
	}

	function removeObsoleteSettings () {
		db_begin () ;

		// Remove obsolete project-wide settings
		$sections = array ('project_read', 'project_admin', 'frs', 'scm', 'docman', 'tracker_admin', 'new_tracker', 'forum_admin', 'new_forum', 'pm_admin', 'new_pm') ;
		db_query_params ('DELETE FROM pfo_role_setting where role_id=$1 AND section_name=ANY($2) and ref_id NOT IN (SELECT home_group_id FROM pfo_role WHERE role_id=$1 AND home_group_id IS NOT NULL UNION SELECT group_id from role_project_refs WHERE role_id=$1)',
				 array ($this->getID(),
					db_string_array_to_any_clause($sections))) ;

		// Remove obsolete settings for multiple-instance tools
		db_query_params ('DELETE FROM pfo_role_setting where role_id=$1 AND section_name=$2 and ref_id NOT IN (SELECT group_artifact_id FROM artifact_group_list WHERE group_id IN (SELECT home_group_id FROM pfo_role WHERE role_id=$1 AND home_group_id IS NOT NULL UNION SELECT group_id from role_project_refs WHERE role_id=$1))',
				 array ($this->getID(),
					'tracker')) ;
		db_query_params ('DELETE FROM pfo_role_setting where role_id=$1 AND section_name=$2 and ref_id NOT IN (SELECT group_project_id FROM project_group_list WHERE group_id IN (SELECT home_group_id FROM pfo_role WHERE role_id=$1 AND home_group_id IS NOT NULL UNION SELECT group_id from role_project_refs WHERE role_id=$1))',
				 array ($this->getID(),
					'pm')) ;
		db_query_params ('DELETE FROM pfo_role_setting where role_id=$1 AND section_name=$2 and ref_id NOT IN (SELECT group_forum_id FROM forum_group_list WHERE group_id IN (SELECT home_group_id FROM pfo_role WHERE role_id=$1 AND home_group_id IS NOT NULL UNION SELECT group_id from role_project_refs WHERE role_id=$1))',
				 array ($this->getID(),
					'forum')) ;

		db_commit () ;
		$this->fetchData($this->getID());
		return true ;
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
		$this->removeObsoleteSettings () ;

		$this->fetchData ($this->getID()) ;

		$projects = $this->getLinkedProjects() ;
		$new_pa = array () ;

		// Add missing settings
		// ...project-wide settings
		$arr = array ('project_read', 'project_admin', 'frs', 'scm', 'docman', 'tracker_admin', 'new_tracker', 'forum_admin', 'new_forum', 'pm_admin', 'new_pm') ;
		foreach ($projects as $p) {
			foreach ($arr as $section) {
				$this->normalizePermsForSection ($new_pa, $section, $p->getID()) ;
			}
		}
		$this->normalizePermsForSection ($new_pa, 'forge_admin', -1) ;
		$this->normalizePermsForSection ($new_pa, 'approve_projects', -1) ;
		$this->normalizePermsForSection ($new_pa, 'approve_news', -1) ;
		$this->normalizePermsForSection ($new_pa, 'forge_stats', -1) ;

		$hook_params = array ();
		$hook_params['role'] =& $this;
		$hook_params['new_pa'] =& $new_pa ;
		plugin_hook ("role_normalize", $hook_params);

		// ...tracker-related settings
		$new_pa['tracker'] = array () ;
		// Direct query to avoid querying each project - especially for global roles
		foreach ($projects as $p)
			$project_ids[] = $p->getID();
		$res = db_query_params('SELECT group_artifact_id FROM artifact_group_list JOIN groups USING (group_id) WHERE use_tracker=1 AND group_id=ANY($1)', 
				       array(db_int_array_to_any_clause($project_ids)));
		while ($row = db_fetch_array($res)) {
			$tid = $row['group_artifact_id'];
			if (array_key_exists ('tracker', $this->perms_array)
			    && array_key_exists ($tid, $this->perms_array['tracker']) ) {
				$new_pa['tracker'][$tid] = $this->perms_array['tracker'][$tid] ;
			} elseif (array_key_exists ('new_tracker', $this->perms_array)
				  && array_key_exists ($p->getID(), $this->perms_array['new_tracker']) ) {
				$new_pa['tracker'][$tid] = $new_pa['new_tracker'][$p->getID()] ;
			}
		}

		// ...forum-related settings
		$new_pa['forum'] = array () ;
		foreach ($projects as $p) {
			if (!$p->usesForum()) {
				continue;
			}
			$ff = new ForumFactory ($p) ;
			if (!$ff->isError()) {
				$fids = $ff->getAllForumIdsWithNews () ;
				foreach ($fids as $fid) {
					if (array_key_exists ('forum', $this->perms_array)
					&& array_key_exists ($fid, $this->perms_array['forum']) ) {
						$new_pa['forum'][$fid] = $this->perms_array['forum'][$fid] ;
					} elseif (array_key_exists ('new_forum', $this->perms_array)
						&& array_key_exists ($p->getID(), $this->perms_array['new_forum']) ) {
						$new_pa['forum'][$fid] = $new_pa['new_forum'][$p->getID()] ;
					}
				}
			}
		}

		// ...pm-related settings
		$new_pa['pm'] = array () ;
		foreach ($projects as $p) {
			if (!$p->usesPM()) {
				continue;
			}
			$pgf = new ProjectGroupFactory ($p) ;
			if (!$pgf->isError()) {
				$pgids = $pgf->getAllProjectGroupIds () ;
				foreach ($pgids as $gid) {
					if (array_key_exists ('pm', $this->perms_array)
					&& array_key_exists ($gid, $this->perms_array['pm']) ) {
						$new_pa['pm'][$gid] = $this->perms_array['pm'][$gid] ;
					} elseif (array_key_exists ('new_pm', $this->perms_array)
						&& array_key_exists ($p->getID(), $this->perms_array['new_pm']) ) {
						$new_pa['pm'][$gid] = $new_pa['new_pm'][$p->getID()] ;
					}
				}
			}
		}

		// Save
		$this->update ($this->getName(), $new_pa, false, false) ;
		return true;
	}
}

// Actual classes

/**
 * TODO: RBAC::RoleExplicit Enter description here ...
 *
 */
abstract class RoleExplicit extends BaseRole implements PFO_RoleExplicit {
	public function addUsers($users) {
		global $SYS;

		$ids = array () ;
		foreach ($users as $user) {
			$ids[] = $user->getID();
		}

		$already_there = array();
		$res = db_query_params('SELECT user_id FROM pfo_user_role WHERE user_id=ANY($1) AND role_id=$2',
					array(db_int_array_to_any_clause($ids), $this->getID()));
		if (!$res) {
			return false;
		}
		while ($arr = db_fetch_array($res)) {
			$already_there[] = $arr['user_id'] ;
		}

		foreach ($ids as $id) {
			if (!in_array ($id, $already_there)) {
				$res = db_query_params ('INSERT INTO pfo_user_role (user_id, role_id) VALUES ($1, $2)',
							array ($id,
							       $this->getID())) ;
				if (!$res) {
					return false;
				}
			}
		}

		foreach ($this->getLinkedProjects() as $p) {
			foreach ($ids as $uid) {
				if (!$SYS->sysGroupCheckUser($p->getID(),$uid)) {
					return false;
				}
			}
		}

		return true;
	}

	public function addUser ($user) {
		if (!$this->addUsers (array ($user))) {
			return false;
		}
		$hook_params['user'] = $user;
		$hook_params['role'] = $this;
		plugin_hook ("role_adduser", $hook_params);

		return true;
	}

	public function removeUsers($users) {
		global $SYS;

		$ids = array () ;
		foreach ($users as $user) {
			$ids[] = $user->getID() ;
		}

		$already_there = array () ;
		$res = db_query_params ('DELETE FROM pfo_user_role WHERE user_id=ANY($1) AND role_id=$2',
					array (db_int_array_to_any_clause($ids), $this->getID())) ;

		foreach ($this->getLinkedProjects() as $p) {
			foreach ($ids as $uid) {
				$SYS->sysGroupCheckUser($p->getID(),$uid) ;
			}
		}

		return true ;
	}

	public function removeUser ($user) {
		if(!$this->removeUsers (array ($user))){
			return false;
		}
		$hook_params['user'] = $user;
		$hook_params['role'] = $this;
		plugin_hook ("role_removeuser", $hook_params);

		return true;
	}

	public function getUsers() {
		$result = array () ;
		$res = db_query_params ('SELECT user_id FROM pfo_user_role WHERE role_id=$1',
					array ($this->getID())) ;
		while ($arr = db_fetch_array($res)) {
			$result[] = user_get_object ($arr['user_id']) ;
		}

		return $result ;
	}

	public function hasUser($user) {
		$res = db_query_params ('SELECT user_id FROM pfo_user_role WHERE user_id=$1 AND role_id=$2',
					array ($user->getID(), $this->getID())) ;
		if ($res && db_numrows($res)) {
			return true ;
		} else {
			return false ;
		}
	}

	function getID() {	// From the PFO spec
		return $this->data_array['role_id'];
	}

	function getName() {	// From the PFO spec
		return $this->data_array['role_name'];
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

		 /* drop vote rights from RoleAnonymous */
		 // why ?????
		foreach (array('tracker', 'new_tracker') as $x) {
			$y = array();
			foreach (self::$_instance->role_values[$x] as $z) {
				if (($z & 16) != 0) {
					continue;
				}
				$y[] = $z;
			}
			self::$_instance->role_values[$x] = $y;
		}

		$res = db_query_params ('SELECT r.role_id FROM pfo_role r, pfo_role_class c WHERE r.role_class = c.class_id AND c.class_name = $1',
					array ('PFO_RoleAnonymous')) ;
		if (!$res || !db_numrows($res)) {
			throw new Exception ("No PFO_RoleAnonymous role in the database") ;
		}
		self::$_instance->_role_id = db_result ($res, 0, 'role_id') ;

		$hook_params = array ();
		$hook_params['role'] =& self::$_instance;
		plugin_hook ("role_get", $hook_params);

		self::$_instance->fetchData (self::$_instance->_role_id) ;

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

		$hook_params = array ();
		$hook_params['role'] =& self::$_instance;
		plugin_hook ("role_get", $hook_params);

		self::$_instance->fetchData (self::$_instance->_role_id) ;

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

/**
 * TODO: Enter description here ...
 *
 */
class RoleComparator {
	var $criterion = 'composite' ;
	var $reference_project = NULL ;

	function Compare ($a, $b) {
		switch ($this->criterion) {
		case 'name':
			return strcoll ($a->getName(), $b->getName()) ;
			break ;
		case 'id':
			$aid = $a->getID() ;
			$bid = $b->getID() ;
			if ($a == $b) {
				return 0;
			}
			return ($a < $b) ? -1 : 1;
			break ;
		case 'composite':
		default:
			if ($this->reference_project == NULL) {
				return $this->CompareNoRef ($a, $b) ;
			}
			$rpid = $this->reference_project->getID () ;
			$ap = $a->getHomeProject() ;
			$bp = $b->getHomeProject() ;
			$a_is_local = ($ap != NULL && $ap->getID() == $rpid) ; // Local
			$b_is_local = ($bp != NULL && $bp->getID() == $rpid) ;

			if ($a_is_local && !$b_is_local) {
				return -1 ;
			} elseif (!$a_is_local && $b_is_local) {
				return 1 ;
			}
			return $this->CompareNoRef ($a, $b) ;
		}
	}

	/**
	 * CompareNoRef - TODO: Enter description here ...
	 * @param	Role	$a
	 * @param	Role	$b
	 * @return	number
	 */
	function CompareNoRef ($a, $b) {
		$ap = $a->getHomeProject() ;
		$bp = $b->getHomeProject() ;
		if ($ap == NULL && $bp != NULL) {
			return 1 ;
		} elseif ($ap != NULL && $bp == NULL) {
			return -1 ;
		} elseif ($ap == NULL && $bp == NULL) {
			$tmp = strcoll ($a->getName(), $b->getName()) ;
			return $tmp ;
		} else {
			$projcmp = new ProjectComparator () ;
			$projcmp->criterion = 'name' ;
			$tmp = $projcmp->Compare ($ap, $bp) ;
			if ($tmp) { /* Different projects, sort accordingly */
				return $tmp ;
			}
			return strcoll ($a->getName(), $b->getName()) ;
		}
	}
}

function sortRoleList (&$list, $relative_to = NULL, $criterion='composite') {
	$cmp = new RoleComparator () ;
	$cmp->criterion = $criterion ;
	$cmp->reference_project = $relative_to ;

	return usort ($list, array ($cmp, 'Compare')) ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
