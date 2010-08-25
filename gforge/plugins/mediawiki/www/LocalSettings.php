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

$wgDefaultSkin = 'fusionforge';
$wgStyleDirectory = '/usr/share/mediawiki/skins' ;

require ('/etc/gforge/local.inc') ;
$wgLanguageCode = "en";
if (!empty($sys_default_country_code)) {
	$wgLanguageCode = strtolower($sys_default_country_code);
}
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

function FusionForgeMWAuth( $user, &$result ) {
	global $fusionforgeproject ;

	$cookie = getStringFromCookie ('session_ser') ;
        if ($cookie != '') {
                $s = session_check_session_cookie ($cookie);
        } else {
                $s = false ;
        }
        if ($s) {
                $u = user_get_object ($s);
		$g = group_get_object_by_name ($fusionforgeproject) ;
		$perm =& $g->getPermission($u);

                $mwname = ucfirst($u->getUnixName ()) ;
                $mwu = User::newFromName ($mwname);
                if($mwu->getID() == 0) {
                        $mwu->addToDatabase ();
                        $mwu->setPassword (User::randomPassword());
                        $mwu->setRealName ($u->getRealName ()) ;
                        $mwu->setToken ();
                        $mwu->loadFromDatabase ();
                }
                $user->mId=$mwu->getID();
                $user->loadFromId() ;

		$user->loadGroups() ;
		$current_groups = $user->getGroups() ;
                if ($perm && is_object($perm) && $perm->isAdmin()) {
                        if (!in_array ('sysop', $current_groups)) {
                                $user->addGroup ('sysop') ;
                        }
                        if (!in_array ('Members', $current_groups)) {
                                $user->addGroup ('Members') ;
                        }
                        if (!in_array ('user', $current_groups)) {
                                $user->addGroup ('user') ;
                        }
                } elseif ($perm && is_object($perm) && $perm->isMember()) {
                        if (in_array ('sysop', $current_groups)) {
                                $user->removeGroup ('sysop') ;
                        }
                        if (!in_array ('Members', $current_groups)) {
                                $user->addGroup ('Members') ;
                        }
                        if (!in_array ('user', $current_groups)) {
                                $user->addGroup ('user') ;
                        }
                } else {
                        if (in_array ('sysop', $current_groups)) {
                                $user->removeGroup ('sysop') ;
                        }
                        if (in_array ('Members', $current_groups)) {
                                $user->removeGroup ('Members') ;
                        }
                        if (!in_array ('user', $current_groups)) {
                                $user->addGroup ('user') ;
                        }
                }

                $user->setCookies ();
                $user->saveSettings ();
		wfSetupSession ();
	} else {
		$user->logout ();
        }

	$result = true;
	return true ;
}

if (is_file("/etc/mediawiki-extensions/extensions.php")) {
        include( "/etc/mediawiki-extensions/extensions.php" );
}
//function NoLogoutLinkOnMainPage(&$personal_urls){unset($personal_urls['logout']);return true;}
//$wgHooks['PersonalUrls']['logout']='NoLogoutLinkOnMainPage';
//function NoLoginLinkOnMainPage(&$personal_urls){unset($personal_urls['anonlogin']);return true;}
//$wgHooks['PersonalUrls']['anonlogin']='NoLoginLinkOnMainPage';
function NoLinkOnMainPage(&$personal_urls){
	unset($personal_urls['anonlogin']);
	unset($personal_urls['anontalk']);
	unset($personal_urls['logout']);
	unset($personal_urls['login']);
	return true;
}
$wgHooks['PersonalUrls'][]='NoLinkOnMainPage';

function DisableLogInOut(&$mList) {
  unset($mList['Userlogin']);
  unset($mList['CreateAccount']);
  unset($mList['Resetpass']);
  unset($mList['Userlogout']);
  return true;
}
//$GLOBALS['wgHooks']['SpecialPage_initList'][] = 'DisableLogInOut';

$GLOBALS['wgHooks']['UserLoadFromSession'][]='FusionForgeMWAuth';

// !! 'read' action is defined in the local project localSettings.php to allow swicth
// for public or private project switching

// No one can manage the accounts with Mediawiki interface
$wgGroupPermissions['sysop']['createaccount'] = false;
$wgGroupPermissions['sysop']['read'] 	      = true;

// Members are user who belongs to the current project, they can create or edit pages
$wgGroupPermissions['Members']['createaccount'] = false;
$wgGroupPermissions['Members']['read'] 		= true;
$wgGroupPermissions['Members']['edit']          = true;
$wgGroupPermissions['Members']['createpage']    = true;
$wgGroupPermissions['Members']['createtalk']    = true;

// logged users can only read all public projects - required because implicitly loaded
$wgGroupPermissions['user']['createaccount'] = false;
$wgGroupPermissions['user']['edit']          = false;
$wgGroupPermissions['user']['createpage']    = false;
$wgGroupPermissions['user']['createtalk']    = false;

// Not logged users can only read all public projects - required because implicitly loaded
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit']          = false;
$wgGroupPermissions['*']['createpage']    = false;
$wgGroupPermissions['*']['createtalk']    = false;

$wgLogo = "/themes/$sys_theme/images/wgLogo.png";

if (file_exists ("$wikidata/LocalSettings.php")) {
        require ("$wikidata/LocalSettings.php") ;
} else {
	exit_error (_('Wiki not created yet, please wait for a few minutes.')) ;
}

$wgFavicon = '/images/icon.png' ;
$wgBreakFrames = false ;
ini_set ('memory_limit', '50M') ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
