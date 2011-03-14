<?php
/** External authentication via CAS for FusionForge
 * Copyright 2007, Benoit Lavenier <benoit.lavenier@ifremer.fr>
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
require_once 'CAS.php';

class AuthCASPlugin extends ForgeAuthPlugin {
	protected $saved_login;
	protected $saved_password;
	protected $saved_user;
	protected $saved_data;

	function AuthCASPlugin () {
		global $gfconfig;
		$this->ForgeAuthPlugin() ;
		$this->name = "authcas";
		$this->text = "CAS authentication";

		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("close_auth_session");

		$this->saved_login = '';
		$this->saved_user = NULL;

		$this->declareConfigVars();
	}

	private static $cas_client = false;
	private static $init = false;

	function initCAS() {
		if (self::$init) {
			return;
		}

		self::$cas_client = phpCAS::client(forge_get_config('cas_version', $this->name),
						   forge_get_config('cas_server', $this->name),
						   intval(forge_get_config('cas_port', $this->name)),
						   '');
		self::$init = true;
	}

	function displayAuthForm($params) {
		if (!$this->isRequired() && !$this->isSufficient()) {
			return true;
		}
		$return_to = $params['return_to'];

		$this->initCAS();

		echo '<h2>'._('CAS authentication').'</h2>';

		echo '<form action="' . util_make_url('/plugins/authcas/post-login.php') . '" method="get">
<input type="hidden" name="form_key" value="' . form_generate_key() . '"/>
<input type="hidden" name="return_to" value="' . htmlspecialchars(stripslashes($return_to)) . '" />
<p><input type="submit" name="login" value="' . _('Login via CAS') . '" />
</p>
</form>' ;
	}

	function checkAuthSession(&$params) {
		$this->initCAS();

		if (phpCAS::isAuthenticated()) {
			if ($this->isSufficient()) {
				$this->saved_user = user_get_object_by_name(phpCAS::getUser());
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_ACCEPT;
				
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		} else {
			$this->saved_user = NULL;
			if ($this->isRequired()) {
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_REJECT;
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		}
	}

	function fetchAuthUser(&$params) {
		if ($this->saved_user && $this->isSufficient()) {
			$params['results'] = $this->saved_user;
		}
	}

	function closeAuthSession($params) {
		$this->initCAS();

		if ($this->isSufficient() || $this->isRequired()) {
			phpCAS::logout(util_make_url('/'));
		} else {
			return true;
		}
	}
	protected function declareConfigVars() {
		parent::declareConfigVars();

		forge_define_config_item ('cas_server', $this->name, 'cas.example.com');
		forge_define_config_item ('cas_port', $this->name, 443);
		forge_define_config_item ('cas_version', $this->name, '2.0');
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
