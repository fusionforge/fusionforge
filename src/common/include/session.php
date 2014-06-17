<?php
/**
 * FusionForge session management
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2001-2002, 2009, Roland Mas
 * Copyright 2004-2005, GForge, LLC
 * Copyright 2013, Franck Villaume - TrivialDev
 * Copyright © 2013
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
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

require_once $gfcommon.'include/account.php';
require_once $gfcommon.'include/utils.php';
require_once $gfcommon.'include/escapingUtils.php';

/**
 * A User object if user is logged in
 *
 * @var	constant		$G_SESSION
 */
$G_SESSION = false;

/**
 * Get session id
 */
$session_ser = '';

/**
 *	session_build_session_token() - Construct session token for the user
 *
 *	@param		int		$user_id	User_id of the logged in user
 *	@return string token value
 */
function session_build_session_token($user_id) {
	if (!$user_id) {
		return '';
	}
	return session_build_session_cookie($user_id);
}

function session_build_session_cookie($user_id) {
	$nonce = md5(util_randbytes());
	if (strlen(forge_get_config('session_key')) < 4) {
		exit_error('ATTN sysadmin: upgrade your session_key; hint: locate secrets.inc');
	}
	$session_cookie_data = array(
		$user_id,
		getStringFromServer('REMOTE_ADDR'),
		$nonce,
		getStringFromServer('HTTP_USER_AGENT')
	    );
	$session_cookie = "" . time();
	foreach ($session_cookie_data as $s) {
		/* for escaping; this is not really HTML */
		$session_cookie .= '<' . util_html_encode($s);
	}
	$session_cookie_hmac = hash_hmac("sha256", $session_cookie,
	    forge_get_config('session_key'), true);
	$session_serial_cookie = base64_encode($session_cookie) . '!' .
	    base64_encode($session_cookie_hmac);
	return $session_serial_cookie;
}

/**
 *	session_get_hash_from_token() - Get hash of session token
 *
 *	This hash can be used as a key to identify session, e.g. in DB.
 *
 *	@param		string	$session_token	Value of the session token
 *	@return string	hash
 */
function session_get_hash_from_token($session_token) {
	return session_get_session_cookie_hash($session_token);
}
function session_get_session_cookie_hash($session_cookie) {
	/*
	 * we cannot just use the HMAC as that may be longer than
	 * the database fields, and this code used to return a
	 * string of the size of an md5(), so just md5 it
	 */
	return md5($session_cookie);
}

/**
 *	session_check_session_token() - Check that session token passed from user is ok
 *
 *	@param		string	$session_token	Value of the session token
 *	@return user_id if token is ok, false otherwise
 */
function session_check_session_token($session_token) {
	if ($session_token == '') {
		return false;
	}
	return session_check_session_cookie($session_token);
}
function session_check_session_cookie($session_cookie) {
	if (!preg_match('#^[A-Za-z0-9+/=]*![A-Za-z0-9+/=]*$#',
	    $session_cookie)) {
		/*
		 * does not match basic format, off; recommended by
		 * http://www.daemonology.net/blog/2009-06-11-cryptographic-right-answers.html
		 * to protect the below code from malformed strings
		 */
		return false;
	}

	list($session_cookie, $session_cookie_hmac) = explode('!',
	    $session_cookie);
	$session_cookie = base64_decode($session_cookie);
	$session_cookie_hmac = base64_decode($session_cookie_hmac);
	if (hash_hmac("sha256", $session_cookie,
	    forge_get_config('session_key'), true) !== $session_cookie_hmac) {
		/* HMAC mismatch */
		return false;
	}

	list($time, $user_id, $ip, $nonce, $user_agent) = explode('<', $session_cookie);
	$user_id = util_unconvert_htmlspecialchars($user_id);
	$ip = util_unconvert_htmlspecialchars($ip);
	$user_agent = util_unconvert_htmlspecialchars($user_agent);

	if (!session_check_ip($ip, getStringFromServer('REMOTE_ADDR'))) {
		return false;
	}
	if (trim($user_agent) != getStringFromServer('HTTP_USER_AGENT')) {
		return false;
	}
	if ((forge_get_config('session_expire') > 0) &&
	    ($time - time() >= forge_get_config('session_expire'))) {
		return false;
	}

	return $user_id;
}

/**
 *	session_logout() - Log the user off the system.
 *
 *	This function destroys object associated with the current session,
 *	making user "logged out".  Deletes both user and session cookies.
 *
 * @return	bool	true/false
 *
 */
function session_logout() {
	plugin_hook('close_auth_session');

	// delete both session and username cookies
	// NB: cookies must be deleted with the same scope parameters they were set with
	//
	session_cookie('session_ser', '');

	RBACEngine::getInstance()->invalidateRoleCaches();
	return true;
}

/**
 *	session_login_valid() - Log the user to the system.
 *
 *	High-level function for user login. Check credentials, and if they
 *	are valid, open new session.
 *
 *	@param	string		$loginname    User name
 *	@param	string		$passwd       User password (in clear text)
 *	@param	bool|int	$allowpending Allow login to non-confirmed user account (only for confirmation of the very account)
 *	@return	bool		true/false, if false reason is in global $feedback
 *
 */
function session_login_valid($loginname, $passwd, $allowpending = 0) {
	global $feedback, $error_msg, $warning_msg;

	if (!$loginname || !$passwd) {
		$warning_msg = _('Missing Password Or User Name');
		return false;
	}

	$hook_params = array();
	$hook_params['loginname'] = $loginname;
	$hook_params['passwd'] = $passwd;
	$result = plugin_hook("session_before_login", $hook_params);

	// Refuse login if not all the plugins are ok.
	if (!$result) {
		if (!util_ifsetor($feedback)) {
			$warning_msg = _('Invalid Password Or User Name');
		}
		return false;
	}

	return session_login_valid_dbonly($loginname, $passwd, $allowpending);
}

function session_check_credentials_in_database($loginname, $passwd, $allowpending=false) {
	return session_login_valid_dbonly($loginname, $passwd, $allowpending);
}
function session_login_valid_dbonly($loginname, $passwd, $allowpending) {
	global $feedback, $userstatus;

	// Try to get the users from the database using user_id and (MD5) user_pw
	if (forge_get_config('require_unique_email')) {
		$res = db_query_params ('SELECT user_id,status,unix_pw FROM users WHERE (user_name=$1 OR email=$1) AND user_pw=$2',
					array ($loginname,
					       md5($passwd))) ;
	} else {
		$res = db_query_params ('SELECT user_id,status,unix_pw FROM users WHERE user_name=$1 AND user_pw=$2',
					array ($loginname,
					       md5($passwd))) ;
	}
	if (!$res || db_numrows($res) < 1) {
		// No user whose MD5 passwd matches the MD5 of the provided passwd
		// Selecting by user_name/email only
		if (forge_get_config('require_unique_email')) {
			$res = db_query_params ('SELECT user_id,status,unix_pw FROM users WHERE user_name=$1 OR email=$1',
						array ($loginname)) ;
		} else {
			$res = db_query_params ('SELECT user_id,status,unix_pw FROM users WHERE user_name=$1',
						array ($loginname)) ;
		}
		if (!$res || db_numrows($res) < 1) {
			// No user by that name
			$warning_msg = _('Invalid Password Or User Name');
			return false;
		} else {
			// There is a user with the provided user_name/email, but the MD5 passwds do not match
			// We'll have to try checking the (crypt) unix_pw
			$usr = db_fetch_array($res);
			$userstatus = $usr['status'] ;

			if (crypt ($passwd, $usr['unix_pw']) != $usr['unix_pw']) {
				// Even the (crypt) unix_pw does not patch
				// This one has clearly typed a bad passwd
				$warning_msg = _('Invalid Password Or User Name');
				return false;
			}
			// User exists, (crypt) unix_pw matches
			// Update the (MD5) user_pw and retry authentication
			// It should work, except for status errors
			$res = db_query_params ('UPDATE users SET user_pw=$1 WHERE user_id=$2',
						array (md5($passwd),
						       $usr['user_id'])) ;
			return session_check_credentials_in_database($loginname, $passwd, $allowpending) ;
		}
	} else {
		// If we're here, then the user has typed a password matching the (MD5) user_pw
		// Let's check whether it also matches the (crypt) unix_pw
		$usr = db_fetch_array($res);

		if (crypt ($passwd, $usr['unix_pw']) != $usr['unix_pw']) {
			// The (crypt) unix_pw does not match
			if ($usr['unix_pw'] == '') {
				// Empty unix_pw, we'll take the MD5 as authoritative
				// Update the (crypt) unix_pw and retry authentication
				// It should work, except for status errors
				$res = db_query_params ('UPDATE users SET unix_pw=$1 WHERE user_id=$2',
							array (account_genunixpw($passwd),
							       $usr['user_id'])) ;
				return session_check_credentials_in_database($loginname, $passwd, $allowpending) ;
			} else {
				// Invalidate (MD5) user_pw, refuse authentication
				$res = db_query_params ('UPDATE users SET user_pw=$1 WHERE user_id=$2',
							array ('OUT OF DATE',
							       $usr['user_id'])) ;
				$warning_msg =_('Invalid Password Or User Name');
				return false;
			}
		}

		// Yay.  The provided password matches both fields in the database.
		// Let's check the status of this user

		// if allowpending (for verify.php) then allow
		$userstatus = $usr['status'];
		if ($allowpending && ($usr['status'] == 'P')) {
			//1;
		} else {
			if ($usr['status'] == 'S') {
				//acount suspended
				$feedback = _('Account Suspended');
				return false;
			}
			if ($usr['status'] == 'P') {
				//account pending
				$feedback = _('Account Pending');
				return false;
			}
			if ($usr['status'] == 'D') {
				//account deleted
				$feedback = _('Account Deleted');
				return false;
			}
			if ($usr['status'] != 'A') {
				//unacceptable account flag
				$feedback = _('Account Not Active');
				return false;
			}
		}
		// create a new session
		session_set_new(db_result($res, 0, 'user_id'));

		return true;
	}
}

/**
 *	session_check_ip() - Check 2 IP addresses for match
 *
 *	This function checks that IP addresses match
 *
 *      IPv4 addresses are allowed to match with some
 *	fuzz factor (within 255.255.0.0 subnet).
 *
 *      For IPv6 addresses, no fuzz is needed since there's
 *      usually no NAT in IPv6.
 *
 *	@param	string	$oldip	The old IP address
 *	@param	string	$newip	The new IP address
 *	@return	bool	true/false
 *	@access private
 */
function session_check_ip($oldip, $newip) {
	if (strstr($oldip, ':')) {
		// Old IP is IPv6
		if (strstr($newip, ':')) {
			// New IP is IPv6 too
			return ($oldip == $newip);
		}
		return false;
	}
	// Old IP is IPv4
	if (strstr($newip, ':')) {
		// New IP is IPv6
		return false;
	}
	$eoldip = explode(".", $oldip);
	$enewip = explode(".", $newip);

	// require same Class B subnet
	return (($eoldip[0] == $enewip[0]) && ($eoldip[1] == $enewip[1]));
}

/**
 *	session_issecure() - Check if current session is secure
 *
 *	@return bool
 */
function session_issecure() {
	return (strtoupper(getStringFromServer('HTTPS')) == "ON");
}

/**
 *	session_set_cookie() - Set a session cookie
 *
 *	Set a cookie with default temporal scope of the current browser session
 *	and URL space of the current webserver
 *
 *	@param	string	$name		Name of cookie
 *	@param	string	$value		Value of cookie
 *	@param	string	$domain		Domain scope (default '')
 *	@param	int		$expiration	Expiration time in UNIX seconds (default 0)
 */
function session_set_cookie($name, $value, $domain='', $expiration=0) {
	session_cookie($name, $value, $domain, $expiration);
}
function session_cookie($name, $value, $domain='', $expiration=0) {
	if (php_sapi_name() == 'cli') {
		return;
	}
	if ($expiration) {
		$expiration = time() + $expiration;
	}
	/* evolvis: force secure (SSL-only) session cookies */
	//$force_secure = true;
	/* not (yet?) in FusionForge */
	$force_secure = false;
	if ($force_secure && !session_issecure()) {
		return;
	}
	if (PHP_MAJOR_VERSION < 5 || PHP_MINOR_VERSION < 2) {
		// In PHP < 5.2, setcookie accepts at most 6 parameters
		setcookie($name, $value, $expiration, '/', $domain, $force_secure);
	} else {
		setcookie($name, $value, $expiration, '/', $domain, $force_secure, true);
	}
}

/**
 *	session_redirect_uri() - Redirect browser
 *
 *	@param		string	Absolute URI
 *	@return never returns
 */
function session_redirect_uri($loc, $permanent=true) {
	util_save_messages();
	if ($permanent)
		sysdebug_off("Status: 301 Moved Permanently", true, 301);
	else
		sysdebug_off("Status: 303 See Other", true, 303);
	header("Location: ${loc}", true);
	header("Content-type: text/html");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"' .
	    ' "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n" .
	    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head>' .
	    "\n <title>Redirection</title>\n</head><body>\n" .
	    "<p>Please go to " . html_e('a', array(
		'href' => $loc,
	    ), util_html_encode($loc)) . " instead!</p>\n</body></html>\n";
	exit;
}

/**
 * session_redirect() - Redirect browser within the site and exit.
 *
 * @param  string $loc    Absolute path within the site
 */
function session_redirect($loc, $permanent=true) {
	session_redirect_uri(util_make_url($loc), $permanent);
	exit;
}

/**
 *	session_redirect_external() - Redirect browser to a (potentially external) URL
 *
 *	@param		string	Absolute URL, not necessarily within the site
 *	@return never returns
 */
function session_redirect_external($url) {
	util_save_messages();
	session_redirect_uri($url, false);
}

/**
 *	session_redirect404() - Redirect browser to 404 error page
 *
 *	@return never returns
 */
function session_redirect404() {
	global $HTML, $gfwww, $gfcommon;

	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	require_once $gfwww.'404.php';
	exit;
}

/**
 *	session_require() - DEPRECATED Convenience function to easily enforce permissions
 *
 *	Calling page will terminate with error message if current user
 *	fails checks.
 *
 * @deprecated
 *
 *	@param array         $req   Associative array specifying criteria
 *	@param string        $reason
 *	@return does not return if check is failed
 */
function session_require($req, $reason = '') {
	if (!session_loggedin()) {
		exit_not_logged_in();
	}

	$user = user_get_object(user_getid());
	if (!$user->isActive()) {
		session_logout();
		exit_error(_('Your account is no longer active; you have been disconnected'), '');
	}

	if (!array_key_exists('group', $req)) {
		exit_permission_denied($reason, '');
	}

	$group = group_get_object($req['group']);
	if (!$group || !is_object($group)) {
		exit_no_group();
	} elseif ($group->isError()) {
		exit_error($reason ? $reason : $group->getErrorMessage(), '');
	}

	$perm =& $group->getPermission();
	if (!$perm || !is_object($perm) || $perm->isError()) {
		exit_permission_denied($reason, '');
	}

	if (isset($req['admin_flags']) && $req['admin_flags']) {
		if (!$perm->isAdmin()) {
			exit_permission_denied($reason, '');
		}
	} else {
		if (!$perm->isMember()) {
			exit_permission_denied($reason, '');
		}
	}
}

/**
 *	session_require_perm() - Convenience function to easily enforce permissions
 *
 *	Calling page will terminate with error message if current user
 *	fails checks.
 *
 */
function session_require_perm($section, $reference, $action = NULL, $reason = '') {
	if (!forge_check_perm($section, $reference, $action)) {
		exit_permission_denied($reason, $section);
	}
}

/**
 *	session_require_global_perm() - Convenience function to easily enforce permissions
 *
 *	Calling page will terminate with error message if current user
 *	fails checks.
 *
 */
function session_require_global_perm($section, $action=NULL, $reason='') {
	if (!forge_check_global_perm($section, $action)) {
		if (!$reason) {
			$reason = sprintf(_('Permission denied. The %s administrators will have to grant you permission to view this page.'),
			    forge_get_config('forge_name'));
		}
		exit_permission_denied($reason, $section);
	}
}

/**
 *	session_require_login() - Convenience function to easily enforce permissions
 *
 *	Calling page will terminate with error message if current user
 *	fails checks.
 *
 */
function session_require_login() {
	if (!session_loggedin()) {
		exit_not_logged_in();
	}
}

/**
 *	session_set_new() - Setup session for the given user
 *
 *	This function sets up SourceForge session for the given user,
 *	making one be "logged in".
 *
 *	@param	int	$user_id	The user ID
 */
function session_set_new($user_id) {
	$token = session_build_session_token($user_id);

	// set session cookie
	//
	$cookie = session_build_session_cookie($user_id);
//	session_cookie("session_ser", $cookie, "", forge_get_config('session_expire'));
//	$session_ser = $cookie;

	$res = db_query_params('SELECT count(*) as c FROM user_session
		WHERE session_hash=$1',
	    array(($shash = session_get_session_cookie_hash($cookie))));
	if (!$res || db_result($res, 0, 'c') < 1) {
		db_query_params('INSERT INTO user_session
			(session_hash,ip_addr,time,user_id)
			VALUES ($1,$2,$3,$4)',
		    array(
			$shash,
			getStringFromServer('REMOTE_ADDR'),
			time(),
			$user_id,
		    ));
	}

	// check uniqueness of the session_hash in the database
	$res = session_getdata($user_id);

	if (!$res) {
		exit_error(db_error(), '');
	} elseif (db_numrows($res) < 1) {
		exit_error(_('Could not fetch user session data'), '');
	} else {
		session_set_internal($user_id, $res);
	}
}

function session_set_internal($user_id, $res = false) {
	global $G_SESSION;

	$G_SESSION = user_get_object($user_id, $res);
	if ($G_SESSION) {
		$G_SESSION->setLoggedIn(true);
	}

	RBACEngine::getInstance()->invalidateRoleCaches();
}

/**
 *	session_set_admin() - Setup session for the admin user
 *
 *	This function sets up a session for the administrator
 */
function session_set_admin() {
	$admins = RBACEngine::getInstance()->getUsersByAllowedAction('forge_admin', -1);
	if (count($admins) == 0) {
		exit_error(_('No admin users ?'), '');
	}
	/*
	 * Use the user with the lowest numerical user ID.
	 * This is to prevent complaints from real humans
	 * if the system is doing something in their stead
	 * (for example by populate_template_project.php).
	 * Usually, “admin” has the ID 101.
	 */
	$admin_ids = array();
	foreach ($admins as $admin) {
		$admin_ids[] = $admin->getID();
	}
	sort($admin_ids);
	session_set_new($admin_ids[0]);
}

/**
 * Private optimization function for logins - fetches user data, language, and session
 * with one query
 *
 *	@param	int	$user_id	The user ID
 *	@access private
 *	@return resource
 */
function session_getdata($user_id) {
	return db_query_params('SELECT u.*, sl.language_id, sl.name,
		    sl.filename, sl.classname, sl.language_code,
		    t.dirname, t.fullname
		FROM users u, supported_languages sl, themes t
		WHERE u.language=sl.language_id
		    AND u.theme_id=t.theme_id
		    AND u.user_id=$1',
	    array($user_id));
}

/**
 *	session_set() - Re-initialize session for the logged in user
 *
 *	This function checks that the user is logged in and if so, initialize
 *	internal session environment.
 */
function session_set() {
	global $G_SESSION;
	global $session_ser;

	// assume bad session_hash and session. If all checks work, then allow
	// otherwise make new session
	$id_is_good = false;

	$params = array();
	// pass the session_ser from cookie to the auth plugins
	// (see AuthBuiltinPlugin::checkAuthSession() or likes)
	// expect FORGE_AUTH_AUTHORITATIVE_ACCEPT, FORGE_AUTH_AUTHORITATIVE_REJECT or FORGE_AUTH_NOT_AUTHORITATIVE
	// in results
	$params['auth_token'] = $session_ser;
	$params['results'] = array();
	plugin_hook_by_reference('check_auth_session', $params);

	$seen_yes = false;
	$seen_no = false;
	foreach ($params['results'] as $p => $r) {
		if ($r == FORGE_AUTH_AUTHORITATIVE_ACCEPT) {
			$seen_yes = true;
		} elseif ($r == FORGE_AUTH_AUTHORITATIVE_REJECT) {
			$seen_no = true;
		}
	}
	if ($seen_yes && !$seen_no) {
		// see AuthBuiltinPlugin::fetchAuthUser() or likes
		// expect user object in results
		$params = array();
		$params['results'] = NULL;
		plugin_hook_by_reference('fetch_authenticated_user', $params);
		$user = $params['results'];

		if ($user) {
			$params = array();
			$params['username'] = $user->getUnixName();
			$params['event'] = 'every-page';
			plugin_hook('sync_account_info', $params);

			$user->setLoggedIn(true);
			$G_SESSION = $user;
		} else {
			$G_SESSION=false;
		}
	}
	// TODO: else... what ?

	$re = RBACEngine::getInstance();
	$re->invalidateRoleCaches() ;
}

/**
 * Re initializes a session, trusting a non-sufficient plugin only temporarily
 *
 * The checkAuthSession of the Auth plugin will have to acknowledge the 'sufficient_forced' param in 'check_auth_session' hook
 * @param string $authpluginname
 */
function session_set_for_authplugin($authpluginname) {
	global $G_SESSION;
	global $session_ser;

	// assume bad session_hash and session. If all checks work, then allow
	// otherwise make new session
	$id_is_good = false;

	$params = array();
	// pass the session_ser from cookie to the auth plugins
	// (see AuthBuiltinPlugin::checkAuthSession() or likes)
	// expect FORGE_AUTH_AUTHORITATIVE_ACCEPT, FORGE_AUTH_AUTHORITATIVE_REJECT or FORGE_AUTH_NOT_AUTHORITATIVE
	// in results
	$params['sufficient_forced'] = $authpluginname;

	$params['auth_token'] = $session_ser;
	$params['results'] = array();

	plugin_hook_by_reference('check_auth_session', $params);

	$seen_yes = false;
	foreach ($params['results'] as $p => $r) {
		if ($r == FORGE_AUTH_AUTHORITATIVE_ACCEPT) {
			$seen_yes = true;
		}
	}

	if ($seen_yes) {
		//echo "user ok\n";
		// see AuthBuiltinPlugin::fetchAuthUser() or likes
		// expect user object in results
		$params = array();
		$params['results'] = NULL;

		plugin_hook_by_reference('fetch_authenticated_user', $params);

		$user = $params['results'];

		if ($user) {
			$params = array();
			$params['username'] = $user->getUnixName();
			$params['event'] = 'every-page';
			plugin_hook('sync_account_info', $params);

			$user->setLoggedIn(true);echo "user:".$user->getUnixName();
			$G_SESSION = $user;

		} else {
			$G_SESSION=false;
		}
	}
	// TODO: else... what ?

	$re = RBACEngine::getInstance();
	//print_r($re->getGlobalRoles());
	//print_r($re->getPublicRoles());
	$re->invalidateRoleCaches() ;
	//print_r($re->getAvailableRoles());
}

//TODO - this should be generalized and used for pre.php,
//SOAP, forum_gateway.php, tracker_gateway.php, etc to
//setup languages
function session_continue($sessionKey) {
	global $session_ser;
	$session_ser = $sessionKey;
	session_set();
	setup_gettext_from_context();
	setup_tz_from_context();
	$LUSER =& session_get_user();
	if (!is_object($LUSER) || $LUSER->isError()) {
		return false;
	}
	return true;
}

function setup_tz_from_context() {
	$user = session_get_user();
	if (!is_object($user) || $user->isError()) {
		$tz = forge_get_config('default_timezone');
	} else {
		$tz = $user->getTimeZone();
	}
	putenv('TZ='.$tz);
	date_default_timezone_set($tz);
}

/**
 *	session_get_user() - Wrapper function to return the User object for the logged in user.
 *
 *	@return User
 *	@access public
 */
function &session_get_user() {
	global $G_SESSION;
	return $G_SESSION;
}

/**
 *  user_getid()
 *  Get user_id of logged in user
 */
function user_getid() {
	global $G_SESSION;
	if ($G_SESSION) {
		return $G_SESSION->getID();
	} else {
		return false;
	}
}

/**
 *  session_loggedin()
 *  See if user is logged in
 */
function session_loggedin() {
	global $G_SESSION;

	if ($G_SESSION) {
		return $G_SESSION->isLoggedIn();
	} else {
		return false;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
