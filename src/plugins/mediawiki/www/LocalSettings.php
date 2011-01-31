<?php
  /* 
   * Copyright (C) 2010 Roland Mas, Olaf Lenz
   *
   * This file is part of FusionForge.
   *
   * FusionForge is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
   *
   * FusionForge is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   *
   * You should have received a copy of the GNU General Public License
   * along with FusionForge; if not, write to the Free Software
   * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   */
  /** This contains the local settings for Mediawiki as used in the
   *  Mediawiki plugin of FusionForge.
   */

require_once dirname(__FILE__) . '/../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

$IP = forge_get_config('master_path', 'mediawiki');

if (!isset ($fusionforgeproject)) {
	$fusionforgeproject = 'siteadmin' ;
}
$exppath = explode ('/', $_SERVER['PHP_SELF']) ;

# determine $fusionforgeproject from the URL
while (count ($exppath) >= 4) {
        if (($exppath[0] == 'plugins') &&
	    ($exppath[1] == 'mediawiki') &&
	    ($exppath[2] == 'wiki') &&
	    (($exppath[4] == 'index.php') || ($exppath[4] == 'api.php'))
	    ) {
                $fusionforgeproject = $exppath[3] ;
                break ;
        } else {
                array_shift ($exppath) ;
        }
}

$gconfig_dir = forge_get_config('mwdata_path', 'mediawiki');
$project_dir = forge_get_config('projects_path', 'mediawiki') . "/" 
	. $fusionforgeproject ;

if (!is_dir($project_dir)) {
	exit_error (sprintf(_('Mediawiki for project %s not created yet, please wait for a few minutes.'), $fusionforgeproject)) ;
}


$path = array( $IP, "$IP/includes", "$IP/languages" );
set_include_path( implode( PATH_SEPARATOR, $path ) . PATH_SEPARATOR . get_include_path() );

require_once( "$IP/includes/DefaultSettings.php" );

if ( $wgCommandLineMode ) {
        if ( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
                die( "This script must be run from the command line\n" );
        }
}
$g = group_get_object_by_name($fusionforgeproject) ;
$wgSitename         = $g->getPublicName() . " Wiki";
$wgScriptPath       = "/plugins/mediawiki/wiki/$fusionforgeproject" ;

$wgEmergencyContact = forge_get_config('admin_email');
$wgPasswordSender = forge_get_config('admin_email');

$wgDBtype           = "forge";
$wgDBserver         = forge_get_config('database_host') ;
$wgDBname           = forge_get_config('database_name');
$wgDBuser           = forge_get_config('database_user') ;
$wgDBpassword       = forge_get_config('database_password') ;
$wgDBadminuser           = forge_get_config('database_user') ;
$wgDBadminpassword       = forge_get_config('database_password') ;
$wgDBport           = forge_get_config('database_port') ;
$wgDBmwschema       = str_replace ('-', '_', "plugin_mediawiki_$fusionforgeproject") ;
$wgDBts2schema      = str_replace ('-', '_', "plugin_mediawiki_$fusionforgeproject") ;
$wgMainCacheType = CACHE_NONE;
$wgMemCachedServers = array();

$wgEnableUploads = forge_get_config('enable_uploads', 'mediawiki');
$wgUploadDirectory = "$project_dir/images";
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgLocalInterwiki   = $wgSitename;
$wgShowExceptionDetails = true ;

$wgLanguageCode = strtolower(forge_get_config('default_country_code'));

$wgDefaultSkin = 'fusionforge';

$GLOBALS['sys_dbhost'] = forge_get_config('database_host') ;
$GLOBALS['sys_dbport'] = forge_get_config('database_port') ;
$GLOBALS['sys_dbname'] = forge_get_config('database_name') ;
$GLOBALS['sys_dbuser'] = forge_get_config('database_user') ;
$GLOBALS['sys_dbpasswd'] = forge_get_config('database_password') ;
$GLOBALS['sys_plugins_path'] = forge_get_config('plugins_path') ;
$GLOBALS['sys_urlprefix'] = forge_get_config('url_prefix') ;
$GLOBALS['sys_use_ssl'] = forge_get_config('use_ssl') ;
$GLOBALS['sys_default_domain'] = forge_get_config('web_host') ;
$GLOBALS['sys_custom_path'] = forge_get_config('custom_path') ;
$GLOBALS['gfwww'] = $gfwww ;
$GLOBALS['gfplugins'] = $gfplugins ;
$GLOBALS['sys_lang'] = forge_get_config('default_language') ;
$GLOBALS['sys_urlroot'] = forge_get_config('url_root');
$GLOBALS['sys_session_key'] = forge_get_config('session_key');
$GLOBALS['sys_session_expire'] = forge_get_config('session_expire');
$GLOBALS['REMOTE_ADDR'] = getStringFromServer('REMOTE_ADDR') ;
$GLOBALS['HTTP_USER_AGENT'] = getStringFromServer('HTTP_USER_AGENT') ;

class DatabaseForge extends DatabasePostgres {
	function DatabaseForge($server=false, $user=false, $password=false,
			       $dbName=false, $failFunction=false, $flags=0) {
		global $wgDBtype;
		
		$wgDBtype = "postgres";
		return DatabasePostgres::DatabasePostgres($server, $user,
							  $password, $dbName, $failFunction, $flags);
	}
	
	function tableName($name) {
		switch ($name) {
		case 'interwiki':
			return 'public.plugin_mediawiki_interwiki';
		default:
			return DatabasePostgres::tableName($name);
		}
	}
}

function FusionForgeRoleToMediawikiGroupName ($role, $project) {
	if ($role instanceof RoleAnonymous) {
		return '*';
	} elseif ($role instanceof RoleLoggedIn) {
		return 'user';
	} elseif ($role->getHomeProject() == NULL) {
		return sprintf ('ForgeRole:%s [global]',
				$role->getName ()) ;
	} elseif ($role->getHomeProject()->getID() != $project->getID()) {
		return sprintf ('ForgeRole:%s [project %s]',
				$role->getName (),
				$role->getHomeProject()->getUnixName()) ;
	} else {
		return sprintf ('ForgeRole:%s',
				$role->getName ()) ;
	}
}

function FusionForgeMWAuth( $user, &$result ) {
	global $fusionforgeproject, $wgGroupPermissions ;

	$cookie = getStringFromCookie ('session_ser') ;
        if ($cookie != '') {
                $s = session_check_session_cookie ($cookie);
        } else {
                $s = false ;
        }
        if ($s) {
                $u = user_get_object ($s);
		$g = group_get_object_by_name ($fusionforgeproject) ;

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

		if (USE_PFO_RBAC) {
			$available_roles = RBACEngine::getInstance()->getAvailableRoles() ;
			$rs = array () ;
			foreach ($available_roles as $r) {
				$linked_projects = $r->getLinkedProjects () ;

				foreach ($linked_projects as $lp) {
					if ($lp->getID() == $g->getID()) {
						$rs[] = $r ;
					}
				}
			}
		} else {
			$perm =& $g->getPermission ();
			$r = $u->getRole($g) ;
			if (isset ($r) && $r && !$r->isError()) {
				$rs = array ($r) ;
			}
		}
		
		// Sync MW groups for current user with FF roles
		$rnames = array () ;
		foreach ($rs as $r) {
			$rnames[] = FusionForgeRoleToMediawikiGroupName ($r, $g) ;
		}
		$role_groups = preg_grep ("/^ForgeRole:/", $current_groups) ;

		foreach ($rnames as $rname) {
			if (!in_array ($rname, $current_groups)) {
				$user->addGroup ($rname) ;
			}
		}
		foreach ($role_groups as $cg) {
			if (!in_array ($cg, $rnames)) {
				$user->removeGroup ($cg) ;
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

function SetupPermissionsFromRoles () {
	global $fusionforgeproject, $wgGroupPermissions ;

	$g = group_get_object_by_name ($fusionforgeproject) ;
	// Setup rights for all roles referenced by project
	$rs = $g->getRoles() ;
	foreach ($rs as $r) {
		$gr = FusionForgeRoleToMediawikiGroupName ($r, $g) ;
		
		// Read access
		$wgGroupPermissions[$gr]['read'] = $r->hasPermission ('plugin_mediawiki_read', $g->getID()) ;
		
		// Day-to-day edit privileges
		$wgGroupPermissions[$gr]['edit']               = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editexisting') ;
		$wgGroupPermissions[$gr]['writeapi']           = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editexisting') ;
		$wgGroupPermissions[$gr]['createpage']         = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editnew') ;
		$wgGroupPermissions[$gr]['createtalk']         = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editnew') ;
		$wgGroupPermissions[$gr]['minoredit']          = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editnew') ;
		$wgGroupPermissions[$gr]['move']               = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editmove') ;
		$wgGroupPermissions[$gr]['move-subpages']      = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editmove') ;
		$wgGroupPermissions[$gr]['move-rootuserpages'] = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editmove') ;
		$wgGroupPermissions[$gr]['delete']             = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editmove') ;
		$wgGroupPermissions[$gr]['undelete']           = $r->hasPermission ('plugin_mediawiki_edit', $g->getID(), 'editmove') ;
		
		// File upload privileges
		$wgGroupPermissions[$gr]['upload']          = $r->hasPermission ('plugin_mediawiki_upload', $g->getID(), 'upload') ;
		$wgGroupPermissions[$gr]['reupload-own']    = $r->hasPermission ('plugin_mediawiki_upload', $g->getID(), 'upload') ;
		$wgGroupPermissions[$gr]['reupload']        = $r->hasPermission ('plugin_mediawiki_upload', $g->getID(), 'reupload') ;
		$wgGroupPermissions[$gr]['reupload-shared'] = $r->hasPermission ('plugin_mediawiki_upload', $g->getID(), 'reupload') ;
		$wgGroupPermissions[$gr]['upload_by_url']   = $r->hasPermission ('plugin_mediawiki_upload', $g->getID(), 'reupload') ;
		
		// Administrative tasks
		$wgGroupPermissions[$gr]['editinterface'] = $r->hasPermission ('plugin_mediawiki_admin', $g->getID()) ;
		$wgGroupPermissions[$gr]['import']        = $r->hasPermission ('plugin_mediawiki_admin', $g->getID()) ;
		$wgGroupPermissions[$gr]['importupload']  = $r->hasPermission ('plugin_mediawiki_admin', $g->getID()) ;
		$wgGroupPermissions[$gr]['siteadmin']     = $r->hasPermission ('plugin_mediawiki_admin', $g->getID()) ;
	}	
}

function NoLinkOnMainPage(&$personal_urls){
	unset($personal_urls['anonlogin']);
	unset($personal_urls['anontalk']);
	unset($personal_urls['logout']);
	unset($personal_urls['login']);
	return true;
}
$wgHooks['PersonalUrls'][]='NoLinkOnMainPage';

if (isset($_SERVER['SERVER_SOFTWARE'])) {
	class SpecialForgeRedir extends SpecialPage {
		var $dstappendself = false;

		function getTitle() {
			return 'SpecialForgeRedir';
		}

		function getRedirect() {
			return $this;
		}

		function getRedirectQuery() {
			return $this;
		}

		function getFullUrl() {
			$u = $this->dst;
			if ($this->dstappendself) {
				$u .= urlencode(getStringFromServer('REQUEST_URI'));
			}
			return util_make_url($u);
		}
	}

	class SpecialForgeRedirLogin extends SpecialForgeRedir {
		var $dstappendself = true;
		var $dst = '/account/login.php?return_to=';
	}

	class SpecialForgeRedirCreateAccount extends SpecialForgeRedir {
		var $dst = '/account/register.php';
	}

	class SpecialForgeRedirResetPass extends SpecialForgeRedir {
		var $dst = '/account/lostpw.php';
	}

	class SpecialForgeRedirLogout extends SpecialForgeRedir {
		var $dstappendself = true;
		var $dst = '/account/logout.php?return_to=';
	}

	function DisableLogInOut(&$mList) {
		$mList['Userlogin'] = 'SpecialForgeRedirLogin';
		$mList['CreateAccount'] = 'SpecialForgeRedirCreateAccount';
		$mList['Resetpass'] = 'SpecialForgeRedirResetPass';
		$mList['Userlogout'] = 'SpecialForgeRedirLogout';
		return true;
	}
	$GLOBALS['wgHooks']['SpecialPage_initList'][] = 'DisableLogInOut';
}

$GLOBALS['wgHooks']['UserLoadFromSession'][]='FusionForgeMWAuth';

$zeroperms = array ('read', 'writeapi', 'edit', 'move-subpages', 'move-rootuserpages', 'reupload-shared', 'createaccount');

foreach ($zeroperms as $i) {
	$wgGroupPermissions['user'][$i] = false;
	$wgGroupPermissions['*'][$i] = false;
}

SetupPermissionsFromRoles();

$wgFavicon = '/images/icon.png' ;
$wgBreakFrames = false ;
ini_set ('memory_limit', '50M') ;

// LOAD THE SITE-WIDE AND PROJECT-SPECIFIC EXTRA-SETTINGS
if (is_file(forge_get_config('config_path')."/plugins/mediawiki/LocalSettings.php")) {
	include(forge_get_config('config_path')."/plugins/mediawiki/LocalSettings.php");
}

// debian style system-wide mediawiki extensions
if (is_file("/etc/mediawiki-extensions/extensions.php")) {
        include( "/etc/mediawiki-extensions/extensions.php" );
}

if (file_exists("$wgUploadDirectory/.wgLogo.png")) {
	$wgLogo = "$wgScriptPath/images/.wgLogo.png";
}

// forge global settings
if (is_file("$gconfig_dir/ForgeSettings.php")) {
	include ("$gconfig_dir/ForgeSettings.php") ;
}
// project specific settings
if (is_file("$project_dir/ProjectSettings.php")) {
        include ("$project_dir/ProjectSettings.php") ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
