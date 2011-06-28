<?php
/**
 * FusionForge permissions
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2011, Franck Villaume - Capgemini
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

require_once $gfcommon.'include/Error.class.php';

$PERMISSION_OBJ=array();

/**
 * permission_get_object() - Get permission objects
 *
 * permission_get_object is useful so you can pool Permission objects/save database queries
 * You should always use this instead of instantiating the object directly
 *
 * @param		object	The Group in question
 * @param		object	The User needing Permission
 * @return a Permission or false on failure
 *
 */
function &permission_get_object(&$_Group, &$_User = NULL) {
	//create a common set of Permission objects
	//saves a little wear on the database

	global $PERMISSION_OBJ;

	if (is_object($_Group)) {
		$group_id = $_Group->getID();
	} else {
		$group_id = 0;
	}

	if (!isset($PERMISSION_OBJ[$group_id])) {
		$PERMISSION_OBJ[$group_id]= new Permission($_Group);
	}
	return $PERMISSION_OBJ[$group_id];
}

class Permission extends Error {
	/**
	 * Associative array of data from db.
	 *
	 * @var array $data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var object $Group.
	 */
	var $Group;

	/**
	 * ID of the Group object
	 *
	 * @var int $group_id.
	 */
	var $group_id;

	/**
	 * Whether the user is an admin/super user of this project.
	 *
	 * @var bool $is_admin.
	 */
	var $is_admin=false;

	/**
	 * Whether the user is an admin/super user of the entire site.
	 *
	 * @var bool $is_site_admin.
	 */
	var $is_site_admin;

	/**
	 *	Constructor for this object.
	 *
	 *	@param	object	Group Object required.
	 *	@param	object	User Object required.
	 *
	 */
	function Permission (&$_Group) {
		if (!$_Group || !is_object($_Group)) {
			$this->setError('No Valid Group Object');
			return false;
		}
		if ($_Group->isError()) {
			$this->setError('Permission: '.$_Group->getErrorMessage());
			return false;
		}
		$this->Group =& $_Group;
		$this->group_id = $this->Group->getID() ;
	}

	/**
	 *  isSuperUser - whether the current user has site admin privilege.
	 *
	 *  @return	boolean	is_super_user.
	 */
	function isSuperUser() {
		return forge_check_global_perm ('forge_admin') ;
	}

	/**
	 *  isForumAdmin - whether the current user has form admin perms.
	 *
	 *  @return	boolean	is_forum_admin.
	 */
	function isForumAdmin() {
		return forge_check_perm ('forum_admin', $this->group_id) ;
	}

	/**
	 *  isDocEditor - whether the current user has form doc editor perms.
	 *
	 *  @return	boolean	is_doc_editor.
	 */
	function isDocEditor() {
		return forge_check_perm('docman', $this->group_id, 'approve');
	}

	/**
	 *  isDocAdmin - whether the current user has form doc admin perms.
	 *
	 *  @return	boolean	is_doc_admin.
	 */
	function isDocAdmin() {
		return forge_check_perm('docman', $this->group_id, 'admin');
	}

	/**
	 *  isReleaseTechnician - whether the current user has FRS admin perms.
	 *
	 *  @return	boolean	is_release_technician.
	 */
	function isReleaseTechnician() {
		return forge_check_perm ('frs', $this->group_id, 'write') ;
	}

	/**
	 *  isArtifactAdmin - whether the current user has artifact admin perms.
	 *
	 *  @return	boolean	is_artifact_admin.
	 */
	function isArtifactAdmin() {
		return forge_check_perm ('tracker_admin', $this->group_id) ;
	}

	/**
	 *  isPMAdmin - whether the current user has Tasks admin perms.
	 *
	 *  @return	boolean	is_projman_admin.
	 */
	function isPMAdmin() {
		return forge_check_perm ('pm_admin', $this->group_id) ;
	}

	/**
	 *  isAdmin - User is an admin of the project or admin of the entire site.
	 *
	 *  @return	boolean	is_admin.
	 */
	function isAdmin() {
		return forge_check_perm ('project_admin', $this->group_id) ;
	}

	/**
	 *	isCVSReader - checks the cvs_flags field in user_group table.
	 *
	 *	@return	boolean	cvs_flags
	 */
	function isCVSReader() {
		return forge_check_perm ('scm', $this->group_id, 'read') ;
	}

	/**
	 *      isCVSWriter - checks if the user has CVS write access.
	 *
	 *      @return boolean cvs_flags
	 */
	function isCVSWriter() {
		return forge_check_perm ('scm', $this->group_id, 'write') ;
	}

	/**
	 *  isMember - Simple test to see if the current user is a member of this project.
	 *
	 *  @return	boolean	is_member.
	 */
	function isMember() {
		if ($this->isAdmin()) {
			//admins are tested first so that super-users can return true
			//and admins of a project should always have full privileges
			//on their project
			return true;
		} else {
			$engine = RBACEngine::getInstance() ;

			$roles = $engine->getAvailableRoles () ;
			foreach ($roles as $role) {
				$hp = $role->getHomeProject () ;
				if ($hp != NULL
				    && $hp->getID() == $this->group_id) {
					return true ;
				}
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
