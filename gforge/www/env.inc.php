<?php
/*
 * Sets the default required environnement for Gforge
 *
 * Some of the settings made here can be overwrite in the
 * configuration file if needed.
 * 
 */

# In case of errors, let output be clean.
$gfRequestTime = microtime( true );

@ini_set( 'memory_limit', '20M' );
@ini_set( "display_errors", true );

error_reporting( E_ALL );

# Attempt to set up the include path, to fix problems with relative includes
$IP = dirname(dirname( __FILE__ )) ;
$include_path = join(PATH_SEPARATOR, 
	array("/etc/gforge/custom", "/etc/gforge", "$IP/common", "$IP/www",	"$IP/plugins", "."));

// By default, the include_path is changed to include path needed by Gforge.
// If this does not work, then set defines to real path directly.
//
// In case of failure, the following defines are set:
//    GFCGFILE : Configuration file of gforge.
//    $gfconfig : Directory where are the configuration files (/etc/gforge).
//    $gfcommon : Directory common of gforge (for common php classes).
//    $gfwww    : Directory www of gforge (publicly accessible files).
//    $gfplugins: Directory for plugins.
//

// Easyforge config, allow several instances of gforge based on server name.
if (getenv('sys_localinc')) {
	$gfcgfile = getenv('sys_localinc');
	$gfconfig = dirname($gfcgfile/);
} elseif (file_exists($IP.'/config/'.$_SERVER['SERVER_NAME'].'/local.inc.php')) {
	$gfcgfile = $IP.'/config/'.$_SERVER['SERVER_NAME'].'/local.inc.php';
	$gfconfig = $IP.'/config/'.$_SERVER['SERVER_NAME'];
} elseif (file_exists($IP.'/config/local.inc.php')) {
	$gfcgfile = $IP.'/config/local.inc.php';
	$gfconfig = $IP.'/config/';
} else {
	$gfcgfile = 'local.inc';
	$gfconfig = '';
}

if( !ini_set('include_path', $include_path ) && !set_include_path( $include_path )) {
	$gfcommon = $IP.'/common/';
	$gfwww = $IP.'/www/';
	$gfplugins = $IP.'/plugins/';
} else {
	$gfcommon = '';
	$gfwww = '';
	$gfplugins = '';
}

?>
