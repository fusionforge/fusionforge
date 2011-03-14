<?php
/** External authentication via LDAP for FusionForge
 * Copyright 2003, Roland Mas <lolando@debian.org>
 * Copyright 2004, Roland Mas <roland@gnurandal.com> 
 *                 The Gforge Group, LLC <http://gforgegroup.com/>
 * Copyright 2004, Christian Bayle <bayle@debian.org>
 * Copyright 2009-2010, Alain Peyrat, Alcatel-Lucent
 * Copyright 2009, Chris Dalzell, OpenGameForge.org
 * Copyright 2011, Roland Mas
 *
 * This file is part of FusionForge
 *
 * This plugin, like FusionForge, is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once $GLOBALS['gfcommon'].'include/User.class.php';
// require_once $GLOBALS['gfconfig'].'plugins/ldapextauth/mapping.php' ;

class LdapextauthPlugin extends ForgeAuthPlugin {
	protected $saved_login;
	protected $saved_password;
	protected $saved_user;
	protected $saved_data;

	function LdapextauthPlugin () {
		global $gfconfig;
		$this->ForgeAuthPlugin() ;
		$this->name = "ldapextauth";
		$this->text = "LDAP authentication";

		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("sync_account_info");
		$this->_addHook("close_auth_session");

		$this->cookie_name = 'forge_session_ldapextauth';

		$this->ldap_conn = false ;
		$this->saved_login = '';
		$this->saved_password = '';
		$this->saved_user = NULL;
		$this->saved_data = array();

		$this->declareConfigVars();
	}
	
	function syncAccountInfo($params) {
		if (!$this->syncDataOn($params['event'])) {
			return true;
		}
		$u = $params['user'];
		$data = $this->saved_data;

		if ($u) {
			if ($u->getStatus() == 'D') {
				$u->setStatus('A');
			}
			if (!session_login_valid_dbonly ($this->saved_login, $this->saved_password, false)) {
				$u->setPasswd ($passwd) ;
			}

		} else {
		}		
	}

	function displayAuthForm($params) {
		if (!$this->isRequired() && !$this->isSufficient()) {
			return true;
		}
		$return_to = $params['return_to'];
		$loginname = '';

		echo '<h2>'._('LDAP authentication').'</h2>';
		echo '<p>';
		echo _('Cookies must be enabled past this point.');
		echo '</p>';

		echo '<form action="' . util_make_url('/plugins/ldapextauth/post-login.php') . '" method="post">
<input type="hidden" name="form_key" value="' . form_generate_key() . '"/>
<input type="hidden" name="return_to" value="' . htmlspecialchars(stripslashes($return_to)) . '" />
<p>';
		echo _('LDAP Login name:');
		echo '<br /><input type="text" name="form_loginname" value="' . htmlspecialchars(stripslashes($loginname)) . '" /></p><p>' . _('Password:') . '<br /><input type="password" name="form_pw" /></p><p><input type="submit" name="login" value="' . _('Login') . '" />
</p>
</form>' ;
	}

	protected function declareConfigVars() {
		parent::declareConfigVars();

		forge_define_config_item ('start_tls', $this->name, 'no');
		forge_set_config_item_bool ('start_tls', $this->name) ;

		forge_define_config_item ('sync_data_as_user', $this->name, 'yes');
		forge_set_config_item_bool ('sync_data_as_user', $this->name) ;

		forge_define_config_item ('ldap_server', $this->name, 'ldap.example.com');
		forge_define_config_item ('ldap_port', $this->name, 389);
		forge_define_config_item ('base_dn', $this->name, 'ou=users,dc=example,dc=com');
		forge_define_config_item ('user_dn', $this->name, 'uid=');
		forge_define_config_item ('skipped_users', $this->name, '');
		forge_define_config_item ('manager_dn', $this->name, 'uid=');
		forge_define_config_item ('manager_password', $this->name, 'uid=');
	}

	/// HELPERS

	function checkLDAPCredentials($loginname, $passwd) {
		if (!$this->ldap_conn) {
			$r = $this->ConnectLdap(forge_get_config('ldap_server', $this->name), forge_get_config('ldap_port', $this->name));
			if (!$r) {
				// No connection to LDAP directory
				if ($this->isRequired()) {
					return FORGE_AUTH_AUTHORITATIVE_REJECT;
				} else {
					return FORGE_AUTH_NOT_AUTHORITATIVE;
				}
			}
		}

		$res = ldap_search($this->ldap_conn, forge_get_config('base_dn', $this->name), forge_get_config('user_dn', $this->name) . $loginname) ;
		if (!$res || ldap_count_entries($this->ldap_conn, $res) == 0) {
			// No user by that name in LDAP directory
			return FORGE_AUTH_AUTHORITATIVE_REJECT;
		}

		$info = ldap_get_entries ($this->ldap_conn,$res);
		$data = $info[0];
		if (@ldap_bind($this->ldap_conn, $data['dn'], $passwd)) {
			// OK
			$this->saved_data = $data;
			$this->saved_password = $passwd;
			return FORGE_AUTH_AUTHORITATIVE_ACCEPT;
		} else {
			// Probably invalid password
			return FORGE_AUTH_AUTHORITATIVE_REJECT;
		}
	}

	function ConnectLDAP($server, $port) {
		if ($port) {
			$this->ldap_conn = ldap_connect ($server, $port);
		} else {
			$this->ldap_conn = ldap_connect ($server);
		}

		if (forge_get_config('ldap_version')) {
			if (!ldap_set_option ($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, forge_get_config('ldap_version'))) {
				return false;
			}
		}

		if (forge_get_config('start_tls', $this->name)) {
			if (!ldap_start_tls($this->ldap_conn)) {
				error_log("LDAP start_tls failed: ".ldap_error($this->ldap_conn));
				return false;
			}
		}

		// If the ldap server does not allow anonymous bind,
		// then authentificate with the server.
		if ($this->ldap_bind_dn) {
			if (!@ldap_bind($this->ldap_conn, $this->ldap_bind_dn, $this->ldap_bind_pwd)) {
				error_log("LDAP application bind failed.");
				return false;
			}
		}

		return true;
	}

	/// LEGACY

	function AuthUser ($loginname, $passwd) {
		global $feedback;

		if  (!function_exists ( "ldap_connect" )) {
			error_log("No ldap_connect function, ldapextauth can't proceed.");
			return false;
		}

		if (!$this->ldap_conn) {
			$r = $this->ConnectLdap(forge_get_config('ldap_server', $this->name), forge_get_config('ldap_port', $this->name));
			if (!$r) {
				// Unable to connect to LDAP server(s), use internal login.
				$GLOBALS['ldap_auth_failed']=true;
				return true;
			}
		}

		// Search LDAP for user account.
		$res = ldap_search($this->ldap_conn, forge_get_config('base_dn', $this->name), forge_get_config('user_dn', $this->name) . $loginname) ;

		if (!$res) {
			// User not found in LDAP => Account invalid
			@ldap_unbind($this->ldap_conn);
			$feedback=_('Invalid Password Or User Name');
			$GLOBALS['ldap_auth_failed']=true;
			return false ;
		}

		$nb_result = ldap_count_entries($this->ldap_conn, $res);
		if ($nb_result!==1) {
			@ldap_unbind($this->ldap_conn);
			$GLOBALS['ldap_auth_failed']=true;
			return false ;
		}

		$info = ldap_get_entries ($this->ldap_conn,$res);
		$dn = $info[0]['dn'];

		$u = user_get_object_by_name ($loginname) ;

		if ($u) {
			// User exists in DB
			if (@ldap_bind($this->ldap_conn, $dn, $passwd)) {
				// Password from form is valid in LDAP

				// If account has been deleted but user is valid in LDAP,
				// then reactivate the account.
				if ($u->getStatus() == 'D') {
					$u->setStatus('A');
				}

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
				// If LDAP server is down, do not refuse the login
				// for this reason (let the DB check occur).
				// 0x51 is errno for LDAP_SERVER_DOWN
				if (ldap_errno($this->ldap_conn) == 0x51) {
					syslog(LOG_ERR, "FusionForge:LDAP server down, using DB login/passwd instead.");
					$GLOBALS['ldap_auth_failed']=true;
					return true;
				}
				// Wrong password according to LDAP
				$feedback=_('Invalid Password Or User Name');
				$GLOBALS['ldap_auth_failed']=true;
				return false ;
			}
		} else {
			// User doesn't exist in DB yet
			if (@ldap_bind($this->ldap_conn, $dn, $passwd))
			{

				$ldapentry = $info[0] ;

				$mappedinfo = plugin_ldapextauth_mapping ($ldapentry) ;
				
				// Insert into DB
				$u = new GFUser () ;

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

				if ($mappedinfo['unix_name']) {
					$unix_name = $mappedinfo['unix_name'] ;
				}
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
					$res = db_query_params('SELECT count(*) as c FROM country_code WHERE ccode=$1', array($mappedinfo['ccode']));
					if (db_result($res, 0, 'c') == 1) {
						$ccode = $mappedinfo['ccode'] ;
					}
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
