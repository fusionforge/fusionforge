<?php
/**
 * pgsql class
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
require_once('common/include/System.class.php');

class pgsql extends System {
	/*
 	* Constants
 	*/
	
	/**
 	* Value to add to unix_uid to get unix uid
 	* 
 	* @var	constant		$UID_ADD
 	*/
	var $UID_ADD = 20000;
		
	/**
 	* Value to add to group_id to get unix gid
 	*
 	* @var	constant		$GID_ADD
 	*/
	var $GID_ADD = 10000;
		
	/**
 	* Value to add to unix gid to get unix uid of anoncvs special user
 	*
 	* @var	constant		$SCM_UID_ADD
 	*/
	var $SCM_UID_ADD = 50000;

	/**
	*	pgsql() - CONSTRUCTOR
	*
	*/
	function pgsql() {
		$this->System();
		return true;
	}

	/*
 	* User management functions
 	*/

	/**
 	* sysCheckUser() - Check for the existence of a user
 	* 
 	* @param		int		The user ID of the user to check
 	* @returns true on success/false on error
 	*
 	*/
	function sysCheckUser($user_id) {
		$user =& user_get_object($user_id);
		if (!$user) {
			return false;
		}
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
			} else {
				$query="DELETE FROM nss_usergroups WHERE user_id=$user_id";
				$res1=db_query($query);
	                	if (!$res1) {
					$this->setError('ERROR - Could Not Delete Group Member(s): '.db_error());
	                        	return false;
				}
				// This is group used for user, not a real project
				$query="DELETE FROM nss_groups WHERE name IN
					(SELECT user_name FROM users WHERE user_id=$user_id)";
				$res2=db_query($query);
	                	if (!$res2) {
	                        	$this->setError('ERROR - Could Not Delete Group GID: '.db_error());
	                        	return false;
				}
				$query="INSERT INTO nss_groups
					(user_id, group_id,name, gid)
					SELECT user_id, 0, user_name, unix_gid
					FROM users WHERE user_id=$user_id"; 
				$res3=db_query($query);
	                	if (!$res3) {
	                        	$this->setError('ERROR - Could Not Update Group GID: '.db_error());
	                        	return false;
				}
				$query="INSERT INTO nss_usergroups (
					SELECT
						users.unix_uid AS uid,
						groups.group_id + ".$this->GID_ADD." AS gid,
						users.user_id AS user_id,
						groups.group_id AS group_id,
						users.user_name AS user_name,
						groups.unix_group_name AS unix_group_name
					FROM users,groups,user_group
					WHERE 
						users.user_id=user_group.user_id
					AND
						groups.group_id=user_group.group_id
					AND
						users.user_id=$user_id
					AND
						groups.status = 'A'
					AND
						users.unix_status='A'
					AND
						users.status = 'A'
					UNION
					SELECT
						users.unix_uid AS uid,
						groups.group_id + ".$this->SCM_UID_ADD." AS gid,
						users.user_id AS user_id,
						groups.group_id AS group_id,
						users.user_name AS user_name,
						'scm_' || groups.unix_group_name AS unix_group_name
					FROM users,groups,user_group
					WHERE 
						users.user_id=user_group.user_id
					AND
						groups.group_id=user_group.group_id
					AND
						users.user_id=$user_id
					AND
						groups.status = 'A'
					AND
						users.unix_status='A'
					AND
						users.status = 'A'
					AND
						user_group.cvs_flags > 0)
				";
				$res4=db_query($query);
	                	if (!$res4) {
	                        	$this->setError('ERROR - Could Not Update Group Member(s): '.db_error());
	                        	return false;
				}
			}
			return true;
		}
	}

	/**
 	* sysCheckCreateUser() - Check that a user has been created
 	*
 	* @param		int		The ID of the user to check
 	* @returns true on success/false on error
 	*
 	*/
	function sysCheckCreateUser($user_id) {
		return $this->sysCreateUser($user_id);
	}

	/**
 	* sysCheckCreateGroup() - Check that a group has been created
 	*
 	* @param		int		The ID of the user to check
 	* @returns true on success/false on error
 	*
 	*/
	function sysCheckCreateGroup($user_id) {
		return $this->sysCreateGroup($user_id);
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
		} else {
			$query="DELETE FROM nss_usergroups WHERE user_id=$user_id";
			$res1=db_query($query);
			if (!$res1) {
				$this->setError('ERROR - Could Not Delete Group Member(s): '.db_error());
				return false;
			}
			// This is group used for user, not a real project
			$query="DELETE FROM nss_groups WHERE name IN
				(SELECT user_name FROM users WHERE user_id=$user_id)";
			$res2=db_query($query);
			if (!$res2) {
				$this->setError('ERROR - Could Not Delete Group GID: '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
 	* sysUserSetAttribute() - Set an attribute for a user
 	*
 	* @param		int		The user ID 
 	* @param		string	The attribute to set
 	* @param		string	The new value of the attribute
 	* @returns true on success/false on error
 	*
 	*/
	function sysUserSetAttribute($user_id,$attr,$value) {
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
		} else {
			$query="SELECT group_id FROM nss_groups WHERE group_id=$group_id";
			$res=db_query($query);
			if (db_numrows($res) == 0){
				return false;
			} else {
				return true;
			}
		}
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
		} else {
				$query="DELETE FROM nss_usergroups WHERE group_id=$group_id";
				$res1=db_query($query);
	                	if (!$res1) {
					$this->setError('ERROR - Could Not Delete Group Member(s): '.db_error());
	                        	return false;
				}
				$query="DELETE FROM nss_groups WHERE group_id=$group_id";
				$res3=db_query($query);
	                	if (!$res3) {
	                        	$this->setError('ERROR - Could Not Delete Group GID: '.db_error());
	                        	return false;
				}
				$query="INSERT INTO nss_groups
					(user_id, group_id, name, gid)
        				SELECT 0, group_id, unix_group_name, group_id +".$this->GID_ADD."
					FROM groups
					WHERE group_id=$group_id
					"; 
				$res4=db_query($query);
	                	if (!$res4) {
	                        	$this->setError('ERROR - Could Not Insert Group GID: '.db_error());
	                        	return false;
				}
				$query="INSERT INTO nss_groups
					(user_id, group_id, name, gid)
        				SELECT 0, group_id, 'scm_' || unix_group_name, group_id +".$this->SCM_UID_ADD."
					FROM groups
					WHERE group_id=$group_id
					"; 
				$res5=db_query($query);
	                	if (!$res5) {
	                        	$this->setError('ERROR - Could Not Insert SCM Group GID: '.db_error());
	                        	return false;
				}
				$query="INSERT INTO nss_usergroups (
					SELECT
						users.unix_uid AS uid,
						groups.group_id + ".$this->GID_ADD." AS gid,
						users.user_id AS user_id,
						groups.group_id AS group_id,
						users.user_name AS user_name,
						groups.unix_group_name AS unix_group_name
					FROM users,groups,user_group
					WHERE 
						users.user_id=user_group.user_id
					AND
						groups.group_id=user_group.group_id
					AND
						groups.group_id=$group_id
					AND
						groups.status = 'A'
					AND
						users.unix_status='A'
					AND
						users.status = 'A'
					UNION
					SELECT
						users.unix_uid AS uid,
						groups.group_id + ".$this->SCM_UID_ADD." AS gid,
						users.user_id AS user_id,
						groups.group_id AS group_id,
						users.user_name AS user_name,
						'scm_' || groups.unix_group_name AS unix_group_name
					FROM users,groups,user_group
					WHERE 
						groups.group_id=user_group.group_id
					AND
						users.user_id=user_group.user_id
					AND
						groups.group_id=$group_id
					AND
						groups.status = 'A'
					AND
						users.unix_status='A'
					AND
						users.status = 'A'
					AND
						user_group.cvs_flags > 0);
				";
				$res6=db_query($query);
	                	if (!$res6) {
	                        	$this->setError('ERROR - Could Not Update Group Member(s): '.db_error());
	                        	return false;
				}
		}
		return true;
	}

	/**
 	* sysRemoveGroup() - Remove a group
 	* 
 	* @param		int		The ID of the group to remove
 	* @returns true on success/false on error
 	*
 	*/
	function sysRemoveGroup($group_id) {
		$query="DELETE FROM nss_usergroups WHERE group_id=$group_id";
//echo "<h2>SYS::sysRemoveGroup: $query</h2>";
		$res1=db_query($query);
		if (!$res1) {
			$this->setError('ERROR - Could Not Delete Group Member(s): '.db_error());
			return false;
		}
		$query="DELETE FROM nss_groups WHERE group_id=$group_id ";
//echo "<h2>SYS::sysRemoveGroup: $query</h2>";
		$res3=db_query($query);
	              	if (!$res3) {
	                      	$this->setError('ERROR - Could Not Delete Group GID: '.db_error());
	                      	return false;
		}
		return true;
	}

	/**
 	* sysGroupAddUser() - Add a user to a group
 	*
 	* @param		int		The ID of the group two which the user will be added
 	* @param		int		The ID of the user to add
 	* @param		bool	Only add this user to CVS
 	* @returns true on success/false on error
 	*
 	*/
	function sysGroupAddUser($group_id,$user_id,$cvs_only=0) {
		if ($cvs_only) {
			$query="DELETE FROM nss_usergroups WHERE user_id=$user_id AND group_id=$group_id
			AND unix_group_name LIKE 'scm_%'";
		} else {
			$query="DELETE FROM nss_usergroups WHERE user_id=$user_id AND group_id=$group_id";
		}
//echo "<h2>SYS::sysGroupAddUser DELETE: $query</h2>";
		$res0=db_query($query);
		if (!$res0) {
			$this->setError('ERROR - Could Not Delete Group Member(s): '.db_error());
			return false;
		}
		$query="INSERT INTO nss_usergroups (
			SELECT
				users.unix_uid AS uid,
				groups.group_id + ".$this->SCM_UID_ADD." AS gid,
				users.user_id AS user_id,
				groups.group_id AS group_id,
				users.user_name AS user_name,
				'scm_' || groups.unix_group_name AS unix_group_name
			FROM users,groups,user_group
			WHERE 
				users.user_id=user_group.user_id
			AND
				groups.group_id=user_group.group_id
			AND
				users.user_id=$user_id
			AND
				groups.group_id=$group_id
			AND
				groups.status = 'A'
			AND
				users.unix_status='A'
			AND
				users.status = 'A'
			AND
				user_group.cvs_flags > 0) ";
//echo "<h2>SYS::sysGroupAddUser ADDCVS: $query</h2>";
		$res1=db_query($query);
		if (!$res1) {
			$this->setError('ERROR - Could Not Add SCM Member(s): '.db_error());
			return false;
		}

		if ($cvs_only) {
			return true;
		}

		$query="INSERT INTO nss_usergroups (
			SELECT
				users.unix_uid AS uid,
				groups.group_id + ".$this->GID_ADD." AS gid,
				users.user_id AS user_id,
				groups.group_id AS group_id,
				users.user_name AS user_name,
				groups.unix_group_name AS unix_group_name
			FROM users,groups,user_group
			WHERE 
				users.user_id=user_group.user_id
			AND
				groups.group_id=user_group.group_id
			AND
				users.user_id=$user_id
			AND
				groups.group_id=$group_id
			AND
				groups.status = 'A'
			AND
				users.unix_status='A'
			AND
				users.status = 'A') ";
//echo "<h2>SYS::sysGroupAddUser ADDSYS: $query</h2>";
		$res2=db_query($query);
		if (!$res2) {
			$this->setError('ERROR - Could Not Add Shell Group Member(s): '.db_error());
			return false;
		}

		return true;
	}

	/**
 	* sysGroupRemoveUser() - Remove a user from a group
 	*
 	* @param		int		The ID of the group from which to remove the user
 	* @param		int		The ID of the user to remove
 	* @param		bool	Only remove user from CVS group
 	* @returns true on success/false on error
 	*
 	*/
	function sysGroupRemoveUser($group_id,$user_id,$cvs_only=0) {
		if ($cvs_only) {
			$query="DELETE FROM nss_usergroups WHERE group_id=$group_id AND user_id=$user_id
			AND unix_group_name LIKE 'scm_%'";
		} else {
			$query="DELETE FROM nss_usergroups WHERE group_id=$group_id AND user_id=$user_id";
		}
//echo "<h2>SYS::sysGroupRemoveUser REM: $query</h2>";
		$res1=db_query($query);
		if (!$res1) {
			$this->setError('ERROR - Could Not Delete Group Member(s): '.db_error());
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
