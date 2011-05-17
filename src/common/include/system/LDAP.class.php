<?php
/**
 * FusionForge system users integration
 *
 * Copyright 2004, Christian Bayle
 * Copyright 2010, Roland Mas
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

require_once $gfcommon.'include/account.php';
require_once $gfcommon.'include/system/UNIX.class.php';

class LDAP extends UNIX {
	/**
	*	LDAP()
	*
	*/
	function LDAP() {
		$this->UNIX();
		return true;
	}

	/*
 	* Auxilary functions
 	*/
	
	/**
 	*	asciize() - Replace non-ascii characters with question marks
 	*
 	*	LDAP expects utf-8 encoded character string. Since we cannot
 	*	know which encoding 8-bit characters in database use, we
 	*	just replace them with question marks.
 	*
 	*  @param		string	UTF-8 encoded character string.
 	*	@return string which contains only ascii characters
 	*/
	function asciize($str) {
		if (!$str) {
			// LDAP don't allow empty strings for some attributes
			return '?';
		}
	
		return preg_replace("/[\x80-\xff]/","?",$str);
	}

	/*
	 * Wrappers for PHP LDAP functions
	 */

	/**
	 * gfLdapConnect() - Connect to the LDAP server
	 *
	 * @returns true on success/false on error
	 *
	 */
	function gfLdapConnect() {

		global $ldap_conn;

		if (!$ldap_conn) {
			$this->clearError();
			$ldap_conn = @ldap_connect(forge_get_config('ldap_host'),forge_get_config('ldap_port'));
			if (!$ldap_conn) {
				$this->setError('ERROR: Cannot connect to LDAP server<br />');
				return false;
			}
			if (forge_get_config('ldap_version')) {
				ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, forge_get_config('ldap_version'));
			}
			ldap_bind($ldap_conn,forge_get_config('ldap_bind_dn'),forge_get_config('ldap_password'));
		}
		return true;
	}

	/**
	 * gfLdapAdd() - Wrapper for ldap_add()
	 * 
	 * @param		string	dn
	 * @param		string	entry
	 *
	 */
	function gfLdapAdd($dn, $entry) {
		global $ldap_conn;
		return @ldap_add($ldap_conn,$dn,$entry);
	}

	/**
	 * gfLdapDelete() - Wrapper for ldap_delete()
	 *
	 * @param		string	dn
	 *
	 */
	function gfLdapDelete($dn) {
		global $ldap_conn;
		return @ldap_delete($ldap_conn,$dn);
	}

	/**
	 * gfLdapModify() - Wrapper for ldap_modify()
	 *
	 * @param		string	dn
	 * @param		string	entry
	 *
	 */
	function gfLdapModify($dn,$entry) {
		global $ldap_conn;
		return @ldap_modify($ldap_conn,$dn,$entry);
	}
	
	/**
	 * gfLdapModifyIfExists() - Wrapper for ldap_modify()
	 * works like gfLdapModify, but returns true if the LDAP entry does not exist
	 *
	 * @param		string	dn
	 * @param		string	entry
	 *
	 */
	function gfLdapModifyIfExists($dn,$entry) {
        	$res = $this->gfLdapModify($dn,$entry);
        	if ($res) {
                	return true ;
        	} else {
                	$err = ldap_errno ($ldap_conn) ;
                	if ($err == 32) {
                        	return true ;
                	} else {
                        	return false ;
                	}
        	};
	}

	/**
	 * gfLdapModAdd() - Wrapper for ldap_mod_add()
	 *
	 * @param		string	dn
	 * @param		string	entry
	 *
	 */
	function gfLdapModAdd($dn,$entry) {
		global $ldap_conn;
		return @ldap_mod_add($ldap_conn,$dn,$entry);
	}
	
	/**
	 * gfLdapModDel() - Wrapper for ldap_mod_del()
	 *
	 * @param		string	dn
	 * @param		string	entry
	 *
	 */
	function gfLdapModDel($dn,$entry) {
		global $ldap_conn;
		return @ldap_mod_del($ldap_conn,$dn,$entry);
	}
	
	/**
	 * gfLdapRead() - Wrapper for ldap_read()
	 *
	 * @param		string	dn
	 * @param		string	filter
	 * @param		int		attrs
	 *
	 */
	function gfLdapRead($dn,$filter,$attrs=0) {
		global $ldap_conn;
		return @ldap_read($ldap_conn,$dn,$filter,$attrs);
	}
	
	/**
	 * gfLdapError() - Wrapper for ldap_error()
	 *
	 * @see ldap_error()
	 *
	 */
	function gfLdapError() {
		global $ldap_conn;
		return ldap_error($ldap_conn);
	}
	
	/**
	 * gfLdapErrno() - Wrapper for ldap_errno()
	 *
	 * @see ldap_errno()
	 *
	 */
	function gfLdapErrno() {
		global $ldap_conn;
		return ldap_errno($ldap_conn);
	}
	
	/**
	 * gfLdapAlreadyExists()
	 */
	function gfLdapAlreadyExists() {
		global $ldap_conn;
		return ldap_errno($ldap_conn)==20;
	}
	
	/**
	 * gfLdapDoesNotExist()
	 */
	function gfLdapDoesNotExist() {
		global $ldap_conn;
		return ldap_errno($ldap_conn)==16;
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
		return $this->gfLdapcheck_user_by_name($user->getUnixName());
	}
	
	/**
	 * gfLdapcheck_user_by_name() - Check for a user by the username
	 *
	 * @param		string	The username 
	 * @returns true on success/false on error
	 *
	 */
	function gfLdapcheck_user_by_name($user_name) {
		global $ldap_conn;

	
		if (!$this->gfLdapConnect()) {
			return false;
		}
	
		$dn = 'uid='.$user_name.',ou=People,'.forge_get_config('ldap_base_dn');
		$res = $this->gfLdapRead($dn,"objectClass=*",array("uid"));
		if ($res) {
			ldap_free_result($res);
			return true;
		}
	
		return false;
	}
	
	/**
	 * sysCreateUser() - Create a user
	 *
	 * @param		int	The user ID of the user to create
	 * @returns The return status of gfLdapcreate_user_from_object()
	 *
	 */
	function sysCreateUser($user_id) {
		// Check even if the user shouldn't exist
		// It can be created by a cron
		if (!$this->sysCheckUser($user_id)){
			$user = &user_get_object($user_id);
			return $this->gfLdapcreate_user_from_object($user);
		}
		return true;
	}
	
	/**
	 * sysCheckCreateUser() - Check that a user has been created
	 *
	 * @param		int		The ID of the user to check
	 * @returns true on success/false on error
	 *
	 */
	function sysCheckCreateUser($user_id) {
		if (!$this->sysCheckUser($user_id)){
			$user = &user_get_object($user_id);
			return $this->gfLdapcreate_user_from_object($user);
		}
		return true;
	}
	
	/**
	 * gfLdapcreate_user_from_object() - Create a user from information contained within an object
	 *
	 * @param		object	The user object
	 * @returns true on success/false on error
	 *
	 */
	function gfLdapcreate_user_from_object(&$user) {

		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'uid='.$user->getUnixName().',ou=People,'.forge_get_config('ldap_base_dn');
		$entry['objectClass'][0]='top';
		$entry['objectClass'][1]='account';
		$entry['objectClass'][2]='posixAccount';
		$entry['objectClass'][3]='shadowAccount';
		$entry['objectClass'][4]='debGforgeAccount';
		$entry['uid']=$user->getUnixName();
		$entry['cn']=$this->asciize($user->getRealName());
		$entry['gecos']=$this->asciize($user->getRealName());
		$entry['userPassword']='{crypt}'.$user->getUnixPasswd();
		$entry['homeDirectory'] = account_user_homedir($user->getUnixName());
		$entry['loginShell']=$user->getShell();
		$entry['debGforgeCvsShell']="/bin/cvssh"; // unless explicitly set otherwise, developer has write access
		$entry['debGforgeForwardEmail']=$user->getEmail();
		$entry['uidNumber']=$this->getUnixUID();
		$entry['gidNumber']=$this->getUnixGID(); // users as in debian backend
		$entry['shadowLastChange']=1; // We don't have expiration, so any non-0
		$entry['shadowMax']=99999;
		$entry['shadowWarning']=7;
	
		if (!$this->gfLdapAdd($dn,$entry)) {
			$this->setError("ERROR: cannot add LDAP user entry '".
				 $user->getUnixName()."': ".$this->gfLdapError()."<br />");
			return false;
		}
		return true;
	}
	
	/**
	 * gfLdapCreateUserFromProps() - Creates an LDAP user from
	 *
	 * @param		string	The username 
	 * @param		string	????
	 * @param		string	The encrypted password
	 * @returns true on success/false on error
	 *
	 */
	function gfLdapCreateUserFromProps($username, $cn, $crypt_pw,
						$shell, $cvsshell, $uid, $gid, $email) {

		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'uid='.$username.',ou=People,'.forge_get_config('ldap_base_dn');
		$entry['objectClass'][0]='top';
		$entry['objectClass'][1]='account';
		$entry['objectClass'][2]='posixAccount';
		$entry['objectClass'][3]='shadowAccount';
		$entry['objectClass'][4]='debGforgeAccount';
		$entry['uid']=$username;
		$entry['cn']=$this->asciize($cn);
		$entry['gecos']=$this->asciize($cn);
		$entry['userPassword']='{crypt}'.$crypt_pw;
		$entry['homeDirectory'] = account_user_homedir($username);
		$entry['loginShell']=$shell;
		$entry['debGforgeCvsShell']=$cvsshell; 
		$entry['debGforgeForwardEmail']=$email;
		$entry['uidNumber']=$uid;
		$entry['gidNumber']=$gid;
		$entry['shadowLastChange']=1;
		$entry['shadowMax']=99999;
		$entry['shadowWarning']=7;
	
		if (!$this->gfLdapAdd($dn,$entry)) {
			$this->setError("ERROR: cannot add LDAP user entry '".
				 $username."': ".$this->gfLdapError()."<br />");
			return false;
		}
		return true;
	}
	
	/**
	 * sysRemoveUser() - Remove an LDAP user
	 *
	 * @param		int		The user ID of the user to remove
	 * @returns true on success/false on failure
	 *
	 */
	function sysRemoveUser($user_id) {

	
		$user = &user_get_object($user_id);
		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'uid='.$user->getUnixName().',ou=People,'.forge_get_config('ldap_base_dn');
	
		if (!$this->gfLdapDelete($dn)) {
		    $this->setError("ERROR: cannot delete LDAP user entry '".
				 $user->getUnixName()."': ".$this->gfLdapError()."<br />");
		    return false;
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

	
		$user = &user_get_object($user_id);
		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'uid='.$user->getUnixName().',ou=People,'.forge_get_config('ldap_base_dn');
		$entry[$attr]=$value;
	
		if (!$this->gfLdapModifyIfExists($dn, $entry)) {
		    $this->setError("ERROR: cannot change LDAP attribute '$attr' for user '".
				 $user->getUnixName()."': ".$this->gfLdapError()."<br />");
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
		global $ldap_conn;

	
		$group = &group_get_object($group_id);
		if (!$group) {
			$this->setError("ERROR: Cannot find group [$group_id]<br />");
			return false;
		}
		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'cn='.$group->getUnixName().',ou=Group,'.forge_get_config('ldap_base_dn');
		$res=$this->gfLdapRead($dn, "objectClass=*", array("cn"));
		if ($res) {
			ldap_free_result($res);
			return true;
		}
		return false;
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
		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'cn='.$group->getUnixName().',ou=Group,'.forge_get_config('ldap_base_dn');
		$entry['objectClass'][0]='top';
		$entry['objectClass'][1]='posixGroup';
		$entry['cn']=$group->getUnixName();
		$entry['userPassword']='{crypt}x';
		$entry['gidNumber']=$this->getUnixGID();
	
		$i=0; $i_cvs=0;
	
		$ret_val=true;
		
		if (!$this->gfLdapAdd($dn,$entry)) {
		    $this->setError("ERROR: cannot add LDAP group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."<br />");
		    // If there's error, that's bad. But don't stop.
		    $ret_val=false;
		}
	
		//
		//	Now create CVS group
		//
	
		// Add virtual anoncvs user to CVS group
		$cvs_member_list[$i_cvs++] = 'anoncvs_'.$group->getUnixName();
	
		$dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.forge_get_config('ldap_base_dn');
	
		if ($cvs_member_list) {
			$entry['memberUid']=$cvs_member_list;
		} else {
			unset($entry['memberUid']);
		}
	
		if (!$this->gfLdapAdd($dn,$entry)) {
			$this->setError("ERROR: cannot add LDAP CVS group entry '"
				 .$group->getUnixName()."': ".$this->gfLdapError()."<br />");
			$ret_val=false;
		}
	
		//
		// Finally, setup AnonCVS virtual user
		//
	
	        if (!$this->gfLdapcheck_user_by_name('anoncvs_'.$group->getUnixName())
		    && !$this->gfLdapCreateUserFromProps('scm_'.$group->getUnixName(),
							'anoncvs', 'x',
							'/bin/false', '/bin/false',
							$this->getSCMGID(),
							$this->getUnixGID(), "/dev/null")) {
			$this->setError("ERROR: cannot add LDAP AnonCVS user entry '"
				 .$group->getUnixName()."': ".$this->gfLdapError()."<br />");
			$ret_val=false;
		}
	
		return $ret_val;
	}
	
	/**
	 * sysRemoveGroup() - Remove a group
	 * 
	 * @param		int		The ID of the group to remove
	 * @returns true on success/false on error
	 *
	 */
	function sysRemoveGroup($group_id) {

	
		$group = &group_get_object($group_id);
		if (!$this->gfLdapConnect()) {
			return false;
		}
	
		//
		//	Remove shell LDAP group
		//
		$ret_val=true;
		
		$dn = 'cn='.$group->getUnixName().',ou=Group,'.forge_get_config('ldap_base_dn');
	
		if (!$this->gfLdapDelete($dn)) {
		    $this->setError("ERROR: cannot delete LDAP group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."<br />");
		    $ret_val = false;
		}
	
		//
		//	Remove CVS LDAP group
		//
	
		$dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.forge_get_config('ldap_base_dn');
	
		if (!$this->gfLdapDelete($dn)) {
		    $this->setError("ERROR: cannot delete LDAP CVS group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."<br />");
		    $ret_val = false;
		}
	
		//
		//	Remove AnonCVS virtual user
		//
	
		$dn = 'uid=anoncvs_'.$group->getUnixName().',ou=People,'.forge_get_config('ldap_base_dn');
		if (!$this->gfLdapDelete($dn)) {
		    $this->setError("ERROR: cannot delete LDAP AnonCVS user entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."<br />");
		    $ret_val = false;
		}
	
		return $ret_val;
	}
	
	function sysGroupCheckUser($group_id,$user_id) {
		db_begin () ;
		if (! $this->sysGroupRemoveUser($group_id,$user_id)) {
			db_rollback () ;
			return false;
		}
		
		$u = user_get_object($user_id) ;
		$p = group_get_object($group_id) ;
		if (forge_check_perm_for_user($u,'scm',$group_id,'write')) {
			if ($u->isMember($p)) {
				$this->sysGroupAddUser($group_id,$user_id,false) ;
			} else {
				$this->sysGroupRemoveUser($group_id,$user_id,false) ;
				$this->sysGroupAddUser($group_id,$user_id,true) ;
			}
		} else {
			if ($u->isMember($p)) {
				$this->sysGroupAddUser($group_id,$user_id,false) ;
				$this->sysGroupRemoveUser($group_id,$user_id,true) ;
			} else {
				$this->sysGroupRemoveUser($group_id,$user_id,false) ;
			}
		}
	}

	/**
	 * sysGroupAddUser() - Add a user to an LDAP group
	 *
	 * @param		int		The ID of the group two which the user will be added
	 * @param		int		The ID of the user to add
	 * @param		bool	Only add this user to CVS
	 * @returns true on success/false on error
	 *
	 */
	function sysGroupAddUser($group_id,$user_id,$cvs_only=0) {
		global $ldap_conn;

	
		$group = &group_get_object($group_id);
		$user  = &user_get_object($user_id);
		if (!$this->gfLdapConnect()) {
			return false;
		}
		$dn = 'cn='.$group->getUnixName().',ou=Group,'.forge_get_config('ldap_base_dn');
		$cvs_dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.forge_get_config('ldap_base_dn');
		$entry['memberUid'] = $user->getUnixName();
		
		//
		//	Check if user already a member of CVS group
		//
	
		$res=$this->gfLdapRead($cvs_dn,"memberUid=".$user->getUnixName(),array("cn"));
		if ($res && ldap_count_entries($ldap_conn,$res)>0) {
			//echo "already a member of CVS<br />";
		} else {
			//
			//	No, add one
			//
	
			if (!$this->gfLdapModAdd($cvs_dn,$entry)) {
				$this->setError("ERROR: cannot add member to LDAP CVS group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."<br />");
				return false;
			}
		}
	
		ldap_free_result($res);
		
		if ($cvs_only) {
			return true;
		}
		
		//
		//	Check if user already a member of shell group
		//
		$res = $this->gfLdapRead($dn, "memberUid=".$user->getUnixName(), array("cn"));
	
		if ($res && ldap_count_entries($ldap_conn,$res)>0) {
			//echo "already a member<br />";
		} else {
			//
			//	No, add one
			//
	
			if (!$this->gfLdapModAdd($dn,$entry)) {
				$this->setError("ERROR: cannot add member to LDAP group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."<br />");
				return false;
			}
		}
	
		ldap_free_result($res);
	
		return true;
	}
	
	/**
	 * sysGroupRemoveUser() - Remove a user from an LDAP group
	 *
	 * @param		int		The ID of the group from which to remove the user
	 * @param		int		The ID of the user to remove
	 * @param		bool	Only remove user from CVS group
	 * @returns true on success/false on error
	 *
	 */
	function sysGroupRemoveUser($group_id,$user_id,$cvs_only=0) {

	
		$group = &group_get_object($group_id);
		$user  = &user_get_object($user_id);
		if (!$this->gfLdapConnect()) {
			return false;
		}
	
		$dn = 'cn='.$group->getUnixName().',ou=Group,'.forge_get_config('ldap_base_dn');
		$cvs_dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.forge_get_config('ldap_base_dn');
		$entry['memberUid'] = $user->getUnixName();
	
		$ret_val=true;
	
		if (!$this->gfLdapModDel($cvs_dn,$entry) && !$this->gfLdapDoesNotExist()) {
			$this->setError("ERROR: cannot remove member from LDAP CVS group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."(".$this->gfLdapErrno().")"."<br />");
			$ret_val=false;
		}
		
		if ($cvs_only) {
			return $ret_val;
		}
	
		if (!$this->gfLdapModDel($dn,$entry) && !$this->gfLdapDoesNotExist()) {
			$this->setError("ERROR: cannot remove member from LDAP group entry '".
				 $group->getUnixName()."': ".$this->gfLdapError()."(".$this->gfLdapErrno().")"."<br />");
			$ret_val=false;
		}
		
		return $ret_val;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
