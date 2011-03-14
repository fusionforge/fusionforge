<?php
/**
 * FusionForge authentication management
 *
 * Copyright 2011, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

define(FORGE_AUTH_AUTHORITATIVE_ACCEPT, 1);
define(FORGE_AUTH_AUTHORITATIVE_REJECT, 2);
define(FORGE_AUTH_NOT_AUTHORITATIVE, 3);

abstract class AuthPlugin extends Plugin {
	/**
	 * AuthPlugin() - constructor
	 *
	 */
	function AuthPlugin() {
		$this->Plugin();
		// Common hooks that can be enabled per plugin:
		// check_auth_session - is there a valid session?
		// fetch_auth_info - what GFUser is logged in?
		// display_auth_form - display a form to input credentials
		// display_create_user_form - display a form to create a user from external auth
		// sync_account_info - sync identity from external source (realname, email, etc.)
		// get_extra_roles - add new roles not necessarily stored in the database
		// restrict_roles - filter out unwanted roles
		// close_auth_session - terminate an authentication session
	}

	// Hook dispatcher
	function CallHook ($hookname, &$params) {
		switch ($hookname) {
		case 'check_auth_session':
			$this->checkAuthSession($params);
			break;
		case 'fetch_authenticated_user':
			$this->fetchAuthUser($params);
			break;
		case 'display_auth_form':
			$this->displayAuthForm($params);
			break;
		case 'display_create_user_form':
			$this->displayCreateUserForm($params);
			break;
		case 'sync_account_info':
			$this->syncAccountInfo($params);
			break;
		case 'get_extra_roles':
			$this->getExtraRoles($params);
			break;
		case 'restrict_roles':
			$this->restrictRoles($params);
			break;
		case 'close_auth_session':
			$this->closeAuthSession($params);
			break;
		default:
			// Forgot something
		}
	}

	// Default mechanisms
	protected $saved_user;
	function checkAuthSession($params) {
		if (isset($params['auth_token']) && $params['auth_token'] != '') {
			$user = $this->checkSessionToken($params['auth_token']);
		} else {
			$user = $this->checkSessionCookie();
		}
		if ($user) {
			$this->saved_user = $user;
		} else {
			$this->saved_user = NULL;
		}
		if ($user) {
			$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_ACCEPT;
		} else {
			$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
		}
	}

	function fetchAuthUser($params) {
		$params['results'] = $this->saved_user;
	}

	function closeAuthSession($params) {
		$this->unsetSessionCookie();
	}

	// Helper functions for individual plugins
	protected $cookie_name = 'session_ser';

	protected function checkSessionToken($token) {
		return session_check_session_cookie($token);
	}

	protected function checkSessionCookie() {
		$token = getStringFromCookie($this->cookie_name);
		return $this->checkSessionToken($token);
	}

	protected function setSessionCookie() {
		$cookie = session_build_session_cookie($user_id);
		session_cookie($this->cookie_name, $cookie, "", forge_get_config('session_expire'));
	}

	protected function unsetSessionCookie() {
		session_cookie($this->cookie_name, '');
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
