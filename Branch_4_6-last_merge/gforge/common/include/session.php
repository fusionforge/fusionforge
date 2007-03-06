<?php
/**
 * SourceForge Session Module
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('common/include/account.php');
require_once('common/include/escapingUtils.php');

/**
 * A User object if user is logged in
 *
 * @var	constant		$G_SESSION
 */
$G_SESSION = false;

/**
 * Get session id
 */
$session_ser = getStringFromCookie('session_ser');

/**
 *	session_build_session_cookie() - Construct session cookie for the user
 *
 *	@param		int		User_id of the logged in user
 *	@return cookie value
 */
function session_build_session_cookie($user_id) {
	$session_serial = $user_id.'-*-'.time().'-*-'.getStringFromServer('REMOTE_ADDR').'-*-'.getStringFromServer('HTTP_USER_AGENT');
	$session_serial_hash = md5($session_serial.$GLOBALS['sys_session_key']);
	$session_serial_cookie = base64_encode($session_serial).'-*-'.$session_serial_hash;
	return $session_serial_cookie;
}

/**
 *	session_get_session_cookie_hash() - Get hash of session cookie
 *
 *	This hash can be used as a key to identify session, e.g. in DB.
 *
 *	@param		string	Value of the session cookie
 *	@return hash
 */
function session_get_session_cookie_hash($session_cookie) {
	list ($junk, $hash) = explode('-*-', $session_cookie);
	return $hash;
}

/**
 *	session_check_session_cookie() - Check that session cookie passed from user is ok
 *
 *	@param		string	Value of the session cookie
 *	@return user_id if cookie is ok, false otherwise
 */
function session_check_session_cookie($session_cookie) {

	list ($session_serial, $hash) = explode('-*-', $session_cookie);
	$session_serial = base64_decode($session_serial);
	$new_hash = md5($session_serial.$GLOBALS['sys_session_key']);

	if ($hash != $new_hash) {
		return false;
	}

	list($user_id, $time, $ip, $user_agent) = explode('-*-', $session_serial, 4);

	if (!session_check_ip($ip, getStringFromServer('REMOTE_ADDR'))) {
		return false;
	}
	if (trim($user_agent) != getStringFromServer('HTTP_USER_AGENT')) {
		return false;
	}
	if (($GLOBALS['sys_session_expire'] > 0) && 
	    ($time - time() >= $GLOBALS['sys_session_expire'])) {
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
 *	@return true/false
 *
 */
function session_logout() {

	// delete both session and username cookies
	// NB: cookies must be deleted with the same scope parameters they were set with
	//
	session_cookie('session_ser', '');
	return true;
}

/**
 *	session_login_valid() - Log the user to the system.
 *
 *	High-level function for user login. Check credentials, and if they
 *	are valid, open new session.
 *
 *	@param		string	User name
 *	@param		string	User password (in clear text)
 *	@param		bool	Allow login to non-confirmed user account (only for confirmation of the very account)
 *	@return true/false, if false reason is in global $feedback
 *	@access public
 *
 */
function session_login_valid($loginname, $passwd, $allowpending=0)  {
	global $feedback,$Language;

	if (!$loginname || !$passwd) {
		$feedback = $Language->getText('session','missingpasswd');
		return false;
	}

	$hook_params = array () ;
	$hook_params['loginname'] = $loginname ;
	$hook_params['passwd'] = $passwd ;
	plugin_hook ("session_before_login", $hook_params) ;

	return session_login_valid_dbonly ($loginname, $passwd, $allowpending) ;
}

function session_login_valid_dbonly ($loginname, $passwd, $allowpending) {
	global $feedback,$userstatus,$Language;

	//  Try to get the users from the database using user_id and (MD5) user_pw
	$res = db_query("
		SELECT user_id,status,unix_pw
		FROM users
		WHERE user_name='$loginname' 
		AND user_pw='".md5($passwd)."'
	");
	if (!$res || db_numrows($res) < 1) {
		// No user whose MD5 passwd matches the MD5 of the provided passwd
		// Selecting by user_name only
		$res = db_query("SELECT user_id,status,unix_pw
					FROM users
					WHERE user_name='$loginname'");
		if (!$res || db_numrows($res) < 1) {
			// No user by that name
			$feedback=$Language->getText('session','invalidpasswd');
			return false;
		} else {
			// There is a user with the provided user_name, but the MD5 passwds do not match
			// We'll have to try checking the (crypt) unix_pw
			$usr = db_fetch_array($res);

			if (crypt ($passwd, $usr['unix_pw']) != $usr['unix_pw']) {
				// Even the (crypt) unix_pw does not patch
				// This one has clearly typed a bad passwd
				$feedback=$Language->getText('session','invalidpasswd');
				return false;
			}
			// User exists, (crypt) unix_pw matches
			// Update the (MD5) user_pw and retry authentication
			// It should work, except for status errors
			$res = db_query ("UPDATE users
				SET user_pw='" . md5($passwd) . "'
				WHERE user_id='".$usr['user_id']."'");
			return session_login_valid_dbonly($loginname, $passwd, $allowpending) ;
		}
	} else {
		// If we're here, then the user has typed a password matching the (MD5) user_pw
		// Let's check whether it also matches the (crypt) unix_pw
		$usr = db_fetch_array($res);
/*
		if (crypt ($passwd, $usr['unix_pw']) != $usr['unix_pw']) {
			// The (crypt) unix_pw does not match
			if ($usr['unix_pw'] == '') {
				// Empty unix_pw, we'll take the MD5 as authoritative
				// Update the (crypt) unix_pw and retry authentication
				// It should work, except for status errors
				$res = db_query ("UPDATE users
					SET unix_pw='" . account_genunixpw($passwd) . "'
					WHERE user_id='".$usr['user_id']."'");
				return session_login_valid_dbonly($loginname, $passwd, $allowpending) ;
			} else {
				// Invalidate (MD5) user_pw, refuse authentication
				$res = db_query ("UPDATE users
					SET user_pw='OUT OF DATE'
					WHERE user_id='".$usr['user_id']."'");
				$feedback=$Language->getText('session','invalidpasswd');
				return false;
			}
		}
*/

		// Yay.  The provided password matches both fields in the database.
		// Let's check the status of this user

		// if allowpending (for verify.php) then allow
		$userstatus=$usr['status'];
		if ($allowpending && ($usr['status'] == 'P')) {
			//1;
		} else {
			if ($usr['status'] == 'S') { 
				//acount suspended
				$feedback = $Language->getText('session','suspended');
				return false;
			}
			if ($usr['status'] == 'P') { 
				//account pending
				$feedback = $Language->getText('session','pending');
				return false;
			} 
			if ($usr['status'] == 'D') { 
				//account deleted
				$feedback = $Language->getText('session','deleted');
				return false;
			}
			if ($usr['status'] != 'A') {
				//unacceptable account flag
				$feedback = $Language->getText('session','notactive');
				return false;
			}
		}
		//create a new session
		session_set_new(db_result($res,0,'user_id'));

		return true;
	}
}

/**
 *	session_check_ip() - Check 2 IP addresses for match
 *
 *	This function checks that IP addresses match with the
 *	given fuzz factor (within 255.255.0.0 subnet).
 *
 *	@param		string	The old IP address
 *	@param		string	The new IP address
 *	@return true/false
 *	@access private
 */
function session_check_ip($oldip,$newip) {
	$eoldip = explode(".",$oldip);
	$enewip = explode(".",$newip);

	// ## require same class b subnet
	if (($eoldip[0]!=$enewip[0])||($eoldip[1]!=$enewip[1])) {
		return 0;
	} else {
		return 1;
	}
}

/**
 *	session_issecure() - Check if current session is secure
 *
 *	@return true/false
 *	@access public
 */
function session_issecure() {
	return (strtoupper(getStringFromServer('HTTPS')) == "ON");
}

/**
 *	session_cookie() - Set a session cookie
 *
 *	Set a cookie with default temporal scope of the current browser session
 *	and URL space of the current webserver
 *
 *	@param		string	Name of cookie
 *	@param		string	Value of cookie
 *	@param		string	Domain scope (default '')
 *	@param		string	Expiration time in UNIX seconds (default 0)
 *	@return true/false
 */
function session_cookie($name ,$value, $domain = '', $expiration = 0) {
	if ( $expiration != 0){
		setcookie($name, $value, time() + $expiration, '/', $domain, 0);
	} else {
		setcookie($name, $value, $expiration, '/', $domain, 0);
	}
}

/**
 *	session_redirect() - Redirect browser within the site
 *
 *	@param		string	Absolute path within the site
 *	@return never returns
 */
function session_redirect($loc) {
	header('Location: http' . (session_issecure()?'s':'') . '://' . getStringFromServer('HTTP_HOST') . $loc);
	print("\n\n");
	exit;
}

/**
 *	session_require() - Convenience function to easily enforce permissions
 *
 *	Calling page will terminate with error message if current user
 *	fails checks.
 *
 *	@param		array	Associative array specifying criteria
 *	@return does not return if check is failed
 *
 */
function session_require($req) {
	if (!session_loggedin()) {
		exit_not_logged_in();	
	}

	if ($req['group']) {
		$group =& group_get_object($req['group']);
		if (!$group || !is_object($group)) {
			exit_error('Error','Could Not Get Group');
		} elseif ($group->isError()) {
			exit_error('Error',$group->getErrorMessage());
		}

		$perm =& $group->getPermission( session_get_user() );
		if (!$perm || !is_object($perm) || $perm->isError()) {
			exit_permission_denied();
		}

		if ($req['admin_flags']) {
			if (!$perm->isAdmin()) {
				exit_permission_denied();
			}
		} else {
			if (!$perm->isMember()) {
				exit_permission_denied();
			}
		}
	} else if ($req['isloggedin']) {
		//no need to check as long as the check is present at top of function
	} else {
		exit_permission_denied();
	}
}

/**
 *	session_set_new() - Setup session for the given user
 *
 *	This function sets up SourceForge session for the given user,
 *	making one be "logged in".
 *
 *	@param		int		The user ID
 *	@return none
 */
function session_set_new($user_id) {
	global $G_SESSION,$session_ser,$Language;

	// set session cookie
  //
	$cookie = session_build_session_cookie($user_id);
	session_cookie("session_ser", $cookie, "", $GLOBALS['sys_session_expire']);
	$session_ser=$cookie;

	db_query("
		INSERT INTO user_session (session_hash, ip_addr, time, user_id) 
		VALUES (
			'".session_get_session_cookie_hash($cookie)."', 
			'".getStringFromServer('REMOTE_ADDR')."',
			'".time()."',
			$user_id
		)
	");

	// check uniqueness of the session_hash in the database
	// 
	$res = session_getdata($user_id);

	if (!$res || db_numrows($res) < 1) {
		exit_error($Language->getText('global','error'),$Language->getText('session','cannotinit').": ".db_error());
	} else {

		//set up the new user object
		//
		$G_SESSION = user_get_object($user_id,$res);
		if ($G_SESSION) {
			$G_SESSION->setLoggedIn(true);
		}
	}

}

/**
 *	Private optimization function for logins - fetches user data, language, and session
 *	with one query
 *
 *  @param		int		The user ID
 *	@access private
 */
function session_getdata($user_id) {
	$res=db_query("SELECT
		u.*,sl.language_id, sl.name, sl.filename, sl.classname, sl.language_code, t.dirname, t.fullname
		FROM users u,
		supported_languages sl,
		themes t
		WHERE u.language=sl.language_id 
		AND u.theme_id=t.theme_id
		AND u.user_id='$user_id'");
	return $res;
}

/**
 *	session_set() - Re-initialize session for the logged in user
 *
 *	This function checks that the user is logged in and if so, initialize
 *	internal session environment.
 *
 *	@return none
 */
function session_set() {
	plugin_hook('session_set_entry');
	global $G_SESSION;
	global $session_ser, $session_key;

	// assume bad session_hash and session. If all checks work, then allow
	// otherwise make new session
	$id_is_good = false;

	// If user says he's logged in (by presenting cookie), check that
	if ($session_ser) {

		$user_id = session_check_session_cookie($session_ser);

		if ($user_id) {

			$result = session_getdata($user_id);

			if (db_numrows($result) > 0) {
				$id_is_good = true;
			}
		}
	} // else (hash does not exist) or (session hash is bad)

	if ($id_is_good) {
		$G_SESSION = user_get_object($user_id, $result);
		if ($G_SESSION) {
			$G_SESSION->setLoggedIn(true);
		}
	} else {
		$G_SESSION=false;

		// if there was bad session cookie, kill it and the user cookie
		//
		if ($session_ser) {
			session_logout();
		}
	}
	plugin_hook('session_set_return');
}

//TODO - this should be generalized and used for pre.php, squal_pre.php, 
//SOAP, forum_gateway.php, tracker_gateway.php, etc to 
//setup languages
function session_continue($sessionKey) {
	global $session_ser, $Language, $sys_strftimefmt, $sys_datefmt;
	$session_ser = $sessionKey;
	session_set();
 	$Language=new BaseLanguage();
	$Language->loadLanguage("English"); // TODO use the user's default language
	setlocale (LC_TIME, $Language->getText('system','locale'));
	$sys_strftimefmt = $Language->getText('system','strftimefmt');
	$sys_datefmt = $Language->getText('system','datefmt');
	$LUSER =& session_get_user();
	if (!is_object($LUSER) || $LUSER->isError()) {
		return false;
	} else {
		putenv('TZ='. $LUSER->getTimeZone());
		return true;
	}
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

?>
