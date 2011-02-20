<?php
/*
 * Sets the default required environement for FusionForge
 *
 * Some of the settings made here can be overwrite in the
 * configuration file if needed.
 * 
 */

// Attempt to set up the include path, to fix problems with relative includes
$fusionforge_basedir = dirname(dirname(dirname( __FILE__ ))) ;
$include_path = join(PATH_SEPARATOR, 
	array("/etc/gforge/custom", "/etc/gforge", ".", 
		"$fusionforge_basedir/common", "$fusionforge_basedir/www",
		"$fusionforge_basedir/plugins", "$fusionforge_basedir",
		"$fusionforge_basedir/www/include",
		"$fusionforge_basedir/common/include",
		"/usr/share/php","/usr/share/pear"));

// By default, the include_path is changed to include path needed by Gforge.
// If this does not work, then set defines to real path directly.
//
// In case of failure, the following defines are set:
//    $gfconfig : Directory where are the configuration files (/etc/gforge).
//    $gfcommon : Directory common of gforge (for common php classes).
//    $gfwww    : Directory www of gforge (publicly accessible files).
//    $gfplugins: Directory for plugins.
//

// Easyforge config, allow several instances of gforge based on server name.
if (getenv('sys_localinc')) {
	$gfcgfile = getenv('sys_localinc');
	$gfconfig = dirname($gfcgfile).'/';
} elseif (isset($_SERVER['SERVER_NAME']) && 
	file_exists($fusionforge_basedir.'/config/'.$_SERVER['SERVER_NAME'].'/local.inc.php')) {
	$gfcgfile = $fusionforge_basedir.'/config/'.$_SERVER['SERVER_NAME'].'/local.inc.php';
	$gfconfig = $fusionforge_basedir.'/config/'.$_SERVER['SERVER_NAME'].'/';
} elseif (file_exists($fusionforge_basedir.'/config/local.inc.php')) {
	$gfcgfile = $fusionforge_basedir.'/config/local.inc.php';
	$gfconfig = $fusionforge_basedir.'/config/';
} elseif (file_exists('/etc/gforge/local.inc.php')) {
	$gfcgfile = '/etc/gforge/local.inc.php';
	$gfconfig = '/etc/gforge/';
} elseif (file_exists('/etc/gforge/local.inc')) {
	$gfcgfile = '/etc/gforge/local.inc';
	$gfconfig = '/etc/gforge/';
} else {
	$gfcgfile = 'local.inc';
	if (is_dir('/etc/gforge')){
                $gfconfig = '/etc/gforge/';
        } else {
		$gfconfig = '';
	}
}

if( !ini_set('include_path', $include_path ) && !set_include_path( $include_path )) {
	$gfcommon = $fusionforge_basedir.'/common/';
	$gfwww = $fusionforge_basedir.'/www/';
	$gfplugins = $fusionforge_basedir.'/plugins/';
} else {
	$gfcommon = '';
	$gfwww = '';
	$gfplugins = '';
}

?>
