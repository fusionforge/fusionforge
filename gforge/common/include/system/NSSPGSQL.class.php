<?php
/**
 * UNIX class
 *
 * Class to interact with the system
 *
 * @version   $Id$
 * @author Christian Bayle
 * @date 2004-02-05
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
require_once $gfcommon.'include/System.class.php';

class NSSPGSQL extends System {
	/**
        * Value to add to unix_uid to get unix uid
	*
	* @var  constant                $UID_ADD
	*/
	var $UID_ADD = 20000;

	/**
	*	NSSPGSQL() - CONSTRUCTOR
	*
	*/
	function NSSPGSQL() {
		$this->System();
		return true;
	}
	/**
 	* sysCreateUser() - Create a user
 	*
 	* @param		int	The user ID of the user to create
 	* @returns The return status
 	*
 	*/
	function sysCreateUser($user_id) {
		return true;
	}

	/**
 	* sysRemoveUser() - Remove a user
 	*
 	* @param		int		The user ID of the user to remove
 	* @returns true on success/false on failure
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
 	* @param		int		The ID of the group to check
 	* @returns true on success/false on error
 	*
 	*/
	function sysCheckGroup($group_id) {
		$group =& group_get_object($group_id);
		if (!$group){
			return false;
		}
		return true;
	}

	/**
 	* sysCreateGroup() - Create a group
 	* 
 	* @param		int		The ID of the group to create
 	* @returns true on success/false on error
 	*
 	*/
	function sysCreateGroup($group_id) {
		$group = &group_get_object($group_id);
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

?>
