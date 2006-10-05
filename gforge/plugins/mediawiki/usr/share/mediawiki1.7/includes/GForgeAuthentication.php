<?php
# Copyright (C) 2006 Christian Bayle <bayle@debian.com>
# http://www.mediawiki.org/
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or 
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html

/**
 * Authentication plugin interface. Instantiate a subclass of AuthPlugin
 * and set $wgAuth to it to authenticate against some external tool.
 *
 * The default behavior is not to do anything, and use the local user
 * database for all authentication. A subclass can require that all
 * accounts authenticate externally, or use it only as a fallback; also
 * you can transparently create internal wiki accounts the first time
 * someone logs in who can be authenticated externally.
 *
 * This interface is new, and might change a bit before 1.4.0 final is
 * done...
 *
 * @package MediaWiki
 */

#
# GForgeAuthentication.php 
# Infos availible at http://bugzilla.wikipedia.org/show_bug.cgi?id=814
#
# Version 1.0f / 07.10.2005
# including the fixes describend in comment #50 #51 and #52
#
$wgGroupPermissions['*'    ]['createaccount']   = false;
//$wgGroupPermissions['*'    ]['read']            = false;
$wgGroupPermissions['*'    ]['edit']            = false;

require_once( 'AuthPlugin.php' );
require_once( "includes/GForgePre.php" );

function GForgeAuthenticationHook() {
	global $wgUser;
	global $wgRequest;
	global $_REQUEST;
	global $_SERVER;
	global $G_USERNAME;

	global $wgCacheEpoch;
	//echo $_SERVER["HTTP_REFERER"];
	$wgCacheEpoch = 'date +%Y%m%d%H%M%S';

	// For a few special pages, don't do anything.
	$title = $wgRequest->getVal('title') ;
	if ($title == 'Special:Userlogout' || $title == 'Special:Userlogin') {
		return;
	}
	// Do nothing if session is valid
	$wgUser = User::loadFromSession();
	if ($wgUser->isLoggedIn()) {
		return;
	}
	// Do little if user already exists
	//  (set the _REQUEST variable so that Login knows we're authenticated)
	$username = $G_USERNAME;
	$u = User::newFromName( $username );
	if (is_null($u)) {
		# Invalid username or some other error -- force login, just return
		return;
	}
	$wgUser = $u; 
	if ($u->getId() != 0) {
		$_REQUEST['wpName'] = $username;
		# also return, but user is know. set Cookies, et al
		$wgUser->setCookies();
		$wgUser->saveSettings();
		return;
	}
	// Ok, now we need to create a user.
	include 'includes/SpecialUserlogin.php';
	$form = new LoginForm( $wgRequest );
	$form->initUser( $wgUser );
	$wgUser->saveSettings();
	// if it worked: refer to login page, otherwise, exit
	header( "Location: http" . 
		(isset($_SERVER['HTTPS']) 
		&& $_SERVER['HTTPS'] == "on" ? "s" : "") . 
		"://" .  $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . 
		"/" .  
		( isset($_SERVER['URL']) ? $_SERVER['PATH_INFO']  . 
		( $_SERVER['QUERY_STRING'] ? "?" . $_SERVER['QUERY_STRING'] : "" )
	 	: "" ) 
	 );
	// Now redirect to referred page
	// print("Done");
	return;
}


class GForgeAuthenticationPlugin extends AuthPlugin {
	var $email, $lang, $realname, $nickname, $SearchType;

	function GForgeAuthenticationPlugin() {
		//if (session_loggedin()){
			global $wgExtensionFunctions;
			if (!isset($wgExtensionFunctions)) {
				$wgExtensionFunctions = array();
			}
			else if (!is_array($wgExtensionFunctions)) {
				$wgExtensionFunctions = array( $wgExtensionFunctions );
			}
			array_push($wgExtensionFunctions, 'GForgeAuthenticationHook');
		//}
		return;
	}

	// disallow password change
	function allowPasswordChange() {
		return false;
	}

	/**
	 * Check whether there exists a user account with the given name.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param string $username
	 * @return bool
	 * @access public
	 */
	//return whether $username is a valid username
	function userExists( $username ) {
		// in media wiki 1.5.5 this should always be true for autocreate() to work right
		return true;
	}
	
	/**
	 * Check if a username+password pair is a valid login.
	 * The name will be normalized to MediaWiki's requirements, so
	 * you might need to munge it (for instance, for lowercase initial
	 * letters).
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 * @access public
	 */
	function authenticate( $username, $password ) {
		/*
		global $G_USERNAME;
echo '<h1>XXXXXX'.$username.$G_USERNAME.'</h1>';
		if (strtolower($username) != $G_USERNAME) {
		      return false;
		}
		return isset($G_USERNAME);
		*/
		return session_login_valid(strtolower($username),$password);
	}

	/**
	 * Modify options in the login template.
	 * 
	 * @param UserLoginTemplate $template
	 * @access public
	 */
	function modifyUITemplate( &$template ) {
		$template->set( 'create', false );
                $template->set( 'usedomain', false );
		$template->set( 'useemail', false );

		//disable the mail new password box
		$template->set("useemail", false);
		//disable 'remember me' box
		$template->set("remember", false);
		//$template->set("create", false);
		$template->set("domain", false);
	}

	/**
	 * Return true if the wiki should create a new local account automatically
	 * when asked to login a user who doesn't exist locally but does in the
	 * external auth database.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return bool
	 * @access public
	 */
	//The authorization is external, so autocreate accounts as necessary
	function autoCreate() {
		return true;
	}

	/**
	 * Set the given password in the authentication database.
	 * Return true if successful.
	 * 
	 * @param string $password
	 * @return bool
	 * @access public
	 */
	function setPassword( $user, &$password ) {
		$this->printDebug("Entering setPassword",1);
		return true;
	}

	/**
	 * Update user information in the external authentication database.
	 * Return true if successful.
	 *
	 * @param User $user
	 * @return bool
	 * @access public
	 */	
        function updateExternalDB( $user ) {
		$this->printDebug("Entering updateExternalDB",1);
		$this->email = $user->getEmail();
		$this->realname = $user->getRealName();
		$this->nickname = $user->getOption('nickname');
		$this->language = $user->getOption('language');
		return true;
        }

	function canCreateAccounts() {
                return true;
	}

	/**
	 * Add a user to the external authentication database.
	 * Return true if successful.
	 *
	 * @param User $user
	 * @param string $password
	 * @return bool
	 * @access public
	 */
        function addUser( $user, $password ) {
		$this->printDebug("Entering addUser",1);
        }

	/**
	 * Return true to prevent logins that don't authenticate here from being
	 * checked against the local database's password fields.
	 *
	 * This is just a question, and shouldn't perform any actions.
	 *
	 * @return bool
	 * @access public
	 */
	function strict() {
		return true;
	}
	
	/**
	 * When creating a user account, optionally fill in preferences and such.
	 * For instance, you might pull the email address or real name from the
	 * external user database.
	 *
	 * The User object is passed by reference so it can be modified; don't
	 * forget the & on your function declaration.
	 *
	 * @param User $user
	 * @access public
	 */
	function initUser( &$user ) {
		global $G_SESSION;
		//unless you want the person to be nameless, you should probably populate
		// info about this user here
		if (isset($G_SESSION)){
			$user->setRealName($G_SESSION->getRealName());
			$user->setEmail($G_SESSION->getEmail());
		}
		$user->mEmailAuthenticated = wfTimestampNow();
		$user->setToken();

		//turn on e-mail notifications by default
		$user->setOption('enotifwatchlistpages', 1);
		$user->setOption('enotifusertalkpages', 1);
		$user->setOption('enotifminoredits', 1);
		$user->setOption('enotifrevealaddr', 1);
	}

	function getGForgeUserSession( &$wgUser ) {
		$wgUser = new User();
		if (session_loggedin()) {
			//User::SetupSession();
			$this->initUser(&$wgUser);
			return true;
		} else {
			$wgUser->logout();
			$wgUser = null;
		return false;
		}
	}
}

?>
