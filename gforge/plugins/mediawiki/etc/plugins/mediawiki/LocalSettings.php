<?php

require_once('/etc/gforge/local.inc');

if (!isset($mediawiki_var_path))
	$mediawiki_var_path = "$sys_var_path/plugins/mediawiki";
if (!isset($mediawiki_projects_path))
	$mediawiki_projects_path = "$mediawiki_var_path/projects";
if (!isset($mediawiki_master_path))
	$mediawiki_master_path = "$mediawiki_var_path/master";

if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	// when loaded from the server
        require_once ("$sys_opt_path/www/env.inc.php") ;
	require_once ("$sys_opt_path/www/include/pre.php") ;
} else {
	// when run from the command line
        require_once ("$sys_etc_path/database.inc") ;
	require_once ("$sys_opt_path/common/include/config.php") ;
}

$IP = $mediawiki_master_path;

if (!isset ($fusionforgeproject)) {
	$fusionforgeproject = 'siteadmin' ;
}
$exppath = explode ('/', $_SERVER['PHP_SELF']) ;

# determine $fusionforgeproject from the URL
while (count ($exppath) >= 4) {
        if (($exppath[0] == 'plugins') && ($exppath[1] == 'mediawiki') && ($exppath[2] == 'wiki') && ($exppath[4] == 'index.php')) {
                $fusionforgeproject = $exppath[3] ;
                break ;
        } else {
                array_shift ($exppath) ;
        }
}

$project_dir = "$mediawiki_projects_path/$fusionforgeproject" ;

$path = array( $IP, "$IP/includes", "$IP/languages" );
set_include_path( implode( PATH_SEPARATOR, $path ) . PATH_SEPARATOR . get_include_path() );

require_once( "$IP/includes/DefaultSettings.php" );

if (!isset($sys_dbport)) { $sys_dbport = 5432; }

if ( $wgCommandLineMode ) {
        if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
                die( "This script must be run from the command line\n" );
        }
}
$g = group_get_object_by_name($fusionforgeproject) ;
$wgSitename         = $g->getPublicName() . " Wiki";
$wgScriptPath       = "/plugins/mediawiki/wiki/$fusionforgeproject" ;

$wgEmergencyContact = $sys_admin_email;
$wgPasswordSender = $sys_admin_email;

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
$wgUploadDirectory = "$project_dir/images";
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgLocalInterwiki   = $wgSitename;
$wgShowExceptionDetails = true ;

$wgLanguageCode = "en";
$wgDefaultSkin = 'fusionforge';

$GLOBALS['sys_dbhost'] = $sys_dbhost ;
$GLOBALS['sys_dbport'] = $sys_dbport ;
$GLOBALS['sys_dbname'] = $sys_dbname ;
$GLOBALS['sys_dbuser'] = $sys_dbuser ;
$GLOBALS['sys_dbpasswd'] = $sys_dbpasswd ;
$GLOBALS['sys_plugins_path'] = $sys_plugins_path ;
$GLOBALS['sys_urlprefix'] = $sys_urlprefix ;
$GLOBALS['sys_use_ssl'] = forge_get_config('use_ssl') ;
$GLOBALS['sys_default_domain'] = forge_get_config('web_host') ;
$GLOBALS['sys_custom_path'] = $sys_custom_path ;
$GLOBALS['gfwww'] = $gfwww ;
$GLOBALS['gfplugins'] = $gfplugins ;
$GLOBALS['sys_lang'] = forge_get_config('default_language') ;
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
		$r =& $u->getRole($g) ;

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

                // Role-based access control
		if (!isset ($r) || !$r || $r->isError()) {
			$rname = '' ;
		} else {
			$rname = "ForgeRole:".$r->getName () ;
		}
		$role_groups = preg_grep ("/^ForgeRole:/", $current_groups) ;
		foreach ($role_groups as $cg) {
			if ($cg != $rname) {
                                $user->removeGroup ($cg) ;
			}
		}
		if (!in_array ($rname, $current_groups)) {
			$user->addGroup ($rname) ;
		}

		// Previous (group-based) access control
               $current_groups = $user->getGroups() ;
                if ($perm && is_object($perm) && $perm->isAdmin()) {
                        if (!in_array ('sysop', $current_groups)) {
                                $user->addGroup ('sysop') ;
                        }
                        if (!in_array ('Members', $current_groups)) {
                                $user->addGroup ('Members') ;
                        }
                        if (!in_array ('ForgeUsers', $current_groups)) {
                                $user->addGroup ('ForgeUsers') ;
                        }
                } elseif ($perm && is_object($perm) && $perm->isMember()) {
                        if (in_array ('sysop', $current_groups)) {
                                $user->removeGroup ('sysop') ;
                        }
                        if (!in_array ('Members', $current_groups)) {
                                $user->addGroup ('Members') ;
                        }
                        if (!in_array ('ForgeUsers', $current_groups)) {
                                $user->addGroup ('ForgeUsers') ;
                        }
                } else {
                        if (in_array ('sysop', $current_groups)) {
                                $user->removeGroup ('sysop') ;
                        }
                        if (in_array ('Members', $current_groups)) {
                                $user->removeGroup ('Members') ;
                        }
                        if (!in_array ('ForgeUsers', $current_groups)) {
                                $user->addGroup ('ForgeUsers') ;
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

$GLOBALS['wgHooks']['UserLoadFromSession'][]='FusionForgeMWAuth';

$g = group_get_object_by_name ($fusionforgeproject) ;
$roles = $g->getRoles () ;
foreach ($roles as $role) {
	$gr = "ForgeRole:".$role->getName () ;
	switch ($role->getVal('plugin_mediawiki_edit', 0)) {
	case 0:
		$wgGroupPermissions[$gr]['edit']          = false;
		$wgGroupPermissions[$gr]['createpage']    = false;
		$wgGroupPermissions[$gr]['createtalk']    = false;
		break ;
	case 1:
		$wgGroupPermissions[$gr]['edit']          = true;
		$wgGroupPermissions[$gr]['createpage']    = false;
		$wgGroupPermissions[$gr]['createtalk']    = false;
		break ;
	case 2:
		$wgGroupPermissions[$gr]['edit']          = true;
		$wgGroupPermissions[$gr]['createpage']    = true;
		$wgGroupPermissions[$gr]['createtalk']    = true;
		break ;
	}
}

$wgGroupPermissions['ForgeUsers']['createaccount'] = false;
$wgGroupPermissions['ForgeUsers']['edit']          = false;
$wgGroupPermissions['ForgeUsers']['createpage']    = false;
$wgGroupPermissions['ForgeUsers']['createtalk']    = false;

$wgGroupPermissions['user']['createaccount'] = false;
$wgGroupPermissions['user']['edit']          = false;
$wgGroupPermissions['user']['createpage']    = false;
$wgGroupPermissions['user']['createtalk']    = false;

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit']          = false;
$wgGroupPermissions['*']['createpage']    = false;
$wgGroupPermissions['*']['createtalk']    = false;

$res = db_query_params("SELECT is_public from groups where unix_group_name=$1", array($fusionforgeproject)) ;
$row = db_fetch_array($res);
$public = $row['is_public'];
if ($public) {
        // Disable read permissions for non-members
	$wgGroupPermissions['Members']['read']          = true;
	$wgGroupPermissions['ForgeUsers']['read']     	= true;
	$wgGroupPermissions['user']['read']     	= true;
	$wgGroupPermissions['*']['read']          	= true;
} else {
        // Disable read permissions for non-members
	$wgGroupPermissions['Members']['read']          = true;
	$wgGroupPermissions['ForgeUsers']['read']     	= false;
	$wgGroupPermissions['user']['read']     	= false;
	$wgGroupPermissions['*']['read']          	= false;
} 

if (file_exists ("$project_dir/ProjectSettings.php")) {
        require ("$project_dir/ProjectSettings.php") ;
} else {
	exit_error (sprintf(_('Mediawiki for project %s not created yet, please wait for a few minutes.'), $fusionforgeproject)) ;
}

// Override default wiki logo
$wgLogo = "/themes/".forge_get_config('default_theme')."/images/wgLogo.png";
$wgFavicon = '/images/icon.png' ;
$wgBreakFrames = false ;
ini_set ('memory_limit', '50M') ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
