<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: session.php,v 1.126 2000/12/05 19:10:49 tperdue Exp $
//

//G_SESSION is now a User object if user is logged in
$G_SESSION=false;

function session_login_valid($form_loginname,$form_pw,$allowpending=0)  {
	global $session_hash,$feedback;

	if (!$form_loginname || !$form_pw) {
		$feedback = 'Missing Password Or users Name';
		return false;
	}

	//get the users from the database using user_id and password
	$res = db_query("SELECT user_id,status FROM users WHERE "
		. "user_name='$form_loginname' "
		. "AND user_pw='" . md5($form_pw) . "'");
	if (!$res || db_numrows($res) < 1) {
		//invalid password or user_name
		$feedback='Invalid Password Or user Name';
		return false;
	} else {
		// check status of this user
		$usr = db_fetch_array($res);

		// if allowpending (for verify.php) then allow
		if ($allowpending && ($usr['status'] == 'P')) {
			//1;
		} else {
			if ($usr['status'] == 'S') { 
				//acount suspended
				$feedback = 'Account Suspended';
				return false;
			}
			if ($usr['status'] == 'P') { 
				//account pending
				$feedback = 'Account Pending';
				return false;
			} 
			if ($usr['status'] == 'D') { 
				//account deleted
				$feedback = 'Account Deleted';
				return false;
			}
			if ($usr['status'] != 'A') {
				//unacceptable account flag
				$feedback = 'Account Not Active';
				return false;
			}
		}
		//create a new session
		session_set_new(db_result($res,0,'user_id'));

		return true;
	}
}

function session_checkip($oldip,$newip) {
	$eoldip = explode(".",$oldip);
	$enewip = explode(".",$newip);
	
	// ## require same class b subnet
	if (($eoldip[0]!=$enewip[0])||($eoldip[1]!=$enewip[1])) {
		return 0;
	} else {
		return 1;
	}
}

function session_issecure() {
	return (getenv('SERVER_PORT') == '443');
}

function session_cookie($n,$v) {
	setcookie($n,$v,0,'/','',0);
}

function session_redirect($loc) {
	header('Location: http' . (session_issecure()?'s':'') . '://' . getenv('HTTP_HOST') . $loc);
	print("\n\n");
	exit;
}

/**
 *
 *   Method of easily enforcing permissions
 *   Page will terminate with error message if you fail checks
 *
 */

function session_require($req) {
	if (!user_isloggedin()) {
		exit_permission_denied();
	}

	/*
		SF Admins always have permission
	*/
	if (user_is_super_user()) {
		return true;
	}
	
	if ($req['group']) {
		$group=&group_get_object($req['group']);
		if (!$group) {
			exit_no_group();
		}		
		if ($req['admin_flags']) {
			//$query .= " AND admin_flags = '$req[admin_flags]'";	
			if (!$group->userIsAdmin()) {
				exit_permission_denied();
			}
		} else {
			if (!$group->userIsMember()) {
				exit_permission_denied();
			}
		}
	} else if ($req['isloggedin']) {
		//no need to check as long as the check is present at top of function
	} else {
		exit_permission_denied();
	}
}

function session_set_new($user_id) {
	global $G_SESSION;
	// concatinate current time, and random seed for MD5 hash
	// continue until unique hash is generated (SHOULD only be once)

	$pre_hash = time() . rand() . $GLOBALS['REMOTE_ADDR'] . microtime();
	$GLOBALS['session_hash'] = md5($pre_hash);

	// set session cookie
	session_cookie("session_hash",$GLOBALS['session_hash']);

	// make new session entries into db
	db_query("INSERT INTO session (session_hash, ip_addr, time,user_id) VALUES "
		. "('$GLOBALS[session_hash]','$GLOBALS[REMOTE_ADDR]'," . time() . ",'$user_id')");

	//
	// check uniqueness of the session_hash in the database
	// 
	$res=session_getdata($GLOBALS['session_hash']);

	if (!$res || db_numrows($res) < 1) {
		exit_error("ERROR","ERROR - SESSION HASH NOT FOUND IN DATABASE: ".db_error());
	} elseif (db_numrows($res) > 1) {
		//somehow, more than one entry in database...
		db_query("DELETE FROM session WHERE session_hash='$GLOBALS[session_hash]'");
		exit_error("ERROR","ERROR - two people had the same hash - backarrow and re-login. It should never happen again");
	} else {
		//set up the new user object

		$G_SESSION = user_get_object($user_id,$res);
		if ($G_SESSION) {
			$G_SESSION->setLoggedIn(true);
		}
	}
}

function session_getdata($session_hash) {
	//
	//      important - this must be updated with new 
	//      columns from the users, themes tables
	//
	$res=db_query("SELECT s.session_hash, s.ip_addr, s.time, 
	
		u.user_id, u.user_name, u.email, u.user_pw, 
		u.realname, u.status, u.shell, u.unix_pw, u.unix_status, 
		u.unix_uid, u.unix_box, u.add_date, u.confirm_hash, 
		u.mail_siteupdates, u.mail_va, u.authorized_keys, 
		u.email_new, u.people_view_skills, u.people_resume, u.timezone, 

		sl.language_id, sl.name, sl.filename, sl.classname, sl.language_code

		FROM users u,
		supported_languages sl, 
		session s
		WHERE u.language=sl.language_id 
		AND s.user_id=u.user_id 
		AND s.session_hash='$session_hash'");
	return $res;
}

function session_set() {
	global $G_SESSION;

	// assume bad session_hash and session. If all checks work, then allow
	// otherwise make new session
	$id_is_good = false;

	// here also check for good hash, set if new session is needed
	if ($GLOBALS['session_hash']) {
		$result=session_getdata($GLOBALS['session_hash']);

		// does hash exist?
		if (db_numrows($result) > 0) {
			if (session_checkip(db_result($result,0,'ip_addr'),$GLOBALS['REMOTE_ADDR'])) {
				$id_is_good = true;
			} else {
				$id_is_good = false;
				session_cookie('session_hash','');
			}
		} else {
			$id_is_good = false;
			session_cookie('session_hash','');
		}
	} // else (hash does not exist) or (session hash is bad)

	if ($id_is_good) {
		$G_SESSION=user_get_object(db_result($result,0,'user_id'),$result);
		if ($G_SESSION) {
			$G_SESSION->setLoggedIn(true);
		}
	} else {
		$G_SESSION=false;
	}
}

?>
