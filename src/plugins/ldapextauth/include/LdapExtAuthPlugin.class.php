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

class LdapextauthPlugin extends AuthPlugin {
	protected $saved_login;
	protected $saved_password;
	protected $saved_user;
	protected $saved_data;

	function LdapextauthPlugin () {
		global $gfconfig;
		$this->Plugin() ;
		$this->name = "ldapextauth";
		$this->text = "LDAP authentication";

		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("sync_account_info");
		$this->_addHook("close_auth_session");

		$this->cookie_name = 'forge_session_ldapextauth';
		
		$this->ldap_conn = false ;
		$this->base_dn = '';
		$this->user_dn = '';
		$this->ldap_server = forge_get_config('ldap_host') ;
		$this->ldap_port = forge_get_config('ldap_port') ;
		$this->ldap_altserver = '';
		$this->ldap_altport = '';
		$this->ldap_start_tls = false;
		$this->ldap_bind_dn = '';
		$this->ldap_bind_pwd = '';
		$this->ldap_skip_users = '';
		// require_once $GLOBALS['gfconfig'].'plugins/ldapextauth/config.php' ;
		if (isset($base_dn)) {
			$this->base_dn = $base_dn ;
		}
		if (isset($user_dn)) {
			$this->user_dn = $user_dn ;
		}
		if (isset($ldap_altserver)) {
			$this->ldap_altserver = $ldap_altserver ;
		}
		if (isset($ldap_altport)) {
			$this->ldap_altport = $ldap_altport ;
		}
		if (isset($ldap_start_tls)) {
			$this->ldap_start_tls = $ldap_start_tls ;
		}
		if (isset($ldap_bind_pwd)) {
			$this->ldap_bind_pwd = $ldap_bind_pwd;
		}
		if (isset($ldap_skip_users)) {
			// Array of login not managed by LDAP (local account).
			$this->ldap_skip_users = $ldap_skip_users ;
		}

		$this->saved_login = '';
		$this->saved_password = '';
		$this->saved_user = NULL;
		$this->saved_data = array();
	}
	
	function CallHook ($hookname, &$params) {
		switch ($hookname) {
		case "session_before_login":
			// Authenticate against LDAP
			debuglog("\nLDAP: CallHook ($hookname) - ". date("F j, Y, g:i a") );
			$loginname = $params['loginname'] ;
			$passwd = $params['passwd'] ;
			// Skip users from LDAP check (local account).
			if (in_array($loginname, $this->ldap_skip_users))
				return true;
			return $this->AuthUser ($loginname, $passwd) ;
			break;
		default:
			return parent::CallHook($hookname, $params);
		}
	}

	function syncAccountInfo($params) {
		if (!$this->syncDataOn($params['event'])) {
			return true;
		}
		$u = $params['user'];
		$u->setEmail('toto@tata.com');
		$data = $this->saved_data;

		if ($u) {
			if ($u->getStatus() == 'D') {
				debuglog("Account deleted, reactivating it.");
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

	/// HELPERS

	function checkLDAPCredentials($loginname, $passwd) {
		if (!$this->ldap_conn) {
			$r = $this->ConnectLdap($this->ldap_server, $this->ldap_port);
			if (!$r && $this->ldap_altserver) {
				ldap_close($this->ldap_conn);
				$r = $this->ConnectLdap($this->ldap_altserver, $this->ldap_altport);
			}
			if (!$r) {
				// No connection to LDAP directory
				return FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		}

		$res = ldap_search($this->ldap_conn, $this->base_dn, $this->user_dn . $loginname) ;
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

		debuglog("LDAP: ldap_connect($server,$port)");
		if ($port) {
			$this->ldap_conn = ldap_connect ($server, $port);
		} else {
			$this->ldap_conn = ldap_connect ($server);
		}
		debuglog("LDAP: Ldap handle: ".$this->ldap_conn);

		if (forge_get_config('ldap_version')) {
			debuglog("LDAP: ldap_set_option ($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, forge_get_config('ldap_version'));");
			if (!ldap_set_option ($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, forge_get_config('ldap_version'))) {
				debuglog("LDAP: ldap_set_option() failed: ".ldap_error($this->ldap_conn));
				return false;
			}
		}

		if ($this->ldap_start_tls) {
			debuglog("LDAP: ldap_start_tls($this->ldap_conn)");
			if (!ldap_start_tls($this->ldap_conn)) {
				syslog(LOG_ERR, "FusionForge: LDAP start_tls failed: ".ldap_error($this->ldap_conn));
				debuglog("LDAP: ldap_start_tls() failed: ".ldap_error($this->ldap_conn));
				return false;
			}
		}

		// If the ldap server does not allow anonymous bind,
		// then authentificate with the server.
		if ($this->ldap_bind_dn) {
			debuglog("LDAP: ldap_bind() (application bind)");
			if (!@ldap_bind($this->ldap_conn, $this->ldap_bind_dn, $this->ldap_bind_pwd)) {
				debuglog("LDAP: ldap_bind() failed (application bind): ". ldap_error($this->ldap_conn));
				syslog(LOG_ERR, "FusionForge:LDAP application bind failed, using DB login/passwd instead.");
				return false;
			}
		}

		return true;
	}

	/// LEGACY

	function AuthUser ($loginname, $passwd) {
		global $feedback;

		debuglog("LDAP: AuthUser ($loginname)");

		if  (!function_exists ( "ldap_connect" )) {
			debuglog("LDAP: No ldap_connect function, ldap is missing in your php.");
			return false;
		}

		if (!$this->ldap_conn) {
			$r = $this->ConnectLdap($this->ldap_server, $this->ldap_port);
			if (!$r && $this->ldap_altserver) {
				ldap_close($this->ldap_conn);
				$r = $this->ConnectLdap($this->ldap_altserver, $this->ldap_altport);
			}
			if (!$r) {
				// Unable to connect to LDAP server(s), use internal login.
				$GLOBALS['ldap_auth_failed']=true;
				return true;
			}
		}

		// Search LDAP for user account.
		debuglog("LDAP: Searching for $loginname");
		$res = ldap_search($this->ldap_conn, $this->base_dn, $this->user_dn . $loginname) ;
		debuglog("LDAP: ldap_search ($this->ldap_conn, $this->base_dn, $this->user_dn . $loginname)");
		debuglog("LDAP: Search handle is: $res");

		if (!$res) {
			// User not found in LDAP => Account invalid
			@ldap_unbind($this->ldap_conn);
			debuglog("LDAP: Wrong password according to LDAP ($loginname)");
			$feedback=_('Invalid Password Or User Name');
			$GLOBALS['ldap_auth_failed']=true;
			return false ;
		}

		$nb_result = ldap_count_entries($this->ldap_conn, $res);
		if ($nb_result!==1) {
			@ldap_unbind($this->ldap_conn);
		        debuglog("LDAP: ldap_count_entries() returned $nb_result values");
			$GLOBALS['ldap_auth_failed']=true;
			return false ;
		}

		$info = ldap_get_entries ($this->ldap_conn,$res);
		$dn = $info[0]['dn'];

		debuglog("LDAP: dn=$dn");

		$u = user_get_object_by_name ($loginname) ;

		if ($u) {
			debuglog("LDAP: User is present in database");

			// User exists in DB
			if (@ldap_bind($this->ldap_conn, $dn, $passwd)) {
				debuglog("LDAP: ldap_bind() ok (user bind)");
				// Password from form is valid in LDAP

				// If account has been deleted but user is valid in LDAP,
				// then reactivate the account.
				if ($u->getStatus() == 'D') {
					debuglog("Account deleted, reactivating it.");
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
				debuglog("LDAP: Wrong password according to LDAP ($loginname)");
				$feedback=_('Invalid Password Or User Name');
				$GLOBALS['ldap_auth_failed']=true;
				return false ;
			}
		} else {
			debuglog("LDAP: User is not present in database\n");

			// User doesn't exist in DB yet
			if (@ldap_bind($this->ldap_conn, $dn, $passwd))
			{

				$ldapentry = $info[0] ;

				debuglog("=> \$info => ".  var_export($info, true));
				debuglog("=> \$info[cn] => ".  $ldapentry['dn']);
				
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

				debuglog("creating account for $unix_name");
				if (!$u->create ($unix_name,$firstname,$lastname,$password1,$password2,$email,
					    $mail_site,$mail_va,$language_id,$timezone,$jabber_address,$jabber_only,$theme_id,
					    $unix_box, $address, $address2, $phone, $fax, $title, $ccode, $send_mail)) {
					$GLOBALS['ldap_auth_failed']=true;
					$feedback = "<br>Error Creating User: ".$u->getErrorMessage();
					return false;
				}

				debuglog("activating account for $unix_name");
				if (!$u->setStatus ('A')) {
					$GLOBALS['ldap_auth_failed']=true;
					debuglog("u->setStatus('A') failed: ".$u->getErrorMessage());
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

function debuglog($msg) {
	$fp = fopen("/tmp/ldap.log", "a+");
	fwrite ($fp, $msg."\n");
	fclose($fp);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
