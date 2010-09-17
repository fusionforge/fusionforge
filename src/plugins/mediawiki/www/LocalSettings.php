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

require_once dirname(__FILE__) . '/../../env.inc.php';
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

$wgDBtype           = "postgres";
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
$GLOBALS['sys_session_expire'] = $sys_session_expire;
$GLOBALS['REMOTE_ADDR'] = getStringFromServer('REMOTE_ADDR') ;
$GLOBALS['HTTP_USER_AGENT'] = getStringFromServer('HTTP_USER_AGENT') ;

function FusionForgeRoleToMediawikiGroupName ($role, $project) {
	if ($role->getHomeProject() == NULL) {
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

		// Setup rights for all roles referenced by project
		$rs = $g->getRoles() ;
		foreach ($rs as $r) {
			$gr = FusionForgeRoleToMediawikiGroupName ($r, $g) ;

			// Day-to-day edit privileges
			switch ($r->getVal('plugin_mediawiki_edit', $g->getID())) {
			case 0:
				$wgGroupPermissions[$gr]['edit']          = false;
				$wgGroupPermissions[$gr]['createpage']    = false;
				$wgGroupPermissions[$gr]['createtalk']    = false;
				$wgGroupPermissions[$gr]['minoredit']     = false;
				$wgGroupPermissions[$gr]['move']          = false;
				$wgGroupPermissions[$gr]['delete']        = false;
				$wgGroupPermissions[$gr]['undelete']      = false;
				break ;
			case 1:
				$wgGroupPermissions[$gr]['edit']          = true;
				$wgGroupPermissions[$gr]['createpage']    = false;
				$wgGroupPermissions[$gr]['createtalk']    = false;
				$wgGroupPermissions[$gr]['minoredit']     = false;
				$wgGroupPermissions[$gr]['move']          = false;
				$wgGroupPermissions[$gr]['delete']        = false;
				$wgGroupPermissions[$gr]['undelete']      = false;
				break ;
			case 2:
				$wgGroupPermissions[$gr]['edit']          = true;
				$wgGroupPermissions[$gr]['createpage']    = true;
				$wgGroupPermissions[$gr]['createtalk']    = true;
				$wgGroupPermissions[$gr]['minoredit']     = true;
				$wgGroupPermissions[$gr]['move']          = false;
				$wgGroupPermissions[$gr]['delete']        = false;
				$wgGroupPermissions[$gr]['undelete']      = false;
				break ;
			case 3:
				$wgGroupPermissions[$gr]['edit']          = true;
				$wgGroupPermissions[$gr]['createpage']    = true;
				$wgGroupPermissions[$gr]['createtalk']    = true;
				$wgGroupPermissions[$gr]['minoredit']     = true;
				$wgGroupPermissions[$gr]['move']          = true;
				$wgGroupPermissions[$gr]['delete']        = true;
				$wgGroupPermissions[$gr]['undelete']      = true;
				break ;
			}

			// File upload privileges
			switch ($r->getVal('plugin_mediawiki_upload', $g->getID())) {
			case 0:
				$wgGroupPermissions[$gr]['upload']        = false;
				$wgGroupPermissions[$gr]['reupload-own']  = false;
				$wgGroupPermissions[$gr]['reupload']      = false;
				$wgGroupPermissions[$gr]['upload_by_url'] = false;
				break ;
			case 1:
				$wgGroupPermissions[$gr]['upload']        = true;
				$wgGroupPermissions[$gr]['reupload-own']  = true;
				$wgGroupPermissions[$gr]['reupload']      = false;
				$wgGroupPermissions[$gr]['upload_by_url'] = false;
				break ;
			case 2:
				$wgGroupPermissions[$gr]['upload']        = true;
				$wgGroupPermissions[$gr]['reupload-own']  = true;
				$wgGroupPermissions[$gr]['reupload']      = true;
				$wgGroupPermissions[$gr]['upload_by_url'] = true;
				break ;
			}

			// Administrative tasks
			switch ($r->getVal('plugin_mediawiki_admin', $g->getID())) {
			case 0:
				$wgGroupPermissions[$gr]['editinterface'] = false;
				$wgGroupPermissions[$gr]['import']        = false;
				$wgGroupPermissions[$gr]['importupload']  = false;
				$wgGroupPermissions[$gr]['siteadmin']     = false;
				break ;
			case 1:
				$wgGroupPermissions[$gr]['editinterface'] = true;
				$wgGroupPermissions[$gr]['import']        = true;
				$wgGroupPermissions[$gr]['importupload']  = true;
				$wgGroupPermissions[$gr]['siteadmin']     = true;
				break ;
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

function NoLinkOnMainPage(&$personal_urls){
	unset($personal_urls['anonlogin']);
	unset($personal_urls['anontalk']);
	unset($personal_urls['logout']);
	unset($personal_urls['login']);
	return true;
}
$wgHooks['PersonalUrls'][]='NoLinkOnMainPage';

class SpecialForgeRedir extends SpecialPage {
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
		return util_make_url($this->dst);
	}
}

class SpecialForgeRedirLogin extends SpecialForgeRedir {
	var $dst = '/account/login.php';
}

class SpecialForgeRedirCreateAccount extends SpecialForgeRedir {
	var $dst = '/account/register.php';
}

class SpecialForgeRedirResetPass extends SpecialForgeRedir {
	var $dst = '/account/lostpw.php';
}

class SpecialForgeRedirLogout extends SpecialForgeRedir {
	var $dst = '/account/logout.php';
}

function DisableLogInOut(&$mList) {
	$mList['Userlogin'] = 'SpecialForgeRedirLogin';
	$mList['CreateAccount'] = 'SpecialForgeRedirCreateAccount';
	$mList['Resetpass'] = 'SpecialForgeRedirResetPass';
	$mList['Userlogout'] = 'SpecialForgeRedirLogout';
	return true;
}
$GLOBALS['wgHooks']['SpecialPage_initList'][] = 'DisableLogInOut';

$GLOBALS['wgHooks']['UserLoadFromSession'][]='FusionForgeMWAuth';

$wgGroupPermissions['ForgeUsers']['createaccount'] = false;
$wgGroupPermissions['ForgeUsers']['edit']          = false;

$wgGroupPermissions['user']['createaccount'] = false;
$wgGroupPermissions['user']['edit']          = false;

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit']          = false;

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
