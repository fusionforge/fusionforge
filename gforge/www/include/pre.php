<?php
/**
 * pre.php - Automatically prepend to every page.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/*
	redirect to proper hostname to get around certificate problem on IE 5
*/
if (is_file('/etc/sourceforge/custom/pre.php')){
	require_once('/etc/sourceforge/custom/pre.php');
} else {

// Defines all of the Source Forge hosts, databases, etc.
// This needs to be loaded first becuase the lines below depend upon it.
require ('/etc/sourceforge/local.inc');

if ($HTTP_HOST != $GLOBALS['sys_default_domain']) {
	if ($SERVER_PORT == '443') {
		header ("Location: https://".$GLOBALS['sys_default_domain']."$REQUEST_URI");
	} else {
		header ("Location: http://".$GLOBALS['sys_default_domain']."$REQUEST_URI");
	}
	exit;
}

//
if ($sys_use_jabber) {
	require_once('common/include/Jabber.class');
}

//library to determine browser settings
require_once('www/include/browser.php');

//base error library for new objects
require_once('common/include/Error.class');

// HTML layout class, may be overriden by the Theme class
require_once('www/include/Layout.class');

$HTML = new Layout();

//various html utilities
require_once('common/include/utils.php');

//database abstraction
require_once('common/include/database.php');

//security library
require_once('common/include/session.php');

// LDAP library
require_once('common/include/ldap.php');

//user functions like get_name, logged_in, etc
require_once('common/include/User.class');

//group functions like get_name, etc
require_once('common/include/Group.class');

//permission functions
require_once('common/include/Permission.class');

//Project extends Group and includes preference accessors
require_once('common/include/Project.class');

//library to set up context help
require_once('www/include/help.php');

//exit_error library
require_once('www/include/exit.php');

//various html libs like button bar, themable
require_once('www/include/html.php');

//left-hand nav library, themable
require_once('www/include/menu.php');

//theme functions like get_themename, etc
require_once('www/include/theme.php');

//$sys_datefmt = "Y-m-d H:i";

// #### Connect to db

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}

//determine if they're logged in
session_set();

//insert this page view into the database
require_once('www/include/logger.php');

//
//	If logged in, set up a $LUSER var referencing
//	the logged in user's object
//
if (session_loggedin()) {
	//set up the user's timezone if they are logged in
	$LUSER =& session_get_user();
}

/*

	Timezone must come after logger to prevent messups


*/

if (session_loggedin()) {
	//set up the user's timezone if they are logged in
	putenv('TZ='. $LUSER->getTimeZone());
} else {
	//just use pacific time as always
}

/*

	Now figure out what language file to instantiate

*/

require_once('www/include/BaseLanguage.class');

if (!$sys_lang) {
	$sys_lang="English";
}
if (user_isloggedin()) {
	$res=$LUSER->getData();
		$classname=db_result($res,0,'classname');
	if ($classname) {
		$Language=new BaseLanguage();
		$Language->loadLanguage($classname);
	} else {
		$Language=new BaseLanguage();
		$Language->loadLanguage($sys_lang);
	}
} else {
	//if you aren't logged in, check your browser settings 
	//and see if we support that language
	//if we don't support it, just use English as default
        if ($HTTP_ACCEPT_LANGUAGE) {
               $res = language_code_to_result ($HTTP_ACCEPT_LANGUAGE);
               $classfile=db_result($res,0,'filename');
       }
	if (!$classname) {
		$classname=$sys_lang;
	}
	$Language=new BaseLanguage();
	$Language->loadLanguage($classname);
}

setlocale (LC_TIME, $Language->getText('system','locale'));
$sys_strftimefmt = $Language->getText('system','strftimefmt');
$sys_datefmt = $Language->getText('system','datefmt');

//ob_start(ob_gzhandler);

//
//	For now, only cache English, non-logged-in pages, with no POST going on
//
if (!user_isloggedin() && ($Language->getLanguageId() == 1) && (count($HTTP_POST_VARS) < 1) && !session_issecure()) {
	include_once('common/include/jpcache.php');
}
}
/*


RESERVED VARIABLES

$conn
$session_hash
$Language
$LUSER - Logged in user object
$HTML
$sys_datefmt

*/

?>
