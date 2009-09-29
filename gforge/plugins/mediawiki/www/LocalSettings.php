<?php

$fusionforgeproject = 'siteadmin' ;
$exppath = explode ('/', $_SERVER['PHP_SELF']) ;
while (count ($exppath) >= 4) {
        if (($exppath[0] == 'plugins') && ($exppath[1] == 'mediawiki') && ($exppath[2] == 'wiki') && ($exppath[4] == 'index.php')) {
                $fusionforgeproject = $exppath[3] ;
                break ;
        } else {
                array_shift ($exppath) ;
        }
}

define('MW_INSTALL_PATH','/usr/share/gforge/www/plugins/mediawiki');
$wikidata = "/var/lib/gforge/plugins/mediawiki/wikidata/$fusionforgeproject" ;

if (file_exists ("$wikidata/LocalSettings.php")) {
        require ("$wikidata/LocalSettings.php") ;
}

if( defined( 'MW_INSTALL_PATH' ) ) {
        $IP = MW_INSTALL_PATH;
} else {
        $IP = dirname( __FILE__ );
}

$path = array( $IP, "$IP/includes", "$IP/languages" );
set_include_path( implode( PATH_SEPARATOR, $path ) . PATH_SEPARATOR . get_include_path() );

require_once( "$IP/includes/DefaultSettings.php" );

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
        require_once ('/etc/gforge/local.inc') ;
        require_once ('/usr/share/gforge/www/env.inc.php') ;
} else {
        require_once ('/etc/gforge/database.inc') ;
}
$sys_dbport = 5432;

if ( $wgCommandLineMode ) {
        if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
                die( "This script must be run from the command line\n" );
        }
}
$wgSitename         = "$sys_name Wiki";
$wgScriptPath       = "/plugins/mediawiki/wiki/$fusionforgeproject" ;

$wgEmergencyContact = "webmaster@fusionforge.org";
$wgPasswordSender = "webmaster@fusionforge.org";

$wgDBtype           = "postgres";
$wgDBserver         = $sys_dbhost ;
$wgDBname           = $sys_dbname;
$wgDBuser           = $sys_dbuser ;
$wgDBpassword       = $sys_dbpasswd ;
$wgDBadminuser           = $sys_dbuser ;
$wgDBadminpassword       = $sys_dbpasswd ;
$wgDBport           = $sys_dbport ;
$wgDBmwschema       = str_replace ('-', '_', "plugin_mediawiki_$fusionforgeproject") ;
$wgDBts2schema      = str_replace ('-', '_', "plugin_mediawiki_$fusionforgeproject") ;
$wgMainCacheType = CACHE_NONE;
$wgMemCachedServers = array();

$wgEnableUploads = false;
$wgUploadDirectory = "$wikidata/images";
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgLocalInterwiki   = $wgSitename;
$wgShowExceptionDetails = true ;

$wgLanguageCode = "en";
# $wgDefaultSkin = 'gforge';
$wgStyleDirectory = '/usr/share/mediawiki/skins' ;

require ('/etc/gforge/local.inc') ;
require ('/usr/share/gforge/www/env.inc.php') ;
$GLOBALS['sys_dbhost'] = $sys_dbhost ;
$GLOBALS['sys_dbport'] = $sys_dbport ;
$GLOBALS['sys_dbname'] = $sys_dbname ;
$GLOBALS['sys_dbuser'] = $sys_dbuser ;
$GLOBALS['sys_dbpasswd'] = $sys_dbpasswd ;
$GLOBALS['sys_plugins_path'] = $sys_plugins_path ;
$GLOBALS['sys_urlprefix'] = $sys_urlprefix ;
$GLOBALS['sys_use_ssl'] = $sys_use_ssl ;
$GLOBALS['sys_default_domain'] = $sys_default_domain ;
$GLOBALS['sys_custom_path'] = $sys_custom_path ;
$GLOBALS['gfwww'] = $gfwww ;
$GLOBALS['gfplugins'] = $gfplugins ;
$GLOBALS['sys_lang'] = $sys_lang ;
require ($gfwww.'include/pre.php') ;
$GLOBALS['sys_urlroot'] = $sys_urlroot;
$GLOBALS['sys_session_key'] = $sys_session_key;
$GLOBALS['sys_session_expire'] = $sys_session_expire;
$GLOBALS['REMOTE_ADDR'] = getStringFromServer('REMOTE_ADDR') ;
$GLOBALS['HTTP_USER_AGENT'] = getStringFromServer('HTTP_USER_AGENT') ;

function GforgeMWAuth( &$user, &$result ) {
	global $fusionforgeproject ;

	$cookie = getStringFromCookie ('session_ser') ;
        if ($cookie != '') {
                $s = session_check_session_cookie ($cookie);
        } else {
                $s = false ;
        }
        if ($s) {
                $u = user_get_object ($s);
                $mwname = ucfirst($u->getUnixName ()) ;
                $mwu = User::newFromName ($mwname);
                if($mwu->getID() == 0) {
                        $mwu->addToDatabase ();
                        $mwu->setPassword (User::randomPassword());
                        $mwu->setRealName ($u->getRealName ()) ;
                        $mwu->setToken ();
                } else {
                        $mwu->loadFromDatabase ();
                }
		$g = group_get_object_by_name ($fusionforgeproject) ;
		$perm =& $g->getPermission($u);

		$mwu->loadGroups() ;
		$current_groups = $mwu->getGroups() ;

                if ($perm && is_object($perm) && $perm->isAdmin()) {
                        if (!in_array ('Administrators', $current_groups)) {
                                $mwu->addGroup ('Administrators') ;
                        }
                        if (!in_array ('Users', $current_groups)) {
                                $mwu->addGroup ('Users') ;
                        }
                } elseif ($perm && is_object($perm) && $perm->isMember()) {
                        if (in_array ('Administrators', $current_groups)) {
                                $mwu->removeGroup ('Administrators') ;
                        }
                        if (!in_array ('Users', $current_groups)) {
                                $mwu->addGroup ('Users') ;
                        }
                } else {
                        if (in_array ('Administrators', $current_groups)) {
                                $mwu->removeGroup ('Administrators') ;
                        }
                        if (in_array ('Users', $current_groups)) {
                                $mwu->removeGroup ('Users') ;
                        }
                }

                $mwu->setCookies ();
                $mwu->saveSettings ();

                $user = $mwu ;
        } else {
		$user->logout ();
        }
	return true ;
}

if (is_file("/etc/mediawiki-extensions/extensions.php")) {
        include( "/etc/mediawiki-extensions/extensions.php" );
}

$GLOBALS['wgHooks']['UserLoadFromSession'][]='GforgeMWAuth';

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit']          = false;
$wgGroupPermissions['*']['createpage']    = false;
$wgGroupPermissions['*']['createtalk']    = false;

$wgFavicon = '/images/icon.png' ;
$wgBreakFrames = false ;
ini_set ('memory_limit', '50M') ;
