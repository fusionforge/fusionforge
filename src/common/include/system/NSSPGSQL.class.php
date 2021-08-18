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

require_once $gfcommon.'include/System.class.php';

class NSSPGSQL extends System {
	/**
	 * Value to add to unix_uid to get unix uid
	 *
	 * @var  constant	$UID_ADD
	 */
	var $UID_ADD = 20000;

	/**
 	 * sysCreateUser() - Create a user
 	 *
 	 * @param		int		$user_id	The user ID of the user to create
	 * @return		bool	The return status
 	 *
 	 */
	function sysCreateUser($user_id) {
		return true;
	}

	/**
 	 * sysRemoveUser() - Remove a user
 	 *
 	 * @param		int		$user_id	The user ID of the user to remove
	 * @return		bool	true on success/false on failure
 	 *
 	 */
	function sysRemoveUser($user_id) {
		return true;
	}

	/*
 	 * Group management functions
 	 */

	/**
 	 * sysCheckGroup() - Check for the existence of a group
 	 *
 	 * @param		int		$group_id	The ID of the group to check
	 * @return		bool	true on success/false on failure
 	 *
 	 */
	function sysCheckGroup($group_id) {
		$group = group_get_object($group_id);
		if (!$group){
			return false;
		}
		return true;
	}

	/**
 	 * sysCreateGroup() - Create a group
 	 *
 	 * @param		int		$group_id	The ID of the group to create
	 * @return		bool	true on success/false on failure
 	 *
 	 */
	function sysCreateGroup($group_id) {
		$group = group_get_object($group_id);
		if (!$group) {
			return false;
		}
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
