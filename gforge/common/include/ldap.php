<?php
/**
 * ldap.php - The LDAP library
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 * @author Paul Sokolovsky pfalcon@users.sourceforge.net
 * @date 2000-10-17
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

require_once('common/include/account.php');

/*
 * Constants
 */

/**
 * Value to add to group_id to get unix gid
 *
 * @var	constant		$GID_ADD
 */
$GID_ADD = 10000;

/**
 * Value to add to unix_uid to get unix uid
 * 
 * @var	constant		$UID_ADD
 */
$UID_ADD = 20000;

/**
 * Value to add to unix gid to get unix uid of anoncvs special user
 *
 * @var	constant		$ANONCVS_UID_ADD
 */
$ANONCVS_UID_ADD = 50000;

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

	return ereg_replace("[\x80-\xff]","?",$str);
}

/*
 * Error message passing facility
 */

/**
 * sf_ldap_set_error_msg() - Set an LDAP error message
 *
 * @param		string	The message string to set
 *
 */
//var $_sf_ldap_error_msg;
function sf_ldap_set_error_msg($msg) {
	global $_sf_ldap_error_msg;
	$_sf_ldap_error_msg .= $msg;
}

/**
 * sf_ldap_get_error_msg() - Get an LDAP error message
 *
 * @returns The error message string
 *
 */
function sf_ldap_get_error_msg() {
	global $_sf_ldap_error_msg;
	return $_sf_ldap_error_msg;
}

/**
 * sf_ldap_reset_error_msg() - Reset the stored LDAP error message
 *
 */
function sf_ldap_reset_error_msg() {
	global $_sf_ldap_error_msg;
	$_sf_ldap_error_msg='';
}


/*
 * Wrappers for PHP LDAP functions
 */

/**
 * sf_ldap_connect() - Connect to the LDAP server
 *
 * @returns true on success/false on error
 *
 */
function sf_ldap_connect() {
	global $sys_ldap_host,$sys_ldap_port;
	global $sys_ldap_bind_dn,$sys_ldap_passwd,$ldap_conn,$sys_ldap_version;

	if (!$ldap_conn) {
		sf_ldap_reset_error_msg();
		$ldap_conn = @ldap_connect($sys_ldap_host,$sys_ldap_port);
		if (!$ldap_conn) {
			sf_ldap_set_error_msg('ERROR: Cannot connect to LDAP server<br />');
			return false;
		}
		if ($sys_ldap_version) {
			ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $sys_ldap_version);
		}
		ldap_bind($ldap_conn,$sys_ldap_bind_dn,$sys_ldap_passwd);
	}
	return true;
}

/**
 * sf_ldap_add() - Wrapper for ldap_add()
 * 
 * @param		string	dn
 * @param		string	entry
 *
 */
function sf_ldap_add($dn, $entry) {
	global $ldap_conn;
	return @ldap_add($ldap_conn,$dn,$entry);
}

/**
 * sf_ldap_delete() - Wrapper for ldap_delete()
 *
 * @param		string	dn
 *
 */
function sf_ldap_delete($dn) {
	global $ldap_conn;
	return @ldap_delete($ldap_conn,$dn);
}

/**
 * sf_ldap_modify() - Wrapper for ldap_modify()
 *
 * @param		string	dn
 * @param		string	entry
 *
 */
function sf_ldap_modify($dn,$entry) {
	global $ldap_conn;
	return @ldap_modify($ldap_conn,$dn,$entry);
}

/**
 * sf_ldap_modify_if_exists() - Wrapper for ldap_modify()
 * works like sf_ldap_modify, but returns true if the LDAP entry does not exist
 *
 * @param		string	dn
 * @param		string	entry
 *
 */
function sf_ldap_modify_if_exists($dn,$entry) {
        $res = sf_ldap_modify($dn,$entry);
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
 * sf_ldap_mod_add() - Wrapper for ldap_mod_add()
 *
 * @param		string	dn
 * @param		string	entry
 *
 */
function sf_ldap_mod_add($dn,$entry) {
	global $ldap_conn;
	return @ldap_mod_add($ldap_conn,$dn,$entry);
}

/**
 * sf_ldap_mod_del() - Wrapper for ldap_mod_del()
 *
 * @param		string	dn
 * @param		string	entry
 *
 */
function sf_ldap_mod_del($dn,$entry) {
	global $ldap_conn;
	return @ldap_mod_del($ldap_conn,$dn,$entry);
}

/**
 * sf_ldap_read() - Wrapper for ldap_read()
 *
 * @param		string	dn
 * @param		string	filter
 * @param		int		attrs
 *
 */
function sf_ldap_read($dn,$filter,$attrs=0) {
	global $ldap_conn;
	return @ldap_read($ldap_conn,$dn,$filter,$attrs);
}

/**
 * sf_ldap_error() - Wrapper for ldap_error()
 *
 * @see ldap_error()
 *
 */
function sf_ldap_error() {
	global $ldap_conn;
	return ldap_error($ldap_conn);
}

/**
 * sf_ldap_errno() - Wrapper for ldap_errno()
 *
 * @see ldap_errno()
 *
 */
function sf_ldap_errno() {
	global $ldap_conn;
	return ldap_errno($ldap_conn);
}

/**
 * sf_ldap_already_exists()
 */
function sf_ldap_already_exists() {
	global $ldap_conn;
	return ldap_errno($ldap_conn)==20;
}

/**
 * sf_ldap_does_not_exist()
 */
function sf_ldap_does_not_exist() {
	global $ldap_conn;
	return ldap_errno($ldap_conn)==16;
}

/*
 * User management functions
 */

/**
 * sf_ldap_check_user() - Check for the existence of a user
 * 
 * @param		int		The user ID of the user to check
 * @returns true on success/false on error
 *
 */
function sf_ldap_check_user($user_id) {
	$user =& user_get_object($user_id);
	if (!$user) {
		return false;
	}
	return sf_ldap_check_user_by_name($user->getUnixName());
}

/**
 * sf_ldap_check_user_by_name() - Check for a user by the username
 *
 * @param		string	The username 
 * @returns true on success/false on error
 *
 */
function sf_ldap_check_user_by_name($user_name) {
	global $ldap_conn;
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	if (!sf_ldap_connect()) {
		return false;
	}

	$dn = 'uid='.$user_name.',ou=People,'.$sys_ldap_base_dn;
	$res = sf_ldap_read($dn,"objectClass=*",array("uid"));
	if ($res) {
		ldap_free_result($res);
		return true;
	}

	return false;
}

/**
 * sf_ldap_create_user() - Create a user
 *
 * @param		int	The user ID of the user to create
 * @returns The return status of sf_ldap_create_user_from_object()
 *
 */
function sf_ldap_create_user($user_id) {
	$user = &user_get_object($user_id);
	return sf_ldap_create_user_from_object($user);
}

/**
 * sf_ldap_check_create_user() - Check that a user has been created
 *
 * @param		int		The ID of the user to check
 * @returns true on success/false on error
 *
 */
function sf_ldap_check_create_user($user_id) {
	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	if (!sf_ldap_check_user($user_id)){
		$user = &user_get_object($user_id);
		return sf_ldap_create_user_from_object($user);
	}
	return true;
}

/**
 * sf_ldap_create_user_from_object() - Create a user from information contained within an object
 *
 * @param		object	The user object
 * @returns true on success/false on error
 *
 */
function sf_ldap_create_user_from_object(&$user) {
	global $sys_ldap_base_dn;
	global $UID_ADD;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

//echo "sf_ldap_create_user_from_object(".$user->getUnixName().")<br />";
	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;
	$entry['objectClass'][0]='top';
	$entry['objectClass'][1]='account';
	$entry['objectClass'][2]='posixAccount';
	$entry['objectClass'][3]='shadowAccount';
	$entry['objectClass'][4]='debGforgeAccount';
	$entry['uid']=$user->getUnixName();
	$entry['cn']=asciize($user->getRealName());
	$entry['gecos']=asciize($user->getRealName());
	$entry['userPassword']='{crypt}'.$user->getUnixPasswd();
	$entry['homeDirectory'] = account_user_homedir($user->getUnixName());
	$entry['loginShell']=$user->getShell();
	$entry['debGforgeCvsShell']="/bin/cvssh"; // unless explicitly set otherwise, developer has write access
	$entry['debGforgeForwardEmail']=$user->getEmail();
	$entry['uidNumber']=$user->getUnixUID() + $UID_ADD;
	$entry['gidNumber']=$user->getUnixUID() + $UID_ADD; // users as in debian backend
	$entry['shadowLastChange']=1; // We don't have expiration, so any non-0
	$entry['shadowMax']=99999;
	$entry['shadowWarning']=7;

	if (!sf_ldap_add($dn,$entry)) {
		sf_ldap_set_error_msg("ERROR: cannot add LDAP user entry '".
			 $user->getUnixName()."': ".sf_ldap_error()."<br />");
		return false;
	}
	return true;
}

/**
 * sf_ldap_create_user_from_props() - Creates an LDAP user from
 *
 * @param		string	The username 
 * @param		string	????
 * @param		string	The encrypted password
 * @returns true on success/false on error
 *
 */
function sf_ldap_create_user_from_props($username, $cn, $crypt_pw,
					$shell, $cvsshell, $uid, $gid, $email) {
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'uid='.$username.',ou=People,'.$sys_ldap_base_dn;
	$entry['objectClass'][0]='top';
	$entry['objectClass'][1]='account';
	$entry['objectClass'][2]='posixAccount';
	$entry['objectClass'][3]='shadowAccount';
	$entry['objectClass'][4]='debGforgeAccount';
	$entry['uid']=$username;
	$entry['cn']=asciize($cn);
	$entry['gecos']=asciize($cn);
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

	if (!sf_ldap_add($dn,$entry)) {
		sf_ldap_set_error_msg("ERROR: cannot add LDAP user entry '".
			 $username."': ".sf_ldap_error()."<br />");
		return false;
	}
	return true;
}

/**
 * sf_ldap_remove_user() - Remove an LDAP user
 *
 * @param		int		The user ID of the user to remove
 * @returns true on success/false on failure
 *
 */
function sf_ldap_remove_user($user_id) {
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	$user = &user_get_object($user_id);
	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;

	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP user entry '".
			 $user->getUnixName()."': ".sf_ldap_error()."<br />");
	    return false;
	}
	return true;
}

/**
 * sf_ldap_user_set_attribute() - Set an attribute for a user
 *
 * @param		int		The user ID 
 * @param		string	The attribute to set
 * @param		string	The new value of the attribute
 * @returns true on success/false on error
 *
 */
function sf_ldap_user_set_attribute($user_id,$attr,$value) {
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	$user = &user_get_object($user_id);
//echo "sf_ldap_user_set_attribute(".$user->getUnixName().",".$attr.",".$value.")<br />";
	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;
	$entry[$attr]=$value;

	if (!sf_ldap_modify_if_exists($dn, $entry)) {
	    sf_ldap_set_error_msg("ERROR: cannot change LDAP attribute '$attr' for user '".
			 $user->getUnixName()."': ".sf_ldap_error()."<br />");
	    return false;
	}
	return true;
}

/*
 * Group management functions
 */

/**
 * sf_ldap_check_group() - Check for the existence of a group
 * 
 * @param		int		The ID of the group to check
 * @returns true on success/false on error
 *
 */
function sf_ldap_check_group($group_id) {
	global $ldap_conn;
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return false;
	}

	$group = &group_get_object($group_id);
	if (!$group) {
		sf_ldap_set_error_msg("ERROR: Cannot find group [$group_id]<br />");
		return false;
	}
	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$res=sf_ldap_read($dn, "objectClass=*", array("cn"));
	if ($res) {
		ldap_free_result($res);
		return true;
	}
	return false;
}

/**
 * sf_ldap_create_group() - Create a group
 * 
 * @param		int		The ID of the group to create
 * @returns true on success/false on error
 *
 */
function sf_ldap_create_group($group_id) {
	global $sys_ldap_base_dn;
	global $GID_ADD;
	global $ANONCVS_UID_ADD;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	$group = &group_get_object($group_id);
	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$entry['objectClass'][0]='top';
	$entry['objectClass'][1]='posixGroup';
	$entry['cn']=$group->getUnixName();
	$entry['userPassword']='{crypt}x';
	$entry['gidNumber']=$group->getID() + $GID_ADD;

	$i=0; $i_cvs=0;

	$ret_val=true;
	
	if (!sf_ldap_add($dn,$entry)) {
	    sf_ldap_set_error_msg("ERROR: cannot add LDAP group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."<br />");
	    // If there's error, that's bad. But don't stop.
	    $ret_val=false;
	}

	//
	//	Now create CVS group
	//

	// Add virtual anoncvs user to CVS group
	$cvs_member_list[$i_cvs++] = 'anoncvs_'.$group->getUnixName();

	$dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;

	if ($cvs_member_list) {
		$entry['memberUid']=$cvs_member_list;
	} else {
		unset($entry['memberUid']);
	}

	if (!sf_ldap_add($dn,$entry)) {
		sf_ldap_set_error_msg("ERROR: cannot add LDAP CVS group entry '"
			 .$group->getUnixName()."': ".sf_ldap_error()."<br />");
		$ret_val=false;
	}

	//
	// Finally, setup AnonCVS virtual user
	//

        if (!sf_ldap_check_user_by_name('anoncvs_'.$group->getUnixName())
	    && !sf_ldap_create_user_from_props('anoncvs_'.$group->getUnixName(),
						'anoncvs', 'x',
						'/bin/false', '/bin/false',
						$group_id+$GID_ADD+$ANONCVS_UID_ADD,
						$group_id+$GID_ADD, "/dev/null")) {
		sf_ldap_set_error_msg("ERROR: cannot add LDAP AnonCVS user entry '"
			 .$group->getUnixName()."': ".sf_ldap_error()."<br />");
		$ret_val=false;
	}

	return $ret_val;
}

/**
 * sf_ldap_remove_group() - Remove a group
 * 
 * @param		int		The ID of the group to remove
 * @returns true on success/false on error
 *
 */
function sf_ldap_remove_group($group_id) {
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	$group = &group_get_object($group_id);
	if (!sf_ldap_connect()) {
		return false;
	}

	//
	//	Remove shell LDAP group
	//
	$ret_val=true;
	
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;

	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."<br />");
	    $ret_val = false;
	}

	//
	//	Remove CVS LDAP group
	//

	$dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;

	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP CVS group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."<br />");
	    $ret_val = false;
	}

	//
	//	Remove AnonCVS virtual user
	//

	$dn = 'uid=anoncvs_'.$group->getUnixName().',ou=People,'.$sys_ldap_base_dn;
	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP AnonCVS user entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."<br />");
	    $ret_val = false;
	}

	return $ret_val;
}

/**
 * sf_ldap_group_add_user() - Add a user to an LDAP group
 *
 * @param		int		The ID of the group two which the user will be added
 * @param		int		The ID of the user to add
 * @param		bool	Only add this user to CVS
 * @returns true on success/false on error
 *
 */
function sf_ldap_group_add_user($group_id,$user_id,$cvs_only=0) {
	global $ldap_conn;
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	$group = &group_get_object($group_id);
	$user  = &user_get_object($user_id);
	if (!sf_ldap_connect()) {
		return false;
	}
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$cvs_dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;
	$entry['memberUid'] = $user->getUnixName();
	
	//
	//	Check if user already a member of CVS group
	//

	$res=sf_ldap_read($cvs_dn,"memberUid=".$user->getUnixName(),array("cn"));
	if ($res && ldap_count_entries($ldap_conn,$res)>0) {
		//echo "already a member of CVS<br />";
	} else {
		//
		//	No, add one
		//

		if (!sf_ldap_mod_add($cvs_dn,$entry)) {
			sf_ldap_set_error_msg("ERROR: cannot add member to LDAP CVS group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."<br />");
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
	$res = sf_ldap_read($dn, "memberUid=".$user->getUnixName(), array("cn"));

	if ($res && ldap_count_entries($ldap_conn,$res)>0) {
		//echo "already a member<br />";
	} else {
		//
		//	No, add one
		//

		if (!sf_ldap_mod_add($dn,$entry)) {
			sf_ldap_set_error_msg("ERROR: cannot add member to LDAP group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."<br />");
			return false;
		}
	}

	ldap_free_result($res);

	return true;
}

/**
 * sf_ldap_group_remove_user() - Remove a user from an LDAP group
 *
 * @param		int		The ID of the group from which to remove the user
 * @param		int		The ID of the user to remove
 * @param		bool	Only remove user from CVS group
 * @returns true on success/false on error
 *
 */
function sf_ldap_group_remove_user($group_id,$user_id,$cvs_only=0) {
	global $sys_ldap_base_dn;

	global $sys_use_ldap;
	if (!$sys_use_ldap) {
		return true;
	}

	$group = &group_get_object($group_id);
	$user  = &user_get_object($user_id);
	if (!sf_ldap_connect()) {
		return false;
	}

	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$cvs_dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;
	$entry['memberUid'] = $user->getUnixName();

	$ret_val=true;

	if (!sf_ldap_mod_del($cvs_dn,$entry) && !sf_ldap_does_not_exist()) {
		sf_ldap_set_error_msg("ERROR: cannot remove member from LDAP CVS group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."(".sf_ldap_errno().")"."<br />");
		$ret_val=false;
	}
	
	if ($cvs_only) {
		return $ret_val;
	}

	if (!sf_ldap_mod_del($dn,$entry) && !sf_ldap_does_not_exist()) {
		sf_ldap_set_error_msg("ERROR: cannot remove member from LDAP group entry '".
			 $group->getUnixName()."': ".sf_ldap_error()."(".sf_ldap_errno().")"."<br />");
		$ret_val=false;
	}
	
	return $ret_val;
}

?>
