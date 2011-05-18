<?php
/** External authentication via HTTPD for FusionForge
 * Copyright 2011, Roland Mas
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

/**
 * Authentication manager for FusionForge
 *
 */
class AuthHTTPDPlugin extends ForgeAuthPlugin {
	function AuthHTTPDPlugin () {
		global $gfconfig;
		$this->ForgeAuthPlugin() ;
		$this->name = "authhttpd";
		$this->text = "HTTPD authentication";

		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("close_auth_session");

		$this->saved_login = '';
		$this->saved_user = NULL;

		$this->declareConfigVars();
	}

	private static $init = false;

	/**
	 * Display a form to input credentials
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

		$result .= '<form action="' . util_make_url('/plugins/authhttpd/post-login.php') . '" method="get">
<input type="hidden" name="form_key" value="' . form_generate_key() . '"/>
<input type="hidden" name="return_to" value="' . htmlspecialchars(stripslashes($return_to)) . '" />
<p><input type="submit" name="login" value="' . _('Login via HTTP authentication') . '" />
</p>
</form>' ;
		
		$params['html_snippets'][$this->name] = $result;

		$params['transparent_redirect_urls'][$this->name] = util_make_url('/plugins/authhttpd/post-login.php?return_to='.htmlspecialchars(stripslashes($return_to)));
	}

	/**
	 * Is there a valid session?
	 * @param unknown_type $params
	 */
	function checkAuthSession(&$params) {
		$this->saved_user = NULL;
		$user = NULL;

		if (isset($GLOBALS['REMOTE_USER'])) {
			$username = $GLOBALS['REMOTE_USER'];
		} else {
			$username = NULL;
		}

		if ($username) {
			$user = user_get_object_by_name($username);
		}
		
		// TODO : shouldn't this part be factorized as it seems quite common for many plugins ?
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
	 * What GFUser is logged in?
	 * @param unknown_type $params
	 */
	function fetchAuthUser(&$params) {
		if ($this->saved_user && $this->isSufficient()) {
			$params['results'] = $this->saved_user;
		}
	}

	function closeAuthSession($params) {
		// No way to close an HTTPD session from the server, unfortunately
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
