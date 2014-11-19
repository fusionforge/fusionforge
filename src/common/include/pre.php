<?php
/**
 * pre.php - Automatically prepend to every page.
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2010, Roland Mas <lolando@debian.org>
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/utils.php';

// Declare and init variables to store messages
util_init_messages();

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
function setconfigfromenv ($sec, $var, $serv, $env) {
	if (getenv ('SERVER_SOFTWARE')) {
		if (function_exists ('apache_request_headers')) {
			$headers = apache_request_headers() ;
		} else {
			$headers = array () ;
		}

		if (isset ($headers[$serv])) {
			forge_define_config_item ($var, $sec,
						  $headers[$serv]) ;
			return true;
		}
	}
	if (isset ($_ENV[$env])) {
		forge_define_config_item ($var, $sec,
					  getenv($env)) ;
		return true;
	}
	return false;
}

setconfigfromenv('core', 'database_host', 'GForgeDbhost', 'sys_gfdbhost');
setconfigfromenv('core', 'database_port', 'GForgeDbport', 'sys_gfdbport');
setconfigfromenv('core', 'database_name', 'GForgeDbname', 'sys_gfdbname');
setconfigfromenv('core', 'database_user', 'GForgeDbuser', 'sys_gfdbuser');
setconfigfromenv('core', 'database_password', 'GForgeDbpasswd', 'sys_gfdbpasswd');
setconfigfromenv('core', 'ldap_password', 'GForgeLdapPasswd', 'sys_gfldap_passwd');
setconfigfromenv('core', 'session_key', 'GForgeSessionKey', 'sys_session_key');

forge_read_config_file($gfconfig.'/'.$gfcgfile);

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

if (forge_get_config('use_ssl')) {
	header('Access-Control-Allow-Origin: http://'.forge_get_config('web_host'));
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
forge_define_config_item ('sysdebug_dberrors', 'core', 'true') ;
forge_set_config_item_bool ('sysdebug_dberrors', 'core') ;
forge_define_config_item ('sysdebug_dbquery', 'core', 'false') ;
forge_set_config_item_bool ('sysdebug_dbquery', 'core') ;
forge_define_config_item ('sysdebug_xmlstarlet', 'core', 'false') ;
forge_set_config_item_bool ('sysdebug_xmlstarlet', 'core') ;
// Load extra func to add extras func like debug
$sysdebug_enable = forge_get_config('sysdebug_enable');

// Server to access 'groupdir_prefix' via SSH
// In simple, single-server installs, it's the 'web_host'
if (forge_get_config('shell_host') == null) {
	forge_define_config_item('shell_host', 'core', forge_get_config('web_host'));
}

$sysDTDs = array(
	/*
	 * we could use xhtml-rdfa-1.dtd but would need to
	 * mirror the entire XHTML/1.1 shebang then, too
	 */
	'strict' => array(
		'dtdfile' => 'xhtml1-strict.dtd',
		'doctype' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
	),
	/* the original XHTML/1.0 Transitional */
	'transitional-orig' => array(
		'dtdfile' => 'xhtml1-transitional.dtd',
		'doctype' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
	),
	/* XHTML/1.0 Transitional + RDFa 1.0 */
	'transitional' => array(
		'dtdfile' => 'xhtml10t-rdfa10.dtd',
		'doctype' => '<!DOCTYPE html SYSTEM "http://evolvis.org/DTD/xhtml10t-rdfa10.dtd">'
	),
);

$sysXMLNSs = 'xmlns="http://www.w3.org/1999/xhtml"';
if (!$sysdebug_enable || !forge_get_config('sysdebug_xmlstarlet')) {
	foreach (array(
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'doap' => 'http://usefulinc.com/ns/doap#',
		'foaf' => 'http://xmlns.com/foaf/0.1/',
		'planetforge' => 'http://coclico-project.org/ontology/planetforge#',
		'sioc' => 'http://rdfs.org/sioc/ns#',
	    ) as $key => $value) {
		$sysXMLNSs .= ' xmlns:' . $key . '="' . $value . '"';
	}
}

if ($sysdebug_enable && getenv('SERVER_SOFTWARE')) {
	require $gfcommon.'include/extras-debug.php';
} else {
	$sysdebug_dberrors = false;
	$sysdebug_dbquery = false;

	function sysdebug_off($hdr=false, $replace=true, $resp=false) {
		if ($hdr !== false) {
			if ($resp === false) {
				header($hdr, $replace);
			} else {
				header($hdr, $replace, $resp);
			}
		}

		return false;
	}
	function sysdebug_lazymode($enable) {
		/* nothing */
	}
	function sysdebug_ajaxbody($enable=true) {
		/* nothing */
	}
}

// Get constants used for flags or status
require $gfcommon.'include/constants.php';

// Base error library for new objects
require_once $gfcommon.'include/Error.class.php';

// Database abstraction
// From here database is required
if (getenv('FUSIONFORGE_NO_DB') != 'true' and forge_get_config('database_name') != "") {
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

	// SysAuth-specific plugins subsystem
	require_once $gfcommon.'include/SysAuthPlugin.class.php' ;

	// Authentication-specific plugins subsystem
	require_once $gfcommon.'include/AuthPlugin.class.php' ;

	if (getenv ('FUSIONFORGE_NO_PLUGINS') != 'true') {
		setup_plugin_manager () ;
	}

	ini_set('date.timezone', forge_get_config ('default_timezone'));

	if (isset($_SERVER['SERVER_SOFTWARE'])) { // We're on the web
		// Detect upload larger that upload allowed size.
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) &&
		     empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0 )
		{
			$error_msg = sprintf(_('Posted data is too large. %1$s exceeds the maximum size of %2$s'),
					     human_readable_bytes($_SERVER['CONTENT_LENGTH']), human_readable_bytes(util_get_maxuploadfilesize()));
		}

		// exit_error() and variants (for the web)
		require_once $gfcommon.'include/exit.php';

		// Library to determine browser settings
		require_once $gfwww.'include/browser.php';

		// HTML layout class, may be overriden by the Theme class
		require_once $gfwww.'include/Layout.class.php';

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
			if (getStringFromServer('REQUEST_URI')!='/' && $expl_pathinfo[1]!='account' && $expl_pathinfo[1]!='export' && $expl_pathinfo[1]!='plugins') exit_not_logged_in();
			// Show proj* export even if not logged in when force login
			// If not default web project page would be broken
			if ($expl_pathinfo[1]=='export' && !preg_match('/^proj/', $expl_pathinfo[2])) exit_not_logged_in();
			// We must let auth plugins go further
			if ($expl_pathinfo[1]=='plugins' && !preg_match('/^auth/', $expl_pathinfo[2])) exit_not_logged_in();
		}

		// Insert this page view into the database
		require_once $gfcommon.'include/logger.php';

		// If logged in, set up a $LUSER var referencing
		// the logged in user's object
		// and setup theme
		if (session_loggedin()) {
			$LUSER =& session_get_user();
			$use_tooltips = $LUSER->usesTooltips();
			header('Cache-Control: private');
			$x_theme = $LUSER->setUpTheme();
		} else {
			$use_tooltips = 1;
			$x_theme = forge_get_config('default_theme');
		}
		require_once forge_get_config('themes_root').'/'.$x_theme.'/Theme.class.php';
		$HTML = new Theme () ;
		$HTML->_theme = $x_theme;
		unset($x_theme);
	} else {		     // Script run from cron or a command line
		require_once $gfcommon.'include/squal_exit.php';
	}

	// Determine locale
	require_once $gfcommon.'include/gettext.php';
	require_once $gfcommon.'include/group_section_texts.php';

	setup_tz_from_context();
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
