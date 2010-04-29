<?php
/**
 * pre.php - Automatically prepend to every page.
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2010, Roland Mas <lolando@debian.org>
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

require_once $gfcommon.'include/escapingUtils.php';

if (isset($_SERVER) && array_key_exists('PHP_SELF', $_SERVER) && $_SERVER['PHP_SELF']) {
	$_SERVER['PHP_SELF'] = htmlspecialchars($_SERVER['PHP_SELF']);
}

if (isset($GLOBALS) && array_key_exists('PHP_SELF', $GLOBALS) && $GLOBALS['PHP_SELF']) {
	$GLOBALS['PHP_SELF'] = htmlspecialchars($GLOBALS['PHP_SELF']);
}

// Block link prefetching (Moz prefetching, Google Web Accelerator, others)
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

// Get constants used for flags or status
require $gfcommon.'include/constants.php';

// Base error library for new objects
require_once $gfcommon.'include/Error.class.php';

// Database abstraction
require_once $gfcommon.'include/database-pgsql.php';
db_connect();
if (!$GLOBALS['gfconn']) {
	print forge_get_config ('forge_name')." Could Not Connect to Database: ".db_error();
	exit;
}

// Security library
require_once $gfcommon.'include/session.php';

// System library
require_once $gfcommon.'include/System.class.php';
if (!forge_get_config('account_manager_type')) {
	$sys_account_manager_type='UNIX';
}
require_once $gfcommon.'include/system/'.forge_get_config('account_manager_type').'.class.php';
$amt = forge_get_config('account_manager_type') ;
$SYS = new $amt();

// User-related classes and functions
require_once $gfcommon.'include/User.class.php';

// Project-related classes and functions
require_once $gfcommon.'include/Group.class.php';

// Permission-related functions
require_once $gfcommon.'include/Permission.class.php';

// Plugins subsystem
require_once $gfcommon.'include/Plugin.class.php' ;
require_once $gfcommon.'include/PluginManager.class.php' ;

// SCM-specific plugins subsystem
require_once $gfcommon.'include/SCMPlugin.class.php' ;

setup_plugin_manager () ;

// Jabber subsystem
if (forge_get_config('use_jabber')) {
	require_once $gfcommon.'include/Jabber.class.php';
}

if (isset($_SERVER['SERVER_SOFTWARE'])) { // We're on the web
	// exit_error() and variants (for the web)
	require_once $gfwww.'include/exit.php';

	// Library to determine browser settings
	require_once $gfwww.'include/browser.php';

	// HTML layout class, may be overriden by the Theme class
	require_once $gfwww.'include/Layout.class.php';

	// Various HTML utilities
	require_once $gfcommon.'include/utils.php';

	// Library to set up context help
	require_once $gfwww.'include/help.php';

	// Various HTML libs like button bar, themable
	require_once $gfwww.'include/html.php';

	// Forms key generation
	require_once $gfcommon.'include/forms.php';

	// Determine if there's a web session running
	session_set();
	
	plugin_hook('after_session_set');
	
	// Mandatory login
	if (!session_loggedin() && forge_get_config ('force_login') == 1 ) {
		$expl_pathinfo = explode('/',getStringFromServer('REQUEST_URI'));
		if (getStringFromServer('REQUEST_URI')!='/' && $expl_pathinfo[1]!='account' && $expl_pathinfo[1]!='export' ) exit_not_logged_in();
		// Show proj* export even if not logged in when force login
		// If not default web project page would be broken
		if ($expl_pathinfo[1]=='export' && !ereg("^proj", $expl_pathinfo[2])) exit_not_logged_in();
	}

	// Insert this page view into the database
	require_once $gfwww.'include/logger.php';

	// If logged in, set up a $LUSER var referencing
	// the logged in user's object
	if (session_loggedin()) {
		$LUSER =& session_get_user();
		$LUSER->setUpTheme();
		putenv ('TZ='. $LUSER->getTimeZone());
		header ('Cache-Control: private');
	}

	require_once forge_get_config('themes_root').forge_get_config('default_theme').'/Theme.class.php';
	$HTML = new Theme () ;
} else {		     // Script run from cron or a command line
	require_once $gfwww.'include/squal_exit.php';
}

// Determine locale
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
