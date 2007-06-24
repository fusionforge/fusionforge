<?php
/**
 * pre.php - Automatically prepend to every page.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: pre.php 5498 2006-05-18 13:06:12Z tperdue $
 */

// escaping lib
require_once('common/include/escapingUtils.php');

// Just say no to link prefetching (Moz prefetching, Google Web Accelerator, others)
// http://www.google.com/webmasters/faq.html#prefetchblock
/*
if (getStringFromServer('HTTP_X_moz') === 'prefetch'){
	header(getStringFromServer('SERVER_PROTOCOL') . ' 404 Prefetch Forbidden');
	trigger_error('Prefetch request forbidden.');
	exit;
}
*/

/*
if (!isset($no_gz_buffer) || !$no_gz_buffer) {
	ob_start("ob_gzhandler");
}
*/

require('local.inc');
// get constants used for flags or status
require('common/include/constants.php');


//
/*
if ($sys_use_jabber) {
	require_once('common/include/Jabber.class.php');
}
*/

//library to determine browser settings
require_once('www/include/browser.php');

//base error library for new objects
require_once('common/include/Error.class.php');

// HTML layout class, may be overriden by the Theme class
require_once('www/include/Layout.class.php');

//various html utilities
//require_once('common/include/utils.php');

//database abstraction
require_once('common/include/database.php');
/*
function db_query(){
}
function db_numrows(){
}
*/

//security library
require_once('common/include/session.php');
/*
function session_issecure(){
}
function session_loggedin(){
}
*/

//system library
/*
require_once('common/include/System.class.php');
if (!$sys_account_manager_type) {
	$sys_account_manager_type='UNIX';
}
require_once('common/include/system/'.$sys_account_manager_type.'.class.php');
$SYS=new $sys_account_manager_type();
*/

//user functions like get_name, logged_in, etc
//require_once('common/include/User.class.php');
require_once('includes/GFUser.class.php.php');

//group functions like get_name, etc
//require_once('common/include/Group.class.php');
require_once('includes/GFProject.class.php.php');

//permission functions
require_once('common/include/Permission.class.php');

//library to set up context help
//require_once('www/include/help.php');

//exit_error library
require_once('www/include/exit.php');

//various html libs like button bar, themable
require_once('www/include/html.php');

//forms key generation
//require_once('common/include/forms.php');

// #### Connect to db
db_connect();

if (!$conn) {
	print "$sys_name Could Not Connect to Database: ".db_error();
	exit;
}

// Plugins subsystem
require_once('common/include/Plugin.class.php') ;
require_once('common/include/PluginManager.class.php') ;

// SCM-specific plugins subsystem
require_once('common/include/SCM.class.php') ;

setup_plugin_manager () ;
/*
function plugin_hook_by_reference(){
}
function plugin_hook(){
}
*/


//determine if they're logged in
session_set();



//mandatory login
if (!session_loggedin() && $sys_force_login == 1 ) {
	$expl_pathinfo = explode('/',getStringFromServer('REQUEST_URI'));
        if (getStringFromServer('REQUEST_URI')!='/' && $expl_pathinfo[1]!='account' && $expl_pathinfo[1]!='export' ) exit_not_logged_in();
	// Show proj* export even if not logged in when force login
	// If not default web project page would be broken
	if ($expl_pathinfo[1]=='export' && !ereg("^proj", $expl_pathinfo[2])) exit_not_logged_in();
}

//insert this page view into the database
//require_once('www/include/logger.php');

//
//	If logged in, set up a $LUSER var referencing
//	the logged in user's object
//
if (session_loggedin()) {
	//set up the user's timezone if they are logged in
	$LUSER =& session_get_user();
	$LUSER->setUpTheme();
	//header('Cache-Control: private');
	$GLOBALS['G_USERNAME']=$GLOBALS['G_SESSION']->getUnixName();
}

//
//	Include user Theme
//
require_once($sys_themeroot.$sys_theme.'/Theme.class.php');

$HTML=new Theme();

/*

	Timezone must come after logger to prevent messups


*/

/*
if (session_loggedin()) {
	//set up the user's timezone if they are logged in
	putenv('TZ='. $LUSER->getTimeZone());
} else {
	//just use pacific time as always
}
*/

/*

	Now figure out what language file to instantiate

*/

require_once('www/include/BaseLanguage.class.php');

if (!$sys_lang) {
	$sys_lang="English";
}
if (session_loggedin()) {
	$Language=new BaseLanguage();
	$Language->loadLanguageID($LUSER->getLanguage());
} else {
	//if you aren't logged in, check your browser settings 
	//and see if we support that language
	//if we don't support it, just use default language
	if (getStringFromServer('HTTP_ACCEPT_LANGUAGE')) {
		$classname = getLanguageClassName(getStringFromServer('HTTP_ACCEPT_LANGUAGE'));
	}
	if (!$classname) {
		$classname=$sys_lang;
	}
	$Language=new BaseLanguage();
	$Language->loadLanguage($classname);
}

setlocale (LC_TIME, _('en_US'));
$sys_strftimefmt = _('%Y %B %e  %H:%M');
$sys_datefmt = _('Y-m-d H:i');
$sys_shortdatefmt = _('Y-m-d');

/*

RESERVED VARIABLES

$conn
$session_hash
$Language
$LUSER - Logged in user object
$HTML
$sys_datefmt
$sys_shortdatefmt

*/

?>
