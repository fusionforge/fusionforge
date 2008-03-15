<?php
/** External authentication via LDAP for Gforge
 * Copyright 2003 Roland Mas <lolando@debian.org>
 * Copyright 2004 Roland Mas <roland@gnurandal.com> 
 *                The Gforge Group, LLC <http://gforgegroup.com/>
 * Copyright 2004 Christian Bayle <bayle@debian.org> 
 *
 * This file is not part of Gforge
 *
 * This plugin, like Gforge, is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('plugins/ldapextauth/mapping.php') ;

class LdapextauthPlugin extends Plugin {
	function LdapextauthPlugin () {
		$this->Plugin() ;
		$this->name = "ldapextauth";
		$this->hooks[] = "session_before_login";
		
		$this->ldap_conn = false ;

		require_once('plugins/ldapextauth/config.php') ;
                $this->base_dn = $sys_ldap_dn ;
		$this->ldap_server = $sys_ldap_server ;
		$this->ldap_port = $sys_ldap_port ;
		if ($base_dn) {
			$this->base_dn = $base_dn ;
		}
		if ($ldap_server) {
			$this->ldap_server = $ldap_server ;
		}
		if ($ldap_port) {
			$this->ldap_port = $ldap_port ;
		}
		if ($ldap_kind) {
			$this->ldap_kind = $ldap_kind ;
		}
	}
	
	function CallHook ($hookname, $params) {
		global $HTML ;
		
		$loginname = $params['loginname'] ;
		$passwd = $params['passwd'] ;
		
		switch ($hookname) {
		case "session_before_login":
			// Authenticate against LDAP
			$this->AuthUser ($loginname, $passwd) ;
			break;
		case "blah":
			// Should not happen
			break;
		default:
			// Forgot something
		}
	}

	function AuthUser ($loginname, $passwd) {
		global $feedback;
	
		if  (!function_exists ( "ldap_connect" )) {
			return false;
		}

		if (!$this->ldap_conn) {
			$this->ldap_conn = ldap_connect ($this->ldap_server,
							 $this->ldap_port);
		}
		if ($GLOBALS['sys_ldap_version']) {
			ldap_set_option ($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $GLOBALS['sys_ldap_version']);
		}
		$dn = plugin_ldapextauth_getdn ($this, $loginname) ;
		if(empty($dn)) {
			$GLOBALS['ldap_auth_failed']=true;
			return false;
		}

		$u = user_get_object_by_name ($loginname) ;
		if ($u) {
			// User exists in DB
			if (@ldap_bind($this->ldap_conn, $dn, $passwd)) {
				// Password from form is valid in LDAP
				if (session_login_valid_dbonly ($loginname, $passwd, false)) {
					// Also according to DB
					$GLOBALS['ldap_auth_failed']=false;
					return true ;
				} else {
					// Passwords mismatch, update DB's
					$u->setPasswd ($passwd) ;
					$GLOBALS['ldap_auth_failed']=false;
					return true ;
				}
			} else {
				// Wrong password according to LDAP
				$feedback=_('Invalid Password Or User Name');
				$GLOBALS['ldap_auth_failed']=true;
				return false ;
			}
		} else {
			// User doesn't exist in DB yet
			if (@ldap_bind($this->ldap_conn, $dn, $passwd)) {
				// User authenticated
				// Now get her info
				if ($this->ldap_kind=="AD"){
					$res = ldap_search ($this->ldap_conn, $this->base_dn, "sAMAccountName=".$loginname) ;
				} else {
					$res = ldap_read ($this->ldap_conn, $dn, "objectclass=*") ;
				}
				$info = ldap_get_entries ($this->ldap_conn,$res);
				$ldapentry = $info[0] ;
				
				$mappedinfo = plugin_ldapextauth_mapping ($ldapentry) ;
				
				// Insert into DB
				$u = new User () ;

				$unix_name = $loginname ;
				$firstname = '' ;
				$lastname = '' ;
				$password1 = $passwd ;
				$password2 = $passwd ;
				$email = '' ;
				$mail_site = 1 ;
				$mail_va = 0 ;
				$language_id = 1 ;
				$timezone = 'GMT' ;
				$jabber_address = '' ;
				$jabber_only = 0 ;
				$theme_id = 1 ;
				$unix_box = '' ;
				$address = '' ;
				$address2 = '' ;
				$phone = '' ;
				$fax = '' ;
				$title = '' ;
				$ccode = 'US' ;
				$send_mail = false ;

				if ($mappedinfo['firstname']) {
					$firstname = $mappedinfo['firstname'] ;
				}
				if ($mappedinfo['lastname']) {
					$lastname = $mappedinfo['lastname'] ;
				}
				if ($mappedinfo['email']) {
					$email = $mappedinfo['email'] ;
				}
				if ($mappedinfo['language_id']) {
					$language_id = $mappedinfo['language_id'] ;
				}
				if ($mappedinfo['timezone']) {
					$timezone = $mappedinfo['timezone'] ;
				}
				if ($mappedinfo['jabber_address']) {
					$jabber_address = $mappedinfo['jabber_address'] ;
				}
				if ($mappedinfo['address']) {
					$address = $mappedinfo['address'] ;
				}
				if ($mappedinfo['address2']) {
					$address2 = $mappedinfo['address2'] ;
				}
				if ($mappedinfo['phone']) {
					$phone = $mappedinfo['phone'] ;
				}
				if ($mappedinfo['fax']) {
					$fax = $mappedinfo['fax'] ;
				}
				if ($mappedinfo['title']) {
					$title = $mappedinfo['title'] ;
				}
				if ($mappedinfo['ccode']) {
					$ccode = $mappedinfo['ccode'] ;
				}
				if ($mappedinfo['themeid']) {
					$theme_id = $mappedinfo['themeid'] ;
				}

				if (!$u->create ($unix_name,$firstname,$lastname,$password1,$password2,$email,
					    $mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,
					    $unix_box, $address, $address2, $phone, $fax, $title, $ccode, $send_mail)) {
					$GLOBALS['ldap_auth_failed']=true;
					$feedback = "<br>Error Creating User: ".$u->getErrorMessage();
					return false;
				}

				if (!$u->setStatus ('A')) {
					$GLOBALS['ldap_auth_failed']=true;
					$feedback = "<br>Error Activating User: ".$u->getErrorMessage();
					return false;
				}
				$GLOBALS['ldap_auth_failed']=false;
				$GLOBALS['ldap_first_login']=true;
				return true ;
			} else {
				$GLOBALS['ldap_auth_failed']=true;
				$feedback=_('Invalid Password Or User Name');
				return false ; // Probably ignored, but just in case
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
