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

class UNIX extends System {
	/**
        * Value to add to unix_uid to get unix uid
	*
	* @var  constant                $UID_ADD
	*/
	var $UID_ADD = 20000;

	/**
	*	UNIX() - CONSTRUCTOR
	*
	*/
	function UNIX() {
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
		$user = &user_get_object($user_id);
		if (!$user) {
			return false;
		} else {
			$res=db_query("UPDATE users SET
			unix_uid=user_id+".$this->UID_ADD.",
			unix_gid=user_id+".$this->UID_ADD.",
			unix_status='A'
			WHERE user_id=$user_id");
	                if (!$res) {
	                        $this->setError('ERROR - Could Not Update User UID/GID: '.db_error());
	                        return false;
			}
			return true;
		}
	}

	/**
 	* sysRemoveUser() - Remove a user
 	*
 	* @param		int		The user ID of the user to remove
 	* @returns true on success/false on failure
 	*
 	*/
	function sysRemoveUser($user_id) {
		$res=db_query("UPDATE users SET unix_status='N' WHERE user_id=$user_id");
		if (!$res) {
			$this->setError('ERROR - Could Not Update User Unix Status: '.db_error());
			return false;
		}
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
