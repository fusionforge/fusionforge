<?php
/**
 * pre.php - Automatically prepend to every page.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 */

// escaping lib
require_once $gfcommon.'include/escapingUtils.php';

if (isset($_SERVER) && array_key_exists('PHP_SELF', $_SERVER) && $_SERVER['PHP_SELF']) {
	$_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
}

if (isset($GLOBALS) && array_key_exists('PHP_SELF', $GLOBALS) && $GLOBALS['PHP_SELF']) {
	$GLOBALS['PHP_SELF'] = htmlspecialchars($GLOBALS['PHP_SELF']);
}

// Just say no to link prefetching (Moz prefetching, Google Web Accelerator, others)
// http://www.google.com/webmasters/faq.html#prefetchblock
if (getStringFromServer('HTTP_X_moz') === 'prefetch'){
	header(getStringFromServer('SERVER_PROTOCOL') . ' 404 Prefetch Forbidden');
	trigger_error('Prefetch request forbidden.');
	exit;
}

if (!isset($no_gz_buffer) || !$no_gz_buffer) {
	ob_start("ob_gzhandler");
}

require $gfcgfile;
require $gfcommon.'include/config.php';
require $gfcommon.'include/config-vars.php';

forge_read_config_file ($gfconfig.'/config.ini') ;

// get constants used for flags or status
require $gfcommon.'include/constants.php';


//
if ($sys_use_jabber) {
	require_once $gfcommon.'include/Jabber.class.php';
}

//library to determine browser settings
require_once $gfwww.'include/browser.php';

//base error library for new objects
require_once $gfcommon.'include/Error.class.php';

// HTML layout class, may be overriden by the Theme class
require_once $gfwww.'include/Layout.class.php';


//various html utilities
require_once $gfcommon.'include/utils.php';

//database abstraction
require_once $gfcommon.'include/database-pgsql.php';

//security library
require_once $gfcommon.'include/session.php';

//system library
require_once $gfcommon.'include/System.class.php';
if (!$sys_account_manager_type) {
	$sys_account_manager_type='UNIX';
}
require_once $gfcommon.'include/system/'.$sys_account_manager_type.'.class.php';
$SYS=new $sys_account_manager_type();

//user functions like get_name, logged_in, etc
require_once $gfcommon.'include/User.class.php';

//group functions like get_name, etc
require_once $gfcommon.'include/Group.class.php';

//permission functions
require_once $gfcommon.'include/Permission.class.php';

//library to set up context help
require_once $gfwww.'include/help.php';

//exit_error library
require_once $gfwww.'include/exit.php';

//various html libs like button bar, themable
require_once $gfwww.'include/html.php';

//forms key generation
require_once $gfcommon.'include/forms.php';

// #### Connect to db

db_connect();

if (!$GLOBALS['gfconn']) {
	print forge_get_config ('forge_name')." Could Not Connect to Database: ".db_error();
	exit;
}

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

// SCM-specific plugins subsystem
require_once $gfcommon.'include/SCMPlugin.class.php' ;

setup_plugin_manager () ;


//determine if they're logged in
session_set();

plugin_hook('after_session_set');

//mandatory login
if (!session_loggedin() && $sys_force_login == 1 ) {
	$expl_pathinfo = explode('/',getStringFromServer('REQUEST_URI'));
        if (getStringFromServer('REQUEST_URI')!='/' && $expl_pathinfo[1]!='account' && $expl_pathinfo[1]!='export' ) exit_not_logged_in();
	// Show proj* export even if not logged in when force login
	// If not default web project page would be broken
	if ($expl_pathinfo[1]=='export' && !ereg("^proj", $expl_pathinfo[2])) exit_not_logged_in();
}

//insert this page view into the database
require_once $gfwww.'include/logger.php';

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
require_once $sys_themeroot.forge_get_config('default_theme').'/Theme.class.php';

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

require_once $gfcommon.'include/gettext.php';
require_once $gfcommon.'include/group_section_texts.php';

setup_gettext_from_context();

/*


RESERVED VARIABLES

$gfconn
$session_hash
$LUSER - Logged in user object
$HTML

*/


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
