<?php
/**
 * FusionForge system users integration
 *
 * Copyright 2004, Christian Bayle
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

class System extends Error {
	/**
	 * System()
	 *
	 */
	function System() {
		$this->Error();
		return true;
	}

	/**
	 * sysUseUnixName() - Check if user/group used the unix_name
	 *
	 * @param  string $unix_name The unix_name to check
	 * @return bool true if used/false is free
	 *
	 */
	function sysUseUnixName($unix_name) {
		return true;
	}

	/*
 	* User management functions
 	*/

	/**
	 * sysCheckUser() - Check for the existence of a user
	 *
	 * @param  int $user_id The user ID of the user to check
	 * @return bool true on success/false on error
	 *
	 */
	function sysCheckUser($user_id) {
		$user = user_get_object($user_id);
		if (!$user) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * sysCreateUser() - Create a user
	 *
	 * @param  int $user_id The user ID of the user to create
	 * @return bool The return status
	 *
	 */
	function sysCreateUser($user_id) {
		$user = user_get_object($user_id);
		if (!$user) {
			return false;
		} else {
			$systasksq = new SysTasksQ();
			$systasksq->add(SYSTASK_CORE, 'HOMEDIR', null, $user_id);
			return true;
		}
	}

	/**
	 * sysCheckCreateUser() - Check that a user has been created
	 *
	 * @param  int $user_id The ID of the user to check
	 * @return bool true on success/false on error
	 *
	 */
	function sysCheckCreateUser($user_id) {
		return $this->sysCreateUser($user_id);
	}

	/**
	 * sysCheckCreateGroup() - Check that a group has been created
	 *
	 * @param  int $user_id The ID of the user to check
	 * @return bool true on success/false on error
	 *
	 */
	function sysCheckCreateGroup($user_id) {
		return $this->sysCreateGroup($user_id);
	}

	/**
	 * sysRemoveUser() - Remove a user
	 *
	 * @param  int $user_id The user ID of the user to remove
	 * @return bool true on success/false on failure
	 *
	 */
	function sysRemoveUser($user_id) {
		return true;
	}

	/**
	 * sysUserSetAttribute() - Set an attribute for a user
	 *
	 * @param  int		$user_id	The user ID
	 * @param  string	$attr		The attribute to set
	 * @param  string	$value		The new value of the attribute
	 * @return bool true on success/false on error
	 *
	 */
	function sysUserSetAttribute($user_id, $attr, $value) {
		return true;
	}

	/*
 	* Group management functions
 	*/

	/**
	 * sysCheckGroup() - Check for the existence of a group
	 *
	 * @param  int		$group_id	The ID of the group to check
	 * @return bool		True on success/false on error
	 *
	 */
	function sysCheckGroup($group_id) {
		return true;
	}

	/**
	 * sysCreateGroup() - Create a group
	 *
	 * @param	int		$group_id	The ID of the group to create
	 * @return	bool	true on success/false on error
	 *
	 */
	function sysCreateGroup($group_id) {
		return true;
	}

	/**
	 * sysRemoveGroup() - Remove a group
	 *
	 * @param	int		$group_id	The ID of the group to remove
	 * @return	bool	true on success/false on error
	 *
	 */
	function sysRemoveGroup($group_id) {
		return true;
	}

	/**
	 * sysGroupCheckUser() - Sync forge permissions with system permissions for that user/group
	 *
	 * @param	int		$group_id	The ID of the group to which the user will be added
	 * @param	int		$user_id	The ID of the user to add
	 * @return	bool	true on success/false on error
	 *
	 */
	function sysGroupCheckUser($group_id, $user_id) {
		return true;
	}

	/**
	 * sysGroupAddUser() - Add a user to a group
	 *
	 * @param	int		$group_id	The ID of the group to which the user will be added
	 * @param	int		$user_id	The ID of the user to add
	 * @param	bool	$cvs_only	Only add this user to CVS
	 * @return	bool	true on success/false on error
	 *
	 */
	function sysGroupAddUser($group_id, $user_id, $cvs_only = false) {
		return true;
	}

	/**
	 * sysGroupRemoveUser() - Remove a user from a group
	 *
	 * @param	int		$group_id	The ID of the group from which to remove the user
	 * @param	int		$user_id	The ID of the user to remove
	 * @param	bool	$cvs_only	Only remove user from CVS group
	 * @return	bool	true on success/false on error
	 *
	 */
	function sysGroupRemoveUser($group_id, $user_id, $cvs_only = false) {
		return true;
	}

	/**
	 * sysGroupUpdateUser() - Remove a user from a group
	 *
	 * @param	int		$group_id	The ID of the group from which to remove the user
	 * @param	int		$user_id	The ID of the user to remove
	 * @param	bool	$cvs_only	Only remove user from CVS group
	 * @return	bool	true on success/false on error
	 *
	 */
	function sysGroupUpdateUser($group_id, $user_id, $cvs_only = false) {
		$this->sysGroupRemoveUser($group_id, $user_id, $cvs_only);
		$this->sysGroupAddUser($group_id, $user_id, $cvs_only);
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
