<?php
/**
  *
  * SourceForge Session Module
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

/**
 * A User object if user is logged in
 *
 * @var	constant		$G_SESSION
 */
$G_SESSION = false;

/**
 *	session_build_session_cookie() - Construct session cookie for the user
 *
 *	@param		int		User_id of the logged in user
 *	@return cookie value
 */
function session_build_session_cookie($user_id) {

	$session_serial = $user_id.'-'.time().'-'.$GLOBALS['REMOTE_ADDR'].'-'.$GLOBALS['HTTP_USER_AGENT'];
	$td = mcrypt_module_open($GLOBALS['sys_session_cypher'], "", $GLOBALS['sys_session_cyphermode'], "");
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $GLOBALS['sys_session_key'], $iv);
	$encrypted_session_serial = mcrypt_generic($td, $session_serial);
	mcrypt_generic_end($td);
	$session_serial_hash = md5($encrypted_session_serial.$GLOBALS['sys_session_key']);
	$session_serial_cookie = base64_encode($encrypted_session_serial).'-'.$session_serial_hash;

	return $session_serial_cookie;
}

/**
 *	session_build_username_cookie() - Construct username cookie
 *
 *	@param  string username of the logged in user
 *	@return cookie value
 */
function session_build_username_cookie($username) {

	// check if operating in plaintext or encrytped mode
	//
	if ($GLOBALS['sys_username_cookie_plaintext']) {

    return $username;

	} else {

		$td = mcrypt_module_open($GLOBALS['sys_username_cookie_cypher'], "", $GLOBALS['sys_username_cookie_cyphermode'], "");
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $GLOBALS['sys_username_cookie_key'], $iv);
		$encrypted_username = mcrypt_generic($td, $username);
		mcrypt_generic_end($td);
		$session_username_cookie = base64_encode($encrypted_username);

		return $session_username_cookie;

	} // else
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
	list ($junk, $hash) = explode('-', $session_cookie);
	return $hash;
}

/**
 *	session_check_session_cookie() - Check that session cookie passed from user is ok
 *
 *	@param		string	Value of the session cookie
 *	@return user_id if cookie is ok, false otherwise
 */
function session_check_session_cookie($session_cookie) {

	list ($encrypted_session_serial, $hash) = explode('-', $session_cookie);
	$encrypted_session_serial = base64_decode($encrypted_session_serial);
	$new_hash = md5($encrypted_session_serial.$GLOBALS['sys_session_key']);

	if ($hash != $new_hash) {
		return false;
	}

	$td = mcrypt_module_open($GLOBALS['sys_session_cypher'], "", $GLOBALS['sys_session_cyphermode'], "");
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $GLOBALS['sys_session_key'], $iv);
	$session_serial = mdecrypt_generic($td, $encrypted_session_serial);
	mcrypt_generic_end($td);

	list($user_id, $time, $ip, $user_agent) = explode('-', $session_serial, 4);

	if (!session_check_ip($ip, $GLOBALS['REMOTE_ADDR'])) {
		return false;
	}
	if (trim($user_agent) != $GLOBALS['HTTP_USER_AGENT']) {
		return false;
	}
	if ($time - time() >= $GLOBALS['sys_session_expire']) {
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
	session_cookie('username',
								 '',
								 $GLOBALS['sys_username_cookie_urlspace'], 
								 0);

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
		$res = db_query("
                       SELECT user_id,status,unix_pw
                       FROM users
	               WHERE user_name='$loginname' 
                ");
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
				// This one has clearly typed a bas passwd
				$feedback=$Language->getText('session','invalidpasswd');
				return false;
			} 
			// User exists, (crypt) unix_pw matches
			// Update the (MD5) user_pw and retry authentication
			// It should work, except for status errors
			$res = db_query ("UPDATE users
                                          SET user_pw='" . md5($passwd) . "'
                                          WHERE user_id='".$usr['user_id']."'
                                          ");
			return session_login_valid($loginname, $passwd, $allowpending) ;
		}
	} else {
		// If we're here, then the user has typed a password matching the (MD5) user_pw
		// Let's check whether it also matches the (crypt) unix_pw
		$usr = db_fetch_array($res);

		if (crypt ($passwd, $usr['unix_pw']) != $usr['unix_pw']) {
			// The (crypt) unix_pw does not patch
			// Invalidate (MD5) user_pw, refuse authentication
			$res = db_query ("UPDATE users
                                          SET user_pw='OUT OF DATE'
                                          WHERE user_id='".$usr['user_id']."'
                                          ");
			$feedback=$Language->getText('session','invalidpasswd');
			return false;
		}

		// Yay.  The provided password matches both fields in the database.
		// Let's check the status of this user

		// if allowpending (for verify.php) then allow
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
	return (getenv('SERVER_PORT') == '443');
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
	setcookie($name, $value, $expiration, '/', $domain, 0);
}

/**
 *	session_redirect() - Redirect browser within the site
 *
 *	@param		string	Absolute path within the site
 *	@return never returns
 */
function session_redirect($loc) {
	header('Location: http' . (session_issecure()?'s':'') . '://' . getenv('HTTP_HOST') . $loc);
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
	if (!user_isloggedin()) {
		exit_not_logged_in();	
		//exit_permission_denied();
	}

	if ($req['group']) {
		$group =& group_get_object($req['group']);
		exit_assert_object($group,'Group');

		$perm =& $group->getPermission( session_get_user() );
		exit_assert_object($perm,'Permission');

		if ($req['admin_flags']) {
			//$query .= " AND admin_flags = '$req[admin_flags]'";	
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
	global $G_SESSION;

	// set session cookie
  //
	$cookie = session_build_session_cookie($user_id);
	session_cookie("session_ser", $cookie);

	db_query("
		INSERT INTO session (session_hash, ip_addr, time, user_id) 
		VALUES (
			'".session_get_session_cookie_hash($cookie)."', 
			'".$GLOBALS['REMOTE_ADDR']."',
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

	// set username cookie for *.hostname.tld, expiration set in local.inc
	//
	session_cookie('username', 
								 session_build_username_cookie($G_SESSION->getUnixName()),
								 $GLOBALS['sys_username_cookie_urlspace'], 
								 time() + $GLOBALS['sys_username_cookie_expiration']);
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

		u.*,sl.language_id, sl.name, sl.filename, sl.classname, sl.language_code

		FROM users u,
		supported_languages sl
		WHERE u.language=sl.language_id 
		AND u.user_id='$user_id'
	");
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
 *  user_isloggedin()
 *  See if user is logged in
 */
function user_isloggedin() {
	global $G_SESSION;

	if ($G_SESSION) {
		return $G_SESSION->isLoggedIn();
	} else {
		return false;
	}
}

?>
