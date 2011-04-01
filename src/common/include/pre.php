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

// Declare and init variables to store messages
$feedback = '';
$warning_msg = '';
$error_msg = '';

require_once $gfcommon.'include/escapingUtils.php';
require_once $gfcommon.'include/config.php';

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

// Database access and other passwords when on the web
function setconfigfromoldsources ($sec, $var, $serv, $env, $glob) {
	if (getenv ('SERVER_SOFTWARE')) {
		if (function_exists ('apache_request_headers')) {
			$headers = apache_request_headers() ;
		} else {
			$headers = array () ;
		}

		if (isset ($headers[$serv])) {
			forge_define_config_item ($var, $sec,
						  $headers[$serv]) ;
			return ;
		} 
	}
	if (isset ($_ENV[$env])) {
		forge_define_config_item ($var, $sec,
					  getenv($env)) ;
		return ;
	}
	if (isset ($GLOBALS[$glob])) {
		forge_define_config_item ($var, $sec,
					  $GLOBALS[$glob]) ;
		return ;
	}
}

if (file_exists ($gfcgfile)) {
	require_once $gfcgfile ;
}

setconfigfromoldsources ('core', 'database_host',
			 'GForgeDbhost', 'sys_gfdbhost', 'sys_dbhost') ;
setconfigfromoldsources ('core', 'database_port',
			 'GForgeDbport', 'sys_gfdbport', 'sys_dbport') ;
setconfigfromoldsources ('core', 'database_name',
			 'GForgeDbname', 'sys_gfdbname', 'sys_dbname') ;
setconfigfromoldsources ('core', 'database_user',
			 'GForgeDbuser', 'sys_gfdbuser', 'sys_dbuser') ;
setconfigfromoldsources ('core', 'database_password',
			 'GForgeDbpasswd', 'sys_gfdbpasswd', 'sys_dbpasswd') ;
setconfigfromoldsources ('core', 'ldap_password',
			 'GForgeLdapPasswd', 'sys_gfldap_passwd', NULL) ;
setconfigfromoldsources ('core', 'jabber_password',
			 'GForgeJabberPasswd', 'sys_gfjabber_pass', NULL) ;

forge_define_config_item ('source_path', 'core', $fusionforge_basedir) ;
forge_define_config_item ('data_path', 'core', '/var/lib/gforge') ;
forge_define_config_item ('chroot', 'core', '$core/data_path/chroot') ;
forge_define_config_item ('config_path', 'core', '/etc/gforge') ;

require_once $gfcommon.'include/config-vars.php';

forge_read_config_file ($gfconfig.'/config.ini') ;
forge_read_config_dir ($gfconfig.'/config.ini.d/') ;
if (($ecf = forge_get_config ('extra_config_files')) != NULL) {
	$ecfa = explode (',', $ecf) ;
	foreach ($ecfa as $cf) {
		$cf = trim ($cf) ;
		forge_read_config_file ($cf) ;
	}
}
if (($ecd = forge_get_config ('extra_config_dirs')) != NULL) {
	$ecda = explode (',', $ecd) ;
	foreach ($ecda as $cd) {
		$cd = trim ($cd) ;
		forge_read_config_dir ($cd) ;
	}
}

forge_define_config_item ('installation_environment', 'core', 'production') ;
$installation_environment = forge_get_config ('installation_environment') ;
if ($installation_environment == 'development' || $installation_environment == 'integration')
	$default_sysdebug_enable = 'true';
else
	$default_sysdebug_enable = 'false';
forge_define_config_item ('sysdebug_enable', 'core', $default_sysdebug_enable) ;
forge_set_config_item_bool ('sysdebug_enable', 'core') ;
forge_define_config_item ('sysdebug_phphandler', 'core', 'true') ;
forge_set_config_item_bool ('sysdebug_phphandler', 'core') ;
forge_define_config_item ('sysdebug_backtraces', 'core', 'false') ;
forge_set_config_item_bool ('sysdebug_backtraces', 'core') ;
forge_define_config_item ('sysdebug_ignored', 'core', 'false') ;
forge_set_config_item_bool ('sysdebug_ignored', 'core') ;
forge_define_config_item ('sysdebug_xmlstarlet', 'core', 'false') ;
forge_set_config_item_bool ('sysdebug_xmlstarlet', 'core') ;
forge_define_config_item ('sysdebug_akelos', 'core', 'false') ;
forge_set_config_item_bool ('sysdebug_akelos', 'core') ;
// Load extra func to add extras func like debug
$sysdebug_enable = forge_get_config('sysdebug_enable');
if ($sysdebug_enable) {
	require $gfcommon.'include/extras-debug.php';
}

// Get constants used for flags or status
require $gfcommon.'include/constants.php';


// Base error library for new objects
require_once $gfcommon.'include/Error.class.php';

// Database abstraction
// From here database is required
if (forge_get_config('database_name')!=""){
	require_once $gfcommon.'include/database-pgsql.php';
	
	// Authentication and access control
	require_once $gfcommon.'include/session.php';
	require_once $gfcommon.'include/RBACEngine.class.php';
	
	
	// System library
	require_once $gfcommon.'include/System.class.php';
	forge_define_config_item('account_manager_type', 'core', 'UNIX') ;
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
	
	// Authentication-specific plugins subsystem
	require_once $gfcommon.'include/AuthPlugin.class.php' ;

	if (getenv ('FUSIONFORGE_NO_PLUGINS') != 'true') {
		setup_plugin_manager () ;
	}
	
	// Jabber subsystem
	if (forge_get_config('use_jabber')) {
		require_once $gfcommon.'include/Jabber.class.php';
	}
	
	ini_set('date.timezone', forge_get_config ('default_timezone'));
	
	if (isset($_SERVER['SERVER_SOFTWARE'])) { // We're on the web
		// exit_error() and variants (for the web)
		require_once $gfcommon.'include/exit.php';
	
		// Library to determine browser settings
		require_once $gfwww.'include/browser.php';
	
		// HTML layout class, may be overriden by the Theme class
		require_once $gfwww.'include/Layout.class.php';
	
		// Various HTML utilities
		require_once $gfcommon.'include/utils.php';
	
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
		// and setup theme
		if (session_loggedin()) {
			$LUSER =& session_get_user();
			putenv ('TZ='. $LUSER->getTimeZone());
			header ('Cache-Control: private');
			require_once forge_get_config('themes_root').'/'.$LUSER->setUpTheme().'/Theme.class.php';
		} else {
			require_once forge_get_config('themes_root').'/'.forge_get_config('default_theme').'/Theme.class.php';
		}
		$HTML = new Theme () ;
	} else {		     // Script run from cron or a command line
		require_once $gfcommon.'include/squal_exit.php';
	}
	
	// Determine locale
	require_once $gfcommon.'include/gettext.php';
	require_once $gfcommon.'include/group_section_texts.php';
	
	setup_gettext_from_context();
}


$feedback = htmlspecialchars(getStringFromRequest('feedback', $feedback));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg', $error_msg));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg', $warning_msg));

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
