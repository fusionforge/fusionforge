<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// SourceForge LDAP module
// pfalcon@users.sourceforge.net 2000-10-17
//
// $Id: ldap.php,v 1.7 2000/11/22 00:05:51 pfalcon Exp $


/*
 * Auxilary functions
 */

/*
 * Error message passing facility
 */

//var $_sf_ldap_error_msg;
function sf_ldap_set_error_msg($msg) {
	global $_sf_ldap_error_msg;
	$_sf_ldap_error_msg .= $msg;
}
function sf_ldap_get_error_msg() {
	global $_sf_ldap_error_msg;
	return $_sf_ldap_error_msg;
}
function sf_ldap_reset_error_msg() {
	global $_sf_ldap_error_msg;
	$_sf_ldap_error_msg='';
}

/*
 * Wrappers for PHP LDAP functions
 */

function sf_ldap_connect() {
	global $sys_ldap_host,$sys_ldap_port;
	global $sys_ldap_bind_dn,$sys_ldap_passwd,$ldap_conn;

	if (!$ldap_conn) {
		sf_ldap_reset_error_msg();
		$ldap_conn = @ldap_connect($sys_ldap_host,$sys_ldap_port);
		if (!$ldap_conn) {
			sf_ldap_set_error_msg('ERROR: Cannot connect to LDAP server<br>');
			return false;
		}
		ldap_bind($ldap_conn,$sys_ldap_bind_dn,$sys_ldap_passwd);
	}
	return true;
}

function sf_ldap_add($dn,$entry) {
	global $ldap_conn;
	return @ldap_add($ldap_conn,$dn,$entry);
}

function sf_ldap_delete($dn) {
	global $ldap_conn;
	return @ldap_delete($ldap_conn,$dn);
}

function sf_ldap_modify($dn,$entry) {
	global $ldap_conn;
	return @ldap_modify($ldap_conn,$dn,$entry);
}

function sf_ldap_mod_add($dn,$entry) {
	global $ldap_conn;
	return @ldap_mod_add($ldap_conn,$dn,$entry);
}

function sf_ldap_mod_del($dn,$entry) {
	global $ldap_conn;
	return @ldap_mod_del($ldap_conn,$dn,$entry);
}

function sf_ldap_read($dn,$filter,$attrs=0) {
	global $ldap_conn;
	return @ldap_read($ldap_conn,$dn,$filter,$attrs);
}

function sf_ldap_error() {
	global $ldap_conn;
	return ldap_error($ldap_conn);
}

function sf_ldap_errno() {
	global $ldap_conn;
	return ldap_errno($ldap_conn);
}

function sf_ldap_already_exists() {
	global $ldap_conn;
	return ldap_errno($ldap_conn)==20;
}

function sf_ldap_does_not_exist() {
	global $ldap_conn;
	return ldap_errno($ldap_conn)==16;
}

/*
 * User management functions
 */

function sf_ldap_check_user($user_id) {
	global $ldap_conn;
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

	if (!sf_ldap_connect()) return false;
        $user = &user_get_object($user_id);
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;
	$res=sf_ldap_read($dn,"objectClass=*",array("uid"));
	if ($res) {
    		ldap_free_result($res);
		return true;
	}
	return false;
}

function sf_ldap_create_user($user_id) {
        $user = &user_get_object($user_id);
//echo "sf_ldap_create_user(".$user->getUnixName().")<br>";
	return sf_ldap_create_user_from_object($user);
}

function sf_ldap_check_create_user($user_id) {
        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

//echo "sf_ldap_check_create_user(".$user_id.")<br>";

        if (!sf_ldap_check_user($user_id)){
    		$user = &user_get_object($user_id);
		return sf_ldap_create_user_from_object($user);
	}
	return true;
}

function sf_ldap_create_user_from_object(&$user) {
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

//echo "sf_ldap_create_user_from_object(".$user->getUnixName().")<br>";
	if (!sf_ldap_connect()) return false;
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;
	$entry['objectClass'][0]='top';
	$entry['objectClass'][1]='account';
	$entry['objectClass'][2]='posixAccount';
	$entry['objectClass'][3]='shadowAccount';
	$entry['objectClass'][4]='x-sourceforgeAccount';
	$entry['uid']=$user->getUnixName();
	$entry['cn']=$user->getRealName();
	$entry['gecos']=$user->getRealName();
	$entry['userPassword']='{crypt}'.$user->getUnixPasswd();
	$entry['homeDirectory']="/home/users/".$user->getUnixName();
	$entry['loginShell']=$user->getShell();
	$entry['x-cvsShell']="/bin/cvssh"; // unless explicitly set otherwise, developer has write access
	$entry['uidNumber']=$user->getUnixUID();
	$entry['gidNumber']=100; // users
	$entry['shadowLastChange']=0; // TODO FIXME
	$entry['shadowMax']=99999;
	$entry['shadowWarning']=7;

	if (!sf_ldap_add($dn,$entry)) {
		sf_ldap_set_error_msg("ERROR: cannot add LDAP user entry '".
	                 $user->getUnixName()."': ".sf_ldap_error()."<br>");
		return false;
	}
	return true;
}

function sf_ldap_remove_user($user_id) {
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $user = &user_get_object($user_id);
//echo "sf_ldap_remove_user(".$user->getUnixName().")<br>";
	if (!sf_ldap_connect()) return false;
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;

	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP user entry '".
	                 $user->getUnixName()."': ".sf_ldap_error()."<br>");
	    return false;
	}
	return true;
}

function sf_ldap_user_set_attribute($user_id,$attr,$value) {
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $user = &user_get_object($user_id);
//echo "sf_ldap_user_set_attribute(".$user->getUnixName().",".$attr.",".$value.")<br>";
	if (!sf_ldap_connect()) return false;
	$dn = 'uid='.$user->getUnixName().',ou=People,'.$sys_ldap_base_dn;
	$entry[$attr]=$value;

	if (!sf_ldap_modify($dn,$entry)) {
	    sf_ldap_set_error_msg("ERROR: cannot change LDAP attribute '$attr' for user '".
	                 $user->getUnixName()."': ".sf_ldap_error()."<br>");
	    return false;
	}
	return true;
}

/*
 * Group management functions
 */

function sf_ldap_check_group($group_id) {
	global $ldap_conn;
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $group = &group_get_object($group_id);
//echo "sf_ldap_check_group(".$group->getUnixName().")";
	if (!sf_ldap_connect()) return false;
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$res=sf_ldap_read($dn,"objectClass=*",array("cn"));
	if ($res) {
	    ldap_free_result($res);
	    return true;
	}
	return false;
}

function sf_ldap_create_group($group_id,$with_members=1) {
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $group = &group_get_object($group_id);
//echo "sf_ldap_create_group(".$group->getUnixName().",$with_members)<br>";
	if (!sf_ldap_connect()) return false;
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$entry['objectClass'][0]='top';
	$entry['objectClass'][1]='posixGroup';
	$entry['cn']=$group->getUnixName();
	$entry['userPassword']='{crypt}x';
	$entry['gidNumber']=$group->getGroupId();

	if ($with_members) {
//echo "adding members:<br>";
		$res=db_query("SELECT users.user_name,user_group.cvs_flags ".
		"FROM users,user_group ".
		"WHERE user_group.group_id=".$group->getGroupId().
		" AND user_group.user_id=users.user_id");

		$i=0; $i_cvs=0;
		while ($user_row=db_fetch_array($res)) {
//echo "+",$user_row[user_name],"<br>";
			$entry['memberUid'][$i++]=$user_row[user_name];
			if ($user_row[cvs_flags]>0) {
				$cvs_member_list[$i_cvs++]=$user_row[user_name];
			}
		}
	}

	$ret_val=true;
	
	if (!sf_ldap_add($dn,$entry)) {
	    sf_ldap_set_error_msg("ERROR: cannot add LDAP group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."<br>");
	    // If there's error, that's bad. But don't stop.
	    $ret_val=false;
	}

	//
	//	Now create CVS group
	//

	$dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;
	if ($cvs_member_list)	$entry['memberUid']=$cvs_member_list;
	else			unset($entry['memberUid']);
	if (!sf_ldap_add($dn,$entry)) {
	    sf_ldap_set_error_msg("ERROR: cannot add LDAP CVS group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."<br>");
	    $ret_val=false;
	}

	return $ret_val;
}

function sf_ldap_remove_group($group_id) {
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $group = &group_get_object($group_id);
//echo "sf_ldap_remove_group(".$group->getUnixName().")<br>";
	if (!sf_ldap_connect()) return false;

	//
	//	Remove shell LDAP group
	//
	$ret_val=true;
	
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."<br>");
	    $ret_val=false;
	}

	//
	//	Remove CVS LDAP group
	//
	$dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;
	if (!sf_ldap_delete($dn)) {
	    sf_ldap_set_error_msg("ERROR: cannot delete LDAP cvs group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."<br>");
	    $ret_val=false;
	}

	return $ret_val;
}

function sf_ldap_group_add_user($group_id,$user_id,$cvs_only=0) {
	global $ldap_conn;
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $group = &group_get_object($group_id);
        $user  = &user_get_object($user_id);
	if (!sf_ldap_connect()) return false;
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$cvs_dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;
	$entry['memberUid']=$user->getUnixName();
	
	//
	//	Check if user already a member of CVS group
	//
	$res=sf_ldap_read($cvs_dn,"memberUid=".$user->getUnixName(),array("cn"));
	if ($res && ldap_count_entries($ldap_conn,$res)>0) {
//echo "already a member of CVS<br>";
	} else {
		//
		//	No, add one
		//

		if (!sf_ldap_mod_add($cvs_dn,$entry)) {
	    		sf_ldap_set_error_msg("ERROR: cannot add member to LDAP CVS group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."<br>");
			return false;
		}
	}
        ldap_free_result($res);
	
	if ($cvs_only)	return true;
	
	//
	//	Check if user already a member of shell group
	//
	$res=sf_ldap_read($dn,"memberUid=".$user->getUnixName(),array("cn"));
	if ($res && ldap_count_entries($ldap_conn,$res)>0) {
//echo "already a member<br>";
	} else {
		//
		//	No, add one
		//

		if (!sf_ldap_mod_add($dn,$entry)) {
	    		sf_ldap_set_error_msg("ERROR: cannot add member to LDAP group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."<br>");
			return false;
		}
	}
        ldap_free_result($res);

	return true;
}

function sf_ldap_group_remove_user($group_id,$user_id,$cvs_only=0) {
	global $sys_ldap_base_dn;

        global $sys_use_ldap;
        if (!$sys_use_ldap) return true;

        $group = &group_get_object($group_id);
        $user  = &user_get_object($user_id);
	if (!sf_ldap_connect()) return false;
	$dn = 'cn='.$group->getUnixName().',ou=Group,'.$sys_ldap_base_dn;
	$cvs_dn = 'cn='.$group->getUnixName().',ou=cvsGroup,'.$sys_ldap_base_dn;
	$entry['memberUid']=$user->getUnixName();

	$ret_val=true;
	if (!sf_ldap_mod_del($cvs_dn,$entry) && !sf_ldap_does_not_exist()) {
	    sf_ldap_set_error_msg("ERROR: cannot remove member from LDAP CVS group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."(".sf_ldap_errno().")"."<br>");
	    $ret_val=false;
	}
	
	if ($cvs_only) return $ret_val;

	if (!sf_ldap_mod_del($dn,$entry) && !sf_ldap_does_not_exist()) {
	    sf_ldap_set_error_msg("ERROR: cannot remove member from LDAP group entry '".
	                 $group->getUnixName()."': ".sf_ldap_error()."(".sf_ldap_errno().")"."<br>");
	    $ret_val=false;
	}
	
	return $ret_val;
}

?>
