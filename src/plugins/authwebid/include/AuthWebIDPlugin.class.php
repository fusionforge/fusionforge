<?php
/** External authentication via WebID for FusionForge
 * Copyright 2011, Roland Mas
 * Copyright 2011, Olivier Berger & Institut Telecom
 *
 * This program was developped in the frame of the COCLICO project
 * (http://www.coclico-project.org/) with financial support of the Paris
 * Region council.
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

require_once $GLOBALS['gfcommon'].'include/User.class.php';

// WebID framework
require_once 'WebIDDelegatedAuth/lib/Authentication.php';

/**
 * WebID Authentication manager Plugin for FusionForge
 *
 */
class AuthWebIDPlugin extends ForgeAuthPlugin {
	
	var $delegatedAuthentifier;
	
	var $delegate_webid_auth_to;
	
	var $idp_delegation_link;

	var $webid_identity;

	function AuthWebIDPlugin () {
		global $gfconfig;
		$this->ForgeAuthPlugin() ;
		$this->name = "authwebid";
		$this->text = "WebID authentication";

		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("close_auth_session");
		$this->_addHook("usermenu") ;
		$this->_addHook("userisactivecheckbox") ; // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost") ; //

		$this->saved_login = '';
		$this->saved_user = NULL;

		$this->delegatedAuthentifier = FALSE;

		$this->webid_identity = FALSE;

		$this->declareConfigVars();
		
		// The IdP to use is configured in the .ini file
		$this->delegate_webid_auth_to = forge_get_config ('delegate_webid_auth_to', $this->name);
		$this->idp_delegation_link = forge_get_config('idp_delegation_link', $this->name);

	}

	/**
	 * Display a link redirecting to a WebID IdP, to test a delegated auth
	 * @param string $callback : callback which the IdP will invoke through with signed parameters
	 * @param string $message : alternative message for the link
	 * @return string html
	 */
	function displayAuthentifyViaIdPLink($callback, $message = FALSE) {
		if (!$message) {
			$message = sprintf( _('Click here to delegate authentication of your WebID to %s'), $this->delegate_webid_auth_to);
		} 
		$html = '<a href="' . $this->idp_delegation_link . '?authreqissuer='. $callback .'">';
		$html .=  $message .'</a>';
		return $html;
	}

	/**
	 * Display a form to redirect to the WebID IdP
	 * @param unknown_type $params
	 * @return boolean
	 */
	function displayAuthForm(&$params) {
		if (!$this->isRequired() && !$this->isSufficient()) {
			return true;
		}
		$return_to = $params['return_to'];

		$result = '';

		$result .= '<p>';
		$result .= _('Cookies must be enabled past this point.');
		$result .= '</p>';

		// TODO Use a trusted IdP that was configured previously by the forge admin, and which is trusted by the libAuthentication checks
		//$result .= '<a href="https://foafssl.org/srv/idp?authreqissuer='. util_make_url('/plugins/authwebid/post-login.php') .'">Click here to Login via foafssl.org</a>';
		//echo "<br />";
		$result .= '<b>'. $this->displayAuthentifyViaIdPLink( util_make_url('/plugins/authwebid/post-login.php') ) . '</b>';
		$result .= ' ('. _('You need to have bound such a WebID to your existing fusionforge account in advance') .')';

		$params['html_snippets'][$this->name] = $result;

	}

    /**
	 * Is there a valid session?
	 * @param unknown_type $params
	 */
	function checkAuthSession(&$params) {
		$this->saved_user = NULL;
		$user = NULL;

		if (isset($params['auth_token']) && $params['auth_token'] != '') {
			$user_id = $this->checkSessionToken($params['auth_token']);
		} else {
			$user_id = $this->checkSessionCookie();
		}
		if ($user_id) {
			$user = user_get_object($user_id);
		} else {
			if ($this->delegatedAuthentifier && $this->delegatedAuthentifier->identity) {
				$username = $this->getUserNameFromWebIDIdentity($this->delegatedAuthentifier->identity);
				if ($username) {
					$user = $this->startSession($username);
				}
			}
		}

		if ($user) {
			if ($this->isSufficient()) {
				$this->saved_user = $user;
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_ACCEPT;

			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		} else {
			if ($this->isRequired()) {
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_REJECT;
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		}
	}

	/**
	 * Retrieve the user_name for a WebID URI stored in DB as a known ID
	 * @param string $webid_identity
	 * @return string
	 */
	public function getUserNameFromWebIDIdentity($webid_identity) {
		$user_name = FALSE;
		$res = db_query_params('SELECT users.user_name FROM users, plugin_authwebid_user_identities WHERE users.user_id = plugin_authwebid_user_identities.user_id AND webid_identity=$1',
							    array($webid_identity));
		if($res) {
			$row = db_fetch_array_by_row($res, 0);
			if($row) {
				$user_name = $row['user_name'];
			}
		}
		return $user_name;
	}

	/**
	 * Check if a WebID is already used and bound to an account
	 * @param string $webid_identity
	 * @return boolean
	 */
	public function existStoredWebID($webid_identity) {
		$res = db_query_params('SELECT webid_identity FROM plugin_authwebid_user_identities WHERE webid_identity =$1',
				array($webid_identity));
		if ($res && db_numrows($res) > 0) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	 * Load WebIDs already bound to an account (not the pending ones)
	 * @param string $user_id
	 * @return array
	 */
	public function getStoredBoundWebIDs($user_id) {
		$boundwebids = array();
		$res = db_query_params('SELECT webid_identity FROM plugin_authwebid_user_identities WHERE user_id =$1',
				array($user_id));
		if($res) {
			$i = 0;
		
			while ($row = db_fetch_array($res)) {
				$webid_identity = 	$row['webid_identity'];
				// filter out the pending ones, prefixes by 'pending:'
				if (substr($webid_identity, 0, 8) != 'pending:') {
					$boundwebids[] = $webid_identity;
				}
			}
		}
		return $boundwebids;
	}
	
	/**
	 * Check if a WebID is pending confirmation of binding for a user
	 * @param string $user_id
	 * @param string $webid_identity
	 * @return boolean
	 */
	public function isStoredPendingWebID($user_id, $webid_identity) {
		// the pending WebIDs will be prefixed in the DB by 'pending:'
		$webid_identity = 'pending:' . $webid_identity;
		$res = db_query_params('SELECT COUNT(*) FROM plugin_authwebid_user_identities WHERE user_id =$1 AND webid_identity =$2',
				array ($user_id, $webid_identity));
		if ($res && db_numrows($res) > 0) {
			$arr = db_fetch_array($res);
			if ($arr[0] == '1') {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	/**
	 * Load WebIDs already stored, but pending confirmation by a user
	 * @param string $user_id
	 * @return array
	 */
	public function getStoredPendingWebIDs($user_id) {
		$pendingwebids = array();
		$res = db_query_params('SELECT webid_identity FROM plugin_authwebid_user_identities WHERE user_id =$1',
				array($user_id));
		if($res) {
			$i = 0;
		
			while ($row = db_fetch_array($res)) {
				$webid_identity = $row['webid_identity'];
				// return them as plain WebIDs without the 'pending:' prefix
				if (substr($webid_identity, 0, 8) == 'pending:') {
					$pendingwebids[] = substr($webid_identity, 8);
				}
			}
		}
		return $pendingwebids;
	}
	
	/**
	 * Convert a WebID pending binding to a bound one
	 * @param string $user_id
	 * @param string $webid_identity
	 * @return string
	 */
	public function bindStoredWebID($user_id, $webid_identity) {
		$error_msg = NULL;
		// remove the 'pending:' prefix
		$res = db_query_params('UPDATE plugin_authwebid_user_identities SET webid_identity=$1 WHERE user_id =$2 AND webid_identity =$3',
								array ($webid_identity, $user_id, 'pending:'.$webid_identity)) ;
		if (!$res) {
			$error_msg = sprintf(_('Cannot bind new identity: %s'), db_error());
		}
		return $error_msg;
	}
	
	/**
	 * Store a WebID as pending binding to an account
	 * @param string $user_id
	 * @param string $webid_identity
	 * @return string
	 */
	public function addStoredPendingWebID($user_id, $webid_identity) {
		$error_msg = NULL;
		// make sure not to add as pending to one account an already bound WebID for another
		if ($this->existStoredWebID($webid_identity)) {
			$error_msg = _('WebID already used');
		}
		else {
			// prefix it with the 'pending:' prefix
			$webid_identity = 'pending:' . $webid_identity;
			// make sure to not add the same pending WebID for two different accounts
			if ($this->existStoredWebID($webid_identity)) {
				$error_msg = _('WebID already pending binding');
			}
			$res = db_query_params('INSERT INTO plugin_authwebid_user_identities (user_id, webid_identity) VALUES ($1,$2)',
					array ($user_id, $webid_identity)) ;
			if (!$res || db_affected_rows($res) < 1) {
				$error_msg = sprintf(_('Cannot insert new identity: %s'), db_error());
			}
		}
		return $error_msg;
	}
	
	/**
	 * Remove a WebID (possibly pending) from the table
	 * @param string $user_id
	 * @param string $webid_identity
	 * @return string
	 */
	public function removeStoredWebID($user_id, $webid_identity) {
		$error_msg = NULL;
		$res = db_query_params('DELETE FROM plugin_authwebid_user_identities WHERE user_id=$1 AND webid_identity=$2',
								array($user_id, $webid_identity));
		if (!$res || db_affected_rows($res) < 1) {
			$error_msg = sprintf(_('Cannot delete identity: %s'), db_error());
		}
		return $error_msg;
	}
	
	/**
	 * Check if we just got invoked back as a callback by the IdP which validated a WebID
	 * @return boolean
	 */
	public function justBeenAuthenticatedByIdP() {
		
		// We should trust lib WebIDDelegatedAuth unless the admin wants to play by customizing by doing something like the commented code below
		/*
		// initialize the WebID lib handler which will read the posted args
		$IDPCertificates = array ( 'foafssl.org' =>
				"-----BEGIN PUBLIC KEY-----
				MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhFboiwS5HzsQAAerGOj8
				Zk6qvEf2QVarlm+c1fxd6f3OoQ9ezib1LjXitw+z2xcLG8lzaTmKOU0jw7KZp6WL
				W6gqhAWj2BQ1Lkl9R7aAUpA3ypk52gik8u/5JiWpTt1EV99DP5XNzzQ/QVjkvBlj
				rY+1ZeM+XtKzGfbK7eWh583xn3AE6maprXfLAo3BjUWJOQe0VHGYgrBVOcRQrSQ6
				34/f+jk22tmYZRzdTT/ZCadeLd7NryIeJbEu0W105JYvKodawSM3/zjt4fXFIPyB
				z8vHHmHRd2syDWqUy46YVQfqCfUBdXkHbvVQBtAfvRGUhYbFQm926an6z9uRE5LC
				aQIDAQAB
				-----END PUBLIC KEY-----
				");
				
		//$certRepository = new Authentication_X509CertRepo($IDPCertificates);
		*/
		
		// We don't rely on the PHP session, as we're in FusionForge
		$create_session = FALSE;
		//$this->delegatedAuthentifier = new Authentication_Delegated($create_session, NULL, NULL, $certRepository);
		$this->delegatedAuthentifier = new Authentication_Delegated($create_session);
		
		return $this->delegatedAuthentifier->isAuthenticated();
	}
	
	/**
	 * Return current WebID if the delegated Auth has proceeded
	 * @return string
	 */
	public function getCurrentWebID() {
		$webid = FALSE;
		if ($this->delegatedAuthentifier) {
			$webid = $this->delegatedAuthentifier->webid;
		}
		return $webid;
	}
	
	protected function declareConfigVars() {
		parent::declareConfigVars();

		// Change vs default
		forge_define_config_item ('required', $this->name, 'no');
		forge_set_config_item_bool ('required', $this->name) ;

		// Change vs default
		forge_define_config_item ('sufficient', $this->name, 'no');
		forge_set_config_item_bool ('sufficient', $this->name) ;
		
		// Default delegated WebID IdP to use
		forge_define_config_item ('delegate_webid_auth_to', $this->name, 'auth.my-profile.eu');
		
		//URL of the delegated auth on the IdP which accepts a ?authreqissuer=callback invocation 
		// for ex, for : https://auth.my-profile.eu/auth/?authreqissuer=http://fusionforge.example.com/callback.php :
		forge_define_config_item ('idp_delegation_link', $this->name, 'https://auth.my-profile.eu/auth/');
		
	}

	/**
	 * Displays link to WebID identities management tab in user's page ('usermenu' hook)
	 * @param unknown_type $params
	 */
	public function usermenu($params) {
		global $G_SESSION, $HTML;
		$text = $this->text; // this is what shows in the tab
		if ($G_SESSION->usesPlugin($this->name)) {
			//$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we�re calling is the user one
			echo $HTML->PrintSubMenu (array ($text), array ('/plugins/authwebid/index.php'), array(_('coin pan')));
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
