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

if (!isset($no_gz_buffer) || !$no_gz_buffer) {
	ob_start("ob_gzhandler");
}

// get constants used for flags or status
require('common/include/constants.php');

// Defines all of the GForge hosts, databases, etc.
// This needs to be loaded first because the lines below depend upon it.
$sys_localinc=getenv('sys_localinc');
if (is_file($sys_localinc)) {
	require($sys_localinc);
} else {
	if (is_file('/etc/gforge/local.inc')) {
		require ('/etc/gforge/local.inc');
	} else {
		if (is_file('etc/local.inc')) {
			require('etc/local.inc');
		}
	}
}

//
//	This file contains a few variables that override the etc/local.inc vars
//	This allows you to override such things as $sys_name, dbname, etc if you 
//	have multiple installs on one server.
//
require('overrides.inc');

/*
	redirect to proper hostname to get around certificate problem on IE 5
*/
if ($HTTP_HOST != $GLOBALS['sys_default_domain'] && $HTTP_HOST != $GLOBALS['sys_fallback_domain']) {
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


//various html utilities
require_once('common/include/utils.php');

//database abstraction
require_once('common/include/database.php');

//security library
require_once('common/include/session.php');

//system library
require_once('common/include/System.class');
if (!$sys_account_manager_type) {
	$sys_account_manager_type='UNIX';
}
require_once('common/include/system/'.$sys_account_manager_type.'.class');
$SYS=new $sys_account_manager_type();

//user functions like get_name, logged_in, etc
require_once('common/include/User.class');

//group functions like get_name, etc
require_once('common/include/Group.class');

//permission functions
require_once('common/include/Permission.class');

// escaping lib
require_once('common/include/escapingUtils.php');

//library to set up context help
require_once('www/include/help.php');

//exit_error library
require_once('www/include/exit.php');

//various html libs like button bar, themable
require_once('www/include/html.php');

// #### Connect to db

db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}

// Plugins subsystem
require_once('common/include/Plugin.class') ;
require_once('common/include/PluginManager.class') ;

// SCM-specific plugins subsystem
require_once('common/include/SCM.class') ;

setup_plugin_manager () ;

//determine if they're logged in
session_set();

//mandatory login
if (!session_loggedin() && $sys_force_login == 1 ) {
        $expl_pathinfo = explode('/',$REQUEST_URI);
        if ($REQUEST_URI!='/' && $expl_pathinfo[1]!='account') exit_not_logged_in();
}

//insert this page view into the database
require_once('www/include/logger.php');

//
//	If logged in, set up a $LUSER var referencing
//	the logged in user's object
//
if (session_loggedin()) {
	//set up the user's timezone if they are logged in
	$LUSER =& session_get_user();
	$LUSER->setUpTheme();
	header('Cache-Control: private');
}

//
//	Include user Theme
//
require_once($sys_themeroot.$sys_theme.'/Theme.class');

$HTML=new Theme();

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
if (session_loggedin()) {
	$Language=new BaseLanguage();
	$Language->loadLanguageID($LUSER->getLanguage());
} else {
	//if you aren't logged in, check your browser settings 
	//and see if we support that language
	//if we don't support it, just use English as default
	if ($HTTP_ACCEPT_LANGUAGE) {
		$res = language_code_to_result ($HTTP_ACCEPT_LANGUAGE);
		$classname=db_result($res,0,'classname');
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
