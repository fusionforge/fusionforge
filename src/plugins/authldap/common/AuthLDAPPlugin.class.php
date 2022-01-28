<?php
/** External authentication via LDAP for FusionForge
 * Copyright 2003, Roland Mas <lolando@debian.org>
 * Copyright 2004, Roland Mas <roland@gnurandal.com>
 *                 The Gforge Group, LLC <http://gforgegroup.com/>
 * Copyright 2004, Christian Bayle <bayle@debian.org>
 * Copyright 2009-2010, Alain Peyrat, Alcatel-Lucent
 * Copyright 2009, Chris Dalzell, OpenGameForge.org
 * Copyright 2011, Roland Mas
 * Copyright 2014,2022, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'include/AuthPlugin.class.php';

class AuthLDAPPlugin extends ForgeAuthPlugin {
	protected $saved_login;
	protected $saved_password;
	protected $saved_data;

	function __construct() {
		global $gfconfig;
		parent::__construct();
		$this->name = "authldap";
		$this->text = _("LDAP authentication");
		$this->pkg_desc =
_("This plugin contains an LDAP authentication mechanism for
FusionForge. It allows users to authenticate against an external LDAP
directory, and syncs some of their personal information from LDAP
into the FusionForge database.");
		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("sync_account_info");
		$this->_addHook("close_auth_session");
		$this->_addHook("refresh_auth_session");
		$this->_addHook('session_login_valid');

		$this->ldap_conn = false;
		$this->saved_login = '';
		$this->saved_password = '';
		$this->saved_data = array();

		$this->declareConfigVars();
	}

	function syncAccountInfo($params) {
		if (!$this->syncDataOn($params['event'])) {
			return true;
		}
		$n = $params['username'];
		$data = $this->saved_data;

		if (!$data) {
			$data = $this->fetchDataForUser($n);
		}

		if (!$data) {
			error_log("No data to sync from LDAP for username ".$n);
			return true;
		}

		$u = user_get_object_by_name($n);

		if (!$u) {
			// No user by that name yet, let's create it

			$u = new FFUser();

			$user_data = array();

			$user_data['unix_name'] = $n;
			$user_data['firstname'] = '';
			$user_data['lastname'] = '';
			if ($this->saved_password == '') {
				$user_data['password1'] = '_INVALID_';
			} else {
				$user_data['password1'] = $this->saved_password;
			}
			$user_data['password2'] = $user_data['password1'];
			$user_data['email'] = '';
			$user_data['mail_site'] = 1;
			$user_data['mail_va'] = 0;
			$user_data['language_id'] = 1;
			$user_data['timezone'] = 'GMT';
			$user_data['theme_id'] = getThemeIdFromName(forge_get_config('default_theme'));
			$user_data['unix_box'] = '';
			$user_data['address'] = '';
			$user_data['address2'] = '';
			$user_data['phone'] = '';
			$user_data['fax'] = '';
			$user_data['title'] = '';
			$user_data['ccode'] = 'US';
			$send_mail = false;

			foreach (explode(',', forge_get_config('mapping', $this->name))
				 as $map_entry) {
				list ($fffield, $ldapfield) = explode('=',$map_entry);
				if (array_key_exists($ldapfield, $data)) {
					$user_data[$fffield] = $data[$ldapfield][0];
				}
			}

			if (!$u->create ($user_data['unix_name'],
					 $user_data['firstname'],
					 $user_data['lastname'],
					 $user_data['password1'],
					 $user_data['password2'],
					 trim($user_data['email']),
					 $user_data['mail_site'],
					 $user_data['mail_va'],
					 $user_data['language_id'],
					 $user_data['timezone'],
					 $user_data['theme_id'],
					 $user_data['unix_box'],
					 $user_data['address'],
					 $user_data['address2'],
					 $user_data['phone'],
					 $user_data['fax'],
					 $user_data['title'],
					 $user_data['ccode'],
					 $send_mail)) {
				error_log("LDAP: user::create() failed: ".$u->getErrorMessage());
				return false;
			}

			if (!$u->setStatus ('A')) {
				return false;
			}
		}

		if ($u->getStatus() == 'D') {
			$u->setStatus('A');
		}
		if ($this->saved_password != ''
		    && !session_check_credentials_in_database($this->saved_login, $this->saved_password, false)) {
			$u->setPasswd($this->saved_password);
		}

		$mapped_data = array(
			'username' => $u->getUnixName(),
			'unix_password' => '',
			'firstname' => $u->getFirstName(),
			'lastname' => $u->getLastName(),
			'email' => $u->getEmail(),
			'phone' => $u->getPhone()
			);

		foreach (explode(',', forge_get_config('mapping', $this->name))
			 as $map_entry) {
			list ($fffield, $ldapfield) = explode('=',$map_entry);
			if (array_key_exists($ldapfield, $data)) {
				$mapped_data[$fffield] = $data[$ldapfield][0];
			}
		}

		$u->update($mapped_data['firstname'],
			   $mapped_data['lastname'],
			   $u->getLanguage(),
			   $u->getTimeZone(),
			   $u->getMailingsPrefs('site'),
			   $u->getMailingsPrefs('va'),
			   $u->usesRatings(),
			   $u->getThemeID(),
			   $u->getAddress(),
			   $u->getAddress2(),
			   $mapped_data['phone'],
			   $u->getFax(),
			   $u->getTitle(),
			   $u->getCountryCode(),
			   $u->usesTooltips(),
			   trim($mapped_data['email']));

		if ((substr($mapped_data['unix_password'], 0, 7) == '{crypt}')
			|| substr($mapped_data['unix_password'], 0, 7) == '{CRYPT}') {
			$mapped_data['unix_password'] = substr($mapped_data['unix_password'],7);
		}
		$u->setUnixPasswd ($mapped_data['unix_password']);
	}

	function displayAuthForm(&$params) {
		global $HTML;
		if (!$this->isRequired() && !$this->isSufficient()) {
			return true;
		}
		$return_to = $params['return_to'];
		$loginname = '';

		$result = '';

		$result .= html_e('p', array(), _('Cookies must be enabled past this point.'));
		$result .= $HTML->openForm(array('action' => '/plugins/'.$this->name.'/post-login.php', 'method' => 'post'));
		$result .= html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
		$result .= html_e('input', array('type' => 'hidden', 'name' => 'return_to', 'value' => $return_to));
		$result .= html_e('p', array(), _('Login Name')._(':').
						html_e('br').html_e('input', array('type' => 'text', 'name' => 'form_loginname', 'value' => htmlspecialchars(stripslashes($loginname)), 'required' => 'required')));
		$result .= html_e('p', array(), _('Password')._(':').
						html_e('br').html_e('input', array('type' => 'password', 'name' => 'form_pw', 'required' => 'required')));
		$result .= html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'login', 'value' => _('Login'))), false);
		$result .= $HTML->closeForm();

		$params['html_snippets'][$this->name] = $result;
	}

	protected function declareConfigVars() {
		parent::declareConfigVars();

		forge_define_config_item('start_tls', $this->name, 'no');
		forge_set_config_item_bool('start_tls', $this->name);

		forge_define_config_item('ldap_server', $this->name, 'ldap.example.com');
		forge_define_config_item('ldap_port', $this->name, 389);
		forge_define_config_item('base_dn', $this->name, 'ou=users,dc=example,dc=com');
		forge_define_config_item('ldap_version', $this->name, 3);
		forge_define_config_item('manager_dn', $this->name, '');
		forge_define_config_item('manager_password', $this->name, '');
		forge_define_config_item('use_x_forward_user', $this->name, false);
	}

	/// HELPERS

	function fetchDataForUser($loginname) {
		if (!$this->ConnectLdap()) {
			return false;
		}

		if (forge_get_config('manager_dn', $this->name)) {
			ldap_bind($this->ldap_conn,
				   forge_get_config('manager_dn', $this->name),
				   forge_get_config('manager_password', $this->name));
		} else {
			ldap_bind($this->ldap_conn);
		}

		$fieldname = 'uid';
		foreach (explode(',', forge_get_config('mapping', $this->name))
			 as $map_entry) {
			list ($fffield, $ldapfield) = explode('=',$map_entry);
			if ($fffield == 'username') {
				$fieldname = $ldapfield;
			}
		}

		$res = ldap_search($this->ldap_conn, forge_get_config('base_dn', $this->name), "($fieldname=$loginname)");
		if (!$res || ldap_count_entries($this->ldap_conn, $res) == 0) {
			// No user by that name in LDAP directory
			return false;
		}
		$info = ldap_get_entries($this->ldap_conn,$res);
		$data = $info[0];
		return $data;
	}

	function session_login_valid($params) {
		$params['results'][] = $this->checkLDAPCredentials($params['loginname'], $params['passwd']);
		return true;
	}

	function checkLDAPCredentials($loginname, $passwd) {
		if (!$this->ConnectLdap()) {
			// No connection to LDAP directory
			if ($this->isRequired()) {
				return FORGE_AUTH_AUTHORITATIVE_REJECT;
			} else {
				return FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		}

		$data = $this->fetchDataForUser($loginname);
		if (!$data) {
			return FORGE_AUTH_AUTHORITATIVE_REJECT;
		}

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

	function ConnectLdap() {
		if ($this->ldap_conn) {
			return true;
		}

		$server = forge_get_config('ldap_server', $this->name);
		$port = forge_get_config('ldap_port', $this->name);
		if ($port) {
			$conn = ldap_connect($server, $port);
		} else {
			$conn = ldap_connect($server);
		}

		if (forge_get_config('ldap_version', $this->name)) {
			if (!ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, forge_get_config('ldap_version', $this->name))) {
				error_log("LDAP: ldap_set_option() failed: ".ldap_error($this->ldap_conn));
				return false;
			}
		}

		if (forge_get_config('ldap_opt_referrals', $this->name) != NULL) {
			if (!ldap_set_option($conn, LDAP_OPT_REFERRALS, forge_get_config('ldap_opt_referrals', $this->name))) {
				error_log("LDAP: ldap_set_option() failed: ".ldap_error($this->ldap_conn));
				return false;
			}
		}

		if (forge_get_config('start_tls', $this->name)) {
			if (!ldap_start_tls($conn)) {
				return false;
			}
		}

		// If the ldap server does not allow anonymous bind,
		// then authentificate with the server.
		if (forge_get_config('manager_dn', $this->name)) {
			if (!@ldap_bind($conn, forge_get_config('manager_dn', $this->name),
					forge_get_config('manager_password', $this->name))) {
				error_log("LDAP application bind failed.");
				return false;
			}
		}

		$this->ldap_conn = $conn;
		return true;
	}

	/**
	 * Is there a valid session?
	 *
	 * @param	array	$params
	 * @return	FORGE_AUTH_AUTHORITATIVE_ACCEPT, FORGE_AUTH_AUTHORITATIVE_REJECT or FORGE_AUTH_NOT_AUTHORITATIVE
	 * TODO : document 'auth_token' param
	 */
	function checkAuthSession(&$params) {
		// check the session cookie/token to get a user_id
		if (isset($params['auth_token']) && $params['auth_token'] != '') {
			$user_id = $this->checkSessionToken($params['auth_token']);
			//WARNING: I HOPE YOU KNOW WHAT YOU ARE DOING WHEN USING THIS OPTION!
		} elseif (forge_get_config('use_x_forward_user', $this->name)) {
			$username = $_SERVER['HTTP_X_FORWARDED_USER'];
			$userObject = user_get_object_by_name($username);
			if ($userObject && is_object($userObject)) {
				$user_id = $userObject->getID();
			} else {
				$user_id = false;
			}
			if (!$user_id) {
				$params['username'] = $username;
				$params['event'] = forge_get_config('sync_data_on', $this->name);
				$this->syncAccountInfo($params);
				$userObject = user_get_object_by_name($username);
				if ($userObject && is_object($userObject)) {
					$user_id = $userObject->getID();
				} else {
					$user_id = false;
				}
			}
			if ($user_id) {
				$this->saved_user = $userObject;
				$cookie_user_id = $this->checkSessionCookie();
				if ($cookie_user_id != $user_id) {
					$this->setSessionCookie();
				}
			}
		} else {
			$user_id = $this->checkSessionCookie();
		}
		$this->saved_user = $user_id ? user_get_object($user_id) : NULL;
		$this->setAuthStateResult($params, $user_id);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
