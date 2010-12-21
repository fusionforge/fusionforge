<?php //-*-php-*-
//rcs_id('$Id: WikiUserNew.php 7787 2010-12-20 12:37:25Z vargenau $');
/* Copyright (C) 2004,2005,2006,2007,2009,2010 $ThePhpWikiProgrammingTeam
* Copyright (C) 2009-2010 Marc-Etienne Vargenau, Alcatel-Lucent
* Copyright (C) 2009-2010 Roger Guignard, Alcatel-Lucent
*
* This file is part of PhpWiki.
*
* PhpWiki is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* PhpWiki is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with PhpWiki; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/**
 * This is a complete OOP rewrite of the old WikiUser code with various
 * configurable external authentication methods.
 *
 * There's only one entry point, the function WikiUser which returns 
 * a WikiUser object, which contains the name, authlevel and user's preferences.
 * This object might get upgraded during the login step and later also.
 * There exist three preferences storage methods: cookie, homepage and db,
 * and multiple password checking methods.
 * See index.php for $USER_AUTH_ORDER[] and USER_AUTH_POLICY if 
 * ALLOW_USER_PASSWORDS is defined.
 *
 * Each user object must define the two preferences methods 
 *  getPreferences(), setPreferences(), 
 * and the following 1-4 auth methods
 *  checkPass()  must be defined by all classes,
 *  userExists() only if USER_AUTH_POLICY'=='strict' 
 *  mayChangePass()  only if the password is storable.
 *  storePass()  only if the password is storable.
 *
 * WikiUser() given no name, returns an _AnonUser (anonymous user)
 * object, who may or may not have a cookie. 
 * However, if the there's a cookie with the userid or a session, 
 * the user is upgraded to the matching user object.
 * Given a user name, returns a _BogoUser object, who may or may not 
 * have a cookie and/or PersonalPage, one of the various _PassUser objects 
 * or an _AdminUser object.
 * BTW: A BogoUser is a userid (loginname) as valid WikiWord, who might 
 * have stored a password or not. If so, his account is secure, if not
 * anybody can use it, because the username is visible e.g. in RecentChanges.
 *
 * Takes care of passwords, all preference loading/storing in the
 * user's page and any cookies. lib/main.php will query the user object to
 * verify the password as appropriate.
 *
 * @author: Reini Urban (the tricky parts), 
 *          Carsten Klapp (started rolling the ball)
 *
 * Random architectural notes, sorted by date:
 * 2004-01-25 rurban
 * Test it by defining ENABLE_USER_NEW in config/config.ini
 * 1) Now a ForbiddenUser is returned instead of false.
 * 2) Previously ALLOW_ANON_USER = false meant that anon users cannot edit, 
 *    but may browse. Now with ALLOW_ANON_USER = false he may not browse, 
 *    which is needed to disable browse PagePermissions.
 *    I added now ALLOW_ANON_EDIT = true to makes things clear. 
 *    (which replaces REQUIRE_SIGNIN_BEFORE_EDIT)
 * 2004-02-27 rurban:
 * 3) Removed pear prepare. Performance hog, and using integers as 
 *    handler doesn't help. Do simple sprintf as with adodb. And a prepare
 *    in the object init is no advantage, because in the init loop a lot of 
 *    objects are tried, but not used.
 * 4) Already gotten prefs are passed to the next object to avoid 
 *    duplicate getPreferences() calls.
 * 2004-03-18 rurban
 * 5) Major php-5 problem: $this re-assignment is disallowed by the parser
 *    So we cannot just discrimate with 
 *      if (!check_php_version(5))
 *          $this = $user;
 *    A /php5-patch.php is provided, which patches the src automatically 
 *    for php4 and php5. Default is php4.
 *    Update: not needed anymore. we use eval to fool the load-time syntax checker.
 * 2004-03-24 rurban
 * 6) enforced new cookie policy: prefs don't get stored in cookies
 *    anymore, only in homepage and/or database, but always in the 
 *    current session. old pref cookies will get deleted.
 * 2004-04-04 rurban
 * 7) Certain themes should be able to extend the predefined list 
 *    of preferences. Display/editing is done in the theme specific userprefs.tmpl,
 *    but storage must be extended to the Get/SetPreferences methods.
 *    <theme>/themeinfo.php must provide CustomUserPreferences:
 *      A list of name => _UserPreference class pairs.
 * 2010-06-07 rurban
 *    Fixed a nasty recursion bug (i.e. php crash), when user = new class 
 *    which returned false, did not return false on php-4.4.7. Check for 
 *    a object member now.
 */

define('WIKIAUTH_FORBIDDEN', -1); // Completely not allowed.
define('WIKIAUTH_ANON', 0);       // Not signed in.
define('WIKIAUTH_BOGO', 1);       // Any valid WikiWord is enough.
define('WIKIAUTH_USER', 2);       // Bogo user with a password.
define('WIKIAUTH_ADMIN', 10);     // UserName == ADMIN_USER.
define('WIKIAUTH_UNOBTAINABLE', 100);  // Permissions that no user can achieve

//if (!defined('COOKIE_EXPIRATION_DAYS')) define('COOKIE_EXPIRATION_DAYS', 365);
//if (!defined('COOKIE_DOMAIN'))          define('COOKIE_DOMAIN', '/');
if (!defined('EDITWIDTH_MIN_COLS'))     define('EDITWIDTH_MIN_COLS',     30);
if (!defined('EDITWIDTH_MAX_COLS'))     define('EDITWIDTH_MAX_COLS',    150);
if (!defined('EDITWIDTH_DEFAULT_COLS')) define('EDITWIDTH_DEFAULT_COLS', 80);

if (!defined('EDITHEIGHT_MIN_ROWS'))     define('EDITHEIGHT_MIN_ROWS',      5);
if (!defined('EDITHEIGHT_MAX_ROWS'))     define('EDITHEIGHT_MAX_ROWS',     80);
if (!defined('EDITHEIGHT_DEFAULT_ROWS')) define('EDITHEIGHT_DEFAULT_ROWS', 22);

define('TIMEOFFSET_MIN_HOURS', -26);
define('TIMEOFFSET_MAX_HOURS',  26);
if (!defined('TIMEOFFSET_DEFAULT_HOURS')) define('TIMEOFFSET_DEFAULT_HOURS', 0);

/* EMAIL VERIFICATION
 * On certain nets or hosts the email domain cannot be determined automatically from the DNS.
 * Provide some overrides here.
 *    ( username @ ) domain => mail-domain
 */
$EMailHosts = array('avl.com' => 'mail.avl.com');

/**
 * There are be the following constants in config/config.ini to 
 * establish login parameters:
 *
 * ALLOW_ANON_USER         default true
 * ALLOW_ANON_EDIT         default true
 * ALLOW_BOGO_LOGIN        default true
 * ALLOW_USER_PASSWORDS    default true
 * PASSWORD_LENGTH_MINIMUM default 0
 *
 * To require user passwords for editing:
 * ALLOW_ANON_USER  = true
 * ALLOW_ANON_EDIT  = false   (before named REQUIRE_SIGNIN_BEFORE_EDIT)
 * ALLOW_BOGO_LOGIN = false
 * ALLOW_USER_PASSWORDS = true
 *
 * To establish a COMPLETELY private wiki, such as an internal
 * corporate one:
 * ALLOW_ANON_USER = false
 * (and probably require user passwords as described above). In this
 * case the user will be prompted to login immediately upon accessing
 * any page.
 *
 * There are other possible combinations, but the typical wiki (such
 * as http://PhpWiki.sf.net/phpwiki) would usually just leave all four 
 * enabled.
 *
 */

// The last object in the row is the bad guy...
if (!is_array($USER_AUTH_ORDER))
    $USER_AUTH_ORDER = array("Forbidden");
else
    $USER_AUTH_ORDER[] = "Forbidden";

// Local convenience functions.
function _isAnonUserAllowed() {
    return (defined('ALLOW_ANON_USER') && ALLOW_ANON_USER);
}
function _isBogoUserAllowed() {
    return (defined('ALLOW_BOGO_LOGIN') && ALLOW_BOGO_LOGIN);
}
function _isUserPasswordsAllowed() {
    return (defined('ALLOW_USER_PASSWORDS') && ALLOW_USER_PASSWORDS);
}

// Possibly upgrade userobject functions.
function _determineAdminUserOrOtherUser($UserName) {
    // Sanity check. User name is a condition of the definition of the
    // _AdminUser, _BogoUser and _passuser.
    if (!$UserName)
        return $GLOBALS['ForbiddenUser'];

    //FIXME: check admin membership later at checkPass. Now we cannot raise the level.
    //$group = &WikiGroup::getGroup($GLOBALS['request']);
    if ($UserName == ADMIN_USER)
        return new _AdminUser($UserName);
    /* elseif ($group->isMember(GROUP_ADMIN)) { // unneeded code
        return _determineBogoUserOrPassUser($UserName);
    }
    */
    else
        return _determineBogoUserOrPassUser($UserName);
}

function _determineBogoUserOrPassUser($UserName) {
    global $ForbiddenUser;

    // Sanity check. User name is a condition of the definition of
    // _BogoUser and _PassUser.
    if (!$UserName)
        return $ForbiddenUser;

    // Check for password and possibly upgrade user object.
    // $_BogoUser = new _BogoUser($UserName);
    if (_isBogoUserAllowed() and isWikiWord($UserName)) {
        include_once("lib/WikiUser/BogoLogin.php");
        $_BogoUser = new _BogoLoginPassUser($UserName);
        if ($_BogoUser->userExists() or $GLOBALS['request']->getArg('auth'))
            return $_BogoUser;
    }
    if (_isUserPasswordsAllowed()) {
    	// PassUsers override BogoUsers if a password is stored
        if (isset($_BogoUser) and isset($_BogoUser->_prefs) 
            and $_BogoUser->_prefs->get('passwd'))
            return new _PassUser($UserName, $_BogoUser->_prefs);
        else { 
            $_PassUser = new _PassUser($UserName,
                                       isset($_BogoUser) ? $_BogoUser->_prefs : false);
            if ($_PassUser->userExists() or $GLOBALS['request']->getArg('auth')) {
            	if (isset($GLOBALS['request']->_user_class))
	    	    $class = $GLOBALS['request']->_user_class;
            	elseif (strtolower(get_class($_PassUser)) == "_passuser")
	    	    $class = $_PassUser->nextClass();
	    	else
		    $class = get_class($_PassUser);
    		if ($user = new $class($UserName, $_PassUser->_prefs)
    		    and $user->_userid) {
	            return $user;
            	} else {
            	    return $_PassUser;
            	}
            }
        }
    }
    // No Bogo- or PassUser exists, or
    // passwords are not allowed, and bogo is disallowed too.
    // (Only the admin can sign in).
    return $ForbiddenUser;
}

/**
 * Primary WikiUser function, called by lib/main.php.
 * 
 * This determines the user's type and returns an appropriate user
 * object. lib/main.php then querys the resultant object for password
 * validity as necessary.
 *
 * If an _AnonUser object is returned, the user may only browse pages
 * (and save prefs in a cookie).
 *
 * To disable access but provide prefs the global $ForbiddenUser class 
 * is returned. (was previously false)
 * 
 */
function WikiUser ($UserName = '') {
    global $ForbiddenUser, $HTTP_SESSION_VARS;

    //Maybe: Check sessionvar for username & save username into
    //sessionvar (may be more appropriate to do this in lib/main.php).
    if ($UserName) {
        $ForbiddenUser = new _ForbiddenUser($UserName);
        // Found a user name.
        return _determineAdminUserOrOtherUser($UserName);
    }
    elseif (!empty($HTTP_SESSION_VARS['userid'])) {
        // Found a user name.
        $ForbiddenUser = new _ForbiddenUser($_SESSION['userid']);
        return _determineAdminUserOrOtherUser($_SESSION['userid']);
    }
    else {
        // Check for autologin pref in cookie and possibly upgrade
        // user object to another type.
        $_AnonUser = new _AnonUser();
        if ($UserName = $_AnonUser->_userid && $_AnonUser->_prefs->get('autologin')) {
            // Found a user name.
            $ForbiddenUser = new _ForbiddenUser($UserName);
            return _determineAdminUserOrOtherUser($UserName);
        }
        else {
            $ForbiddenUser = new _ForbiddenUser();
            if (_isAnonUserAllowed())
                return $_AnonUser;
            return $ForbiddenUser; // User must sign in to browse pages.
        }
        return $ForbiddenUser;     // User must sign in with a password.
    }
    /*
    trigger_error("DEBUG: Note: End of function reached in WikiUser." . " "
                  . "Unexpectedly, an appropriate user class could not be determined.");
    return $ForbiddenUser; // Failsafe.
    */
}

/**
 * WikiUser.php use the name 'WikiUser'
 */
function WikiUserClassname() {
    return '_WikiUser';
}


/**
 * Upgrade olduser by copying properties from user to olduser.
 * We are not sure yet, for which php's a simple $this = $user works reliably,
 * (on php4 it works ok, on php5 it's currently disallowed on the parser level)
 * that's why try it the hard way.
 */
function UpgradeUser ($user, $newuser) {
    if (isa($user,'_WikiUser') and isa($newuser,'_WikiUser')) {
        // populate the upgraded class $newuser with the values from the current user object
        //only _auth_level, _current_method, _current_index,
        if (!empty($user->_level) and 
            $user->_level > $newuser->_level)
            $newuser->_level = $user->_level;
        if (!empty($user->_current_index) and
            $user->_current_index > $newuser->_current_index) {
            $newuser->_current_index = $user->_current_index;
            $newuser->_current_method = $user->_current_method;
        }
        if (!empty($user->_authmethod))
            $newuser->_authmethod = $user->_authmethod;
	$GLOBALS['request']->_user_class = get_class($newuser);
        /*
        foreach (get_object_vars($user) as $k => $v) {
            if (!empty($v)) $olduser->$k = $v;	
        }
        */
        $newuser->hasHomePage(); // revive db handle, because these don't survive sessions
        //$GLOBALS['request']->_user = $olduser;
        return $newuser;
    } else {
        return false;
    }
}

/**
 * Probably not needed, since we use the various user objects methods so far.
 * Anyway, here it is, looping through all available objects.
 */
function UserExists ($UserName) {
    global $request;
    if (!($user = $request->getUser()))
        $user = WikiUser($UserName);
    if (!$user) 
        return false;
    if ($user->userExists($UserName)) {
        $request->_user = $user;
        return true;
    }
    if (isa($user,'_BogoUser'))
        $user = new _PassUser($UserName,$user->_prefs);
    $class = $user->nextClass();
    if ($user = new $class($UserName, $user->_prefs)) {
        return $user->userExists($UserName);
    }
    $request->_user = $GLOBALS['ForbiddenUser'];
    return false;
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Base WikiUser class.
 */
class _WikiUser
{
     var $_userid = '';
     var $_level = WIKIAUTH_ANON;
     var $_prefs = false;
     var $_HomePagehandle = false;

    // constructor
    function _WikiUser($UserName='', $prefs=false) {

        $this->_userid = $UserName;
        $this->_HomePagehandle = false;
        if ($UserName) {
            $this->hasHomePage();
        }
        if (empty($this->_prefs)) {
            if ($prefs) $this->_prefs = $prefs;
            else $this->getPreferences();
        }
    }

    function UserName() {
        if (!empty($this->_userid))
            return $this->_userid;
    }

    function getPreferences() {
        trigger_error("DEBUG: Note: undefined _WikiUser class trying to load prefs." . " "
                      . "New subclasses of _WikiUser must override this function.");
        return false;
    }

    function setPreferences($prefs, $id_only) {
        trigger_error("DEBUG: Note: undefined _WikiUser class trying to save prefs." 
                      . " "
                      . "New subclasses of _WikiUser must override this function.");
        return false;
    }

    function userExists() {
        return $this->hasHomePage();
    }

    function checkPass($submitted_password) {
        // By definition, an undefined user class cannot sign in.
        trigger_error("DEBUG: Warning: undefined _WikiUser class trying to sign in." 
                      . " "
                      . "New subclasses of _WikiUser must override this function.");
        return false;
    }

    // returns page_handle to user's home page or false if none
    function hasHomePage() {
        if ($this->_userid) {
            if (!empty($this->_HomePagehandle) and is_object($this->_HomePagehandle)) {
                return $this->_HomePagehandle->exists();
            }
            else {
                // check db again (maybe someone else created it since
                // we logged in.)
                global $request;
                $this->_HomePagehandle = $request->getPage($this->_userid);
                return $this->_HomePagehandle->exists();
            }
        }
        // nope
        return false;
    }

    function createHomePage() {
        global $request;
        $versiondata = array('author' => ADMIN_USER);
        $request->_dbi->save(_("Automatically created user homepage to be able to store UserPreferences.").
                             "\n{{Template/UserPage}}",
                             1, $versiondata);
        $request->_dbi->touch();
        $this->_HomePagehandle = $request->getPage($this->_userid);
    }

    // innocent helper: case-insensitive position in _auth_methods
    function array_position ($string, $array) {
        $string = strtolower($string);
        for ($found = 0; $found < count($array); $found++) {
            if (strtolower($array[$found]) == $string)
                return $found;
        }
        return false;
    }

    function nextAuthMethodIndex() {
        if (empty($this->_auth_methods)) 
            $this->_auth_methods = $GLOBALS['USER_AUTH_ORDER'];
        if (empty($this->_current_index)) {
            if (strtolower(get_class($this)) != '_passuser') {
            	$this->_current_method = substr(get_class($this),1,-8);
                $this->_current_index = $this->array_position($this->_current_method,
                                                              $this->_auth_methods);
            } else {
            	$this->_current_index = -1;
            }
        }
        $this->_current_index++;
        if ($this->_current_index >= count($this->_auth_methods))
            return false;
        $this->_current_method = $this->_auth_methods[$this->_current_index];
        return $this->_current_index;
    }

    function AuthMethod($index = false) {
        return $this->_auth_methods[ $index === false 
                                     ? count($this->_auth_methods)-1 
                                     : $index];
    }

    // upgrade the user object
    function nextClass() {
        $method = $this->AuthMethod($this->nextAuthMethodIndex());
        include_once("lib/WikiUser/$method.php");
        return "_".$method."PassUser";
    }

    //Fixme: for _HttpAuthPassUser
    function PrintLoginForm (&$request, $args, $fail_message = false,
                             $seperate_page = false) {
        include_once('lib/Template.php');
        // Call update_locale in case the system's default language is not 'en'.
        // (We have no user pref for lang at this point yet, no one is logged in.)
        if ($GLOBALS['LANG'] != DEFAULT_LANGUAGE)
            update_locale(DEFAULT_LANGUAGE);
        $userid = $this->_userid;
        $require_level = 0;
        extract($args); // fixme

        $require_level = max(0, min(WIKIAUTH_ADMIN, (int)$require_level));

        $pagename = $request->getArg('pagename');
        $nocache = 1;
        $login = Template('login',
                          compact('pagename', 'userid', 'require_level',
                                  'fail_message', 'pass_required', 'nocache'));
        // check if the html template was already processed
        $seperate_page = $seperate_page ? true : !alreadyTemplateProcessed('html');
        if ($seperate_page) {
            $page = $request->getPage($pagename);
            $revision = $page->getCurrentRevision();
            return GeneratePage($login,_("Sign In"), $revision);
        } else {
            return $login->printExpansion();
        }
    }

    /** Signed in but not password checked or empty password.
     */
    function isSignedIn() {
        return (isa($this,'_BogoUser') or isa($this,'_PassUser'));
    }

    /** This is password checked for sure.
     */
    function isAuthenticated() {
        //return isa($this,'_PassUser');
        //return isa($this,'_BogoUser') || isa($this,'_PassUser');
        return $this->_level >= WIKIAUTH_BOGO;
    }

    function isAdmin () {
        static $group; 
        if ($this->_level == WIKIAUTH_ADMIN) return true;
        if (!$this->isSignedIn()) return false;
        if (!$this->isAuthenticated()) return false;

        if (!$group) $group = &$GLOBALS['request']->getGroup();
        return ($this->_level > WIKIAUTH_BOGO and $group->isMember(GROUP_ADMIN));
    }

    /** Name or IP for a signed user. UserName could come from a cookie e.g.
     */
    function getId () {
        return ( $this->UserName()
                 ? $this->UserName()
                 : $GLOBALS['request']->get('REMOTE_ADDR') );
    }

    /** Name for an authenticated user. No IP here.
     */
    function getAuthenticatedId() {
        return ( $this->isAuthenticated()
                 ? $this->_userid
                 : ''); //$GLOBALS['request']->get('REMOTE_ADDR') );
    }

    function hasAuthority ($require_level) {
        return $this->_level >= $require_level;
    }

    /* This is quite restrictive and not according the login description online. 
       Any word char (A-Za-z0-9_), " ", ".", "@" and "-"
       The backends may loosen or tighten this.
    */
    function isValidName ($userid = false) {
        if (!$userid) $userid = $this->_userid;
        if (!$userid) return false;
        if (FUSIONFORGE) {
            return true;
        }
        return preg_match("/^[\-\w\.@ ]+$/U", $userid) and strlen($userid) < 32;
    }

    /**
     * Called on an auth_args POST request, such as login, logout or signin.
     * TODO: Check BogoLogin users with empty password. (self-signed users)
     */
    function AuthCheck ($postargs) {
        // Normalize args, and extract.
        $keys = array('userid', 'passwd', 'require_level', 'login', 'logout',
                      'cancel');
        foreach ($keys as $key)
            $args[$key] = isset($postargs[$key]) ? $postargs[$key] : false;
        extract($args);
        $require_level = max(0, min(WIKIAUTH_ADMIN, (int)$require_level));

        if ($logout) { // Log out
	    if (LOGIN_LOG and is_writeable(LOGIN_LOG)) {
		global $request;
		$zone_offset = Request_AccessLogEntry::_zone_offset();
		$ncsa_time = date("d/M/Y:H:i:s", time());
		$entry = sprintf('%s - %s - [%s %s] "%s" %s - "%s" "%s"',
				 (string) $request->get('REMOTE_HOST'),
				 (string) $request->_user->_userid,
				 $ncsa_time, $zone_offset, 
				 "logout ".get_class($request->_user),
				 "401",
				 (string) $request->get('HTTP_REFERER'),
				 (string) $request->get('HTTP_USER_AGENT')
				 );
		if (($fp = fopen(LOGIN_LOG, "a"))) {
		    flock($fp, LOCK_EX);
		    fputs($fp, "$entry\n");
		    fclose($fp);
		}
		//error_log("$entry\n", 3, LOGIN_LOG);
	    }
            if (method_exists($GLOBALS['request']->_user, "logout")) { //_HttpAuthPassUser
          	$GLOBALS['request']->_user->logout();
            }
            $user = new _AnonUser();
            $user->_userid = '';
            $user->_level = WIKIAUTH_ANON;
            return $user; 
        } elseif ($cancel)
            return false;        // User hit cancel button.
        elseif (!$login && !$userid)
            return false;       // Nothing to do?

        if (!$this->isValidName($userid))
            return _("Invalid username.");;

        $authlevel = $this->checkPass($passwd === false ? '' : $passwd);

	if (LOGIN_LOG and is_writeable(LOGIN_LOG)) {
	    global $request;
	    $zone_offset = Request_AccessLogEntry::_zone_offset();
	    $ncsa_time = date("d/M/Y:H:i:s", time());
	    $manglepasswd = $passwd;
	    for ($i=0; $i<strlen($manglepasswd); $i++) {
		$c = substr($manglepasswd,$i,1);
		if (ord($c) < 32) $manglepasswd[$i] = "<";
		elseif ($c == '*') $manglepasswd[$i] = "*";
		elseif ($c == '?') $manglepasswd[$i] = "?";
		elseif ($c == '(') $manglepasswd[$i] = "(";
		elseif ($c == ')') $manglepasswd[$i] = ")";
		elseif ($c == "\\") $manglepasswd[$i] = "\\";
		elseif (ord($c) < 127) $manglepasswd[$i] = "x";
		elseif (ord($c) >= 127) $manglepasswd[$i] = ">";
	    }
            if ((DEBUG & _DEBUG_LOGIN) and $authlevel <= 0) $manglepasswd = $passwd;
	    $entry = sprintf('%s - %s - [%s %s] "%s" %s - "%s" "%s"',
			     $request->get('REMOTE_HOST'),
			     (string) $request->_user->_userid,
			     $ncsa_time, $zone_offset, 
			     "login $userid/$manglepasswd => $authlevel ".get_class($request->_user),
			     $authlevel > 0 ? "200" : "403",
			     (string) $request->get('HTTP_REFERER'),
			     (string) $request->get('HTTP_USER_AGENT')
			     );
	    if (($fp = fopen(LOGIN_LOG, "a"))) {
		flock($fp, LOCK_EX);
		fputs($fp, "$entry\n");
		fclose($fp);
	    }
	    //error_log("$entry\n", 3, LOGIN_LOG);
	}

        if ($authlevel <= 0) { // anon or forbidden
            if ($passwd)
                return _("Invalid password.");
            else
                return _("Invalid password or userid.");
        } elseif ($authlevel < $require_level) { // auth ok, but not enough 
            if (!empty($this->_current_method) and strtolower(get_class($this)) == '_passuser') 
            {
                // upgrade class
                $class = "_" . $this->_current_method . "PassUser";
                include_once("lib/WikiUser/".$this->_current_method.".php");
                $user = new $class($userid,$this->_prefs);
                if (!check_php_version(5))
                    eval("\$this = \$user;");
                // /*PHP5 patch*/$this = $user;
                $this->_level = $authlevel;
                return $user;
            }
            $this->_userid = $userid;
            $this->_level = $authlevel;
            return _("Insufficient permissions.");
        }

        // Successful login.
        //$user = $GLOBALS['request']->_user;
        if (!empty($this->_current_method) and 
            strtolower(get_class($this)) == '_passuser') 
        {
            // upgrade class
            $class = "_" . $this->_current_method . "PassUser";
            include_once("lib/WikiUser/".$this->_current_method.".php");
            $user = new $class($userid, $this->_prefs);
            if (!check_php_version(5))
                eval("\$this = \$user;");
            // /*PHP5 patch*/$this = $user;
            $user->_level = $authlevel;
            return $user;
        }
        $this->_userid = $userid;
        $this->_level = $authlevel;
        return $this;
    }

}

/**
 * Not authenticated in user, but he may be signed in. Basicly with view access only.
 * prefs are stored in cookies, but only the userid.
 */
class _AnonUser
extends _WikiUser
{
    var $_level = WIKIAUTH_ANON; 	// var in php-5.0.0RC1 deprecated

    /** Anon only gets to load and save prefs in a cookie, that's it.
     */
    function getPreferences() {
        global $request;

        if (empty($this->_prefs))
            $this->_prefs = new UserPreferences;
        $UserName = $this->UserName();

        // Try to read deprecated 1.3.x style cookies
        if ($cookie = $request->cookies->get_old(WIKI_NAME)) {
            if (! $unboxedcookie = $this->_prefs->retrieve($cookie)) {
                trigger_error(_("Empty Preferences or format of UserPreferences cookie not recognised.") 
                              . "\n"
                              . sprintf("%s='%s'", WIKI_NAME, $cookie)
                              . "\n"
                              . _("Default preferences will be used."),
                              E_USER_NOTICE);
            }
            /**
             * Only set if it matches the UserName who is
             * signing in or if this really is an Anon login (no
             * username). (Remember, _BogoUser and higher inherit this
             * function too!).
             */
            if (! $UserName || $UserName == @$unboxedcookie['userid']) {
                $updated = $this->_prefs->updatePrefs($unboxedcookie);
                //$this->_prefs = new UserPreferences($unboxedcookie);
                $UserName = @$unboxedcookie['userid'];
                if (is_string($UserName) and (substr($UserName,0,2) != 's:'))
                    $this->_userid = $UserName;
                else 
                    $UserName = false;    
            }
            // v1.3.8 policy: don't set PhpWiki cookies, only plaintext WIKI_ID cookies
            if (!headers_sent())
                $request->deleteCookieVar(WIKI_NAME);
        }
        // Try to read deprecated 1.3.4 style cookies
        if (! $UserName and ($cookie = $request->cookies->get_old("WIKI_PREF2"))) {
            if (! $unboxedcookie = $this->_prefs->retrieve($cookie)) {
                if (! $UserName || $UserName == $unboxedcookie['userid']) {
                    $updated = $this->_prefs->updatePrefs($unboxedcookie);
                    //$this->_prefs = new UserPreferences($unboxedcookie);
                    $UserName = $unboxedcookie['userid'];
                    if (is_string($UserName) and (substr($UserName,0,2) != 's:'))
                        $this->_userid = $UserName;
                    else 
                        $UserName = false;    
                }
                if (!headers_sent())
                    $request->deleteCookieVar("WIKI_PREF2");
            }
        }
        if (! $UserName ) {
            // Try reading userid from old PhpWiki cookie formats:
            if ($cookie = $request->cookies->get_old(getCookieName())) {
                if (is_string($cookie) and (substr($cookie,0,2) != 's:'))
                    $UserName = $cookie;
                elseif (is_array($cookie) and !empty($cookie['userid']))
                    $UserName = $cookie['userid'];
            }
            if (! $UserName and !headers_sent())
                $request->deleteCookieVar(getCookieName());
            else
                $this->_userid = $UserName;
        }

        // initializeTheme() needs at least an empty object
        /*
         if (empty($this->_prefs))
            $this->_prefs = new UserPreferences;
        */
        return $this->_prefs;
    }

    /** _AnonUser::setPreferences(): Save prefs in a cookie and session and update all global vars
     *
     * Allow for multiple wikis in same domain. Encode only the
     * _prefs array of the UserPreference object. Ideally the
     * prefs array should just be imploded into a single string or
     * something so it is completely human readable by the end
     * user. In that case stricter error checking will be needed
     * when loading the cookie.
     */
    function setPreferences($prefs, $id_only=false) {
        if (!is_object($prefs)) {
            if (is_object($this->_prefs)) {
                $updated = $this->_prefs->updatePrefs($prefs);
                $prefs =& $this->_prefs;
            } else {
                // update the prefs values from scratch. This could leed to unnecessary
                // side-effects: duplicate emailVerified, ...
                $this->_prefs = new UserPreferences($prefs);
                $updated = true;
            }
        } else {
            if (!isset($this->_prefs))
                $this->_prefs =& $prefs;
            else
                $updated = $this->_prefs->isChanged($prefs);
        }
        if ($updated) {
            if ($id_only and !headers_sent()) {
                global $request;
                // new 1.3.8 policy: no array cookies, only plain userid string as in 
                // the pre 1.3.x versions.
                // prefs should be stored besides the session in the homepagehandle or in a db.
                $request->setCookieVar(getCookieName(), $this->_userid,
                                       COOKIE_EXPIRATION_DAYS, COOKIE_DOMAIN);
                //$request->setCookieVar(WIKI_NAME, array('userid' => $prefs->get('userid')),
                //                       COOKIE_EXPIRATION_DAYS, COOKIE_DOMAIN);
            }
        }
        if (is_object($prefs)) {
            $packed = $prefs->store();
            $unpacked = $prefs->unpack($packed);
            if (count($unpacked)) {
                foreach (array('_method','_select','_update','_insert') as $param) {
            	    if (!empty($this->_prefs->{$param}))
            	        $prefs->{$param} = $this->_prefs->{$param};
                }
                $this->_prefs = $prefs;
            }
        }
        return $updated;
    }

    function userExists() {
        return true;
    }

    function checkPass($submitted_password) {
        return false;
        // this might happen on a old-style signin button.

        // By definition, the _AnonUser does not HAVE a password
        // (compared to _BogoUser, who has an EMPTY password).
        trigger_error("DEBUG: Warning: _AnonUser unexpectedly asked to checkPass()." . " "
                      . "Check isa(\$user, '_PassUser'), or: isa(\$user, '_AdminUser') etc. first." . " "
                      . "New subclasses of _WikiUser must override this function.");
        return false;
    }

}

/**
 * Helper class to finish the PassUser auth loop. 
 * This is added automatically to USER_AUTH_ORDER.
 */
class _ForbiddenUser
extends _AnonUser
{
    var $_level = WIKIAUTH_FORBIDDEN;

    function checkPass($submitted_password) {
        return WIKIAUTH_FORBIDDEN;
    }

    function userExists() {
        if ($this->_HomePagehandle) return true;
        return false;
    }
}

/**
 * Do NOT extend _BogoUser to other classes, for checkPass()
 * security. (In case of defects in code logic of the new class!)
 * The intermediate step between anon and passuser.
 * We also have the _BogoLoginPassUser class with stricter 
 * password checking, which fits into the auth loop.
 * Note: This class is not called anymore by WikiUser()
 */
class _BogoUser
extends _AnonUser
{
    function userExists() {
        if (isWikiWord($this->_userid)) {
            $this->_level = WIKIAUTH_BOGO;
            return true;
        } else {
            $this->_level = WIKIAUTH_ANON;
            return false;
        }
    }

    function checkPass($submitted_password) {
        // By definition, BogoUser has an empty password.
        $this->userExists();
        return $this->_level;
    }
}

class _PassUser
extends _AnonUser
/**
 * Called if ALLOW_USER_PASSWORDS and Anon and Bogo failed.
 *
 * The classes for all subsequent auth methods extend from this class. 
 * This handles the auth method type dispatcher according $USER_AUTH_ORDER, 
 * the three auth method policies first-only, strict and stacked
 * and the two methods for prefs: homepage or database, 
 * if $DBAuthParams['pref_select'] is defined.
 *
 * Default is PersonalPage auth and prefs.
 * 
 * @author: Reini Urban
 * @tables: pref
 */
{
    var $_auth_dbi, $_prefs;
    var $_current_method, $_current_index;

    // check and prepare the auth and pref methods only once
    function _PassUser($UserName='', $prefs=false) {
        //global $DBAuthParams, $DBParams;
        if ($UserName) {
            /*if (!$this->isValidName($UserName))
                return false;*/
            $this->_userid = $UserName;
            if ($this->hasHomePage())
                $this->_HomePagehandle = $GLOBALS['request']->getPage($this->_userid);
        }
        $this->_authmethod = substr(get_class($this),1,-8);
        if ($this->_authmethod == 'a') $this->_authmethod = 'admin';

        // Check the configured Prefs methods
        $dbi = $this->getAuthDbh();
        $dbh = $GLOBALS['request']->getDbh();
        if ( $dbi 
             and !$dbh->readonly 
             and !isset($this->_prefs->_select) 
             and $dbh->getAuthParam('pref_select')) 
        {
            if (!$this->_prefs) {
            	$this->_prefs = new UserPreferences();
            	$need_pref = true;
            }
            $this->_prefs->_method = $dbh->getParam('dbtype');
            $this->_prefs->_select = $this->prepare($dbh->getAuthParam('pref_select'), "userid");
            // read-only prefs?
            if ( !isset($this->_prefs->_update) and $dbh->getAuthParam('pref_update')) {
                $this->_prefs->_update = $this->prepare($dbh->getAuthParam('pref_update'), 
                                                        array("userid", "pref_blob"));
            }
        } else {
            if (!$this->_prefs) {
            	$this->_prefs = new UserPreferences();
            	$need_pref = true;
            }
            $this->_prefs->_method = 'HomePage';
        }
        
        if (! $this->_prefs or isset($need_pref) ) {
            if ($prefs) $this->_prefs = $prefs;
            else $this->getPreferences();
        }
        
        // Upgrade to the next parent _PassUser class. Avoid recursion.
        if ( strtolower(get_class($this)) === '_passuser' ) {
            //auth policy: Check the order of the configured auth methods
            // 1. first-only: Upgrade the class here in the constructor
            // 2. old:       ignore USER_AUTH_ORDER and try to use all available methods as 
            ///              in the previous PhpWiki releases (slow)
            // 3. strict:    upgrade the class after checking the user existance in userExists()
            // 4. stacked:   upgrade the class after the password verification in checkPass()
            // Methods: PersonalPage, HttpAuth, DB, Ldap, Imap, File
            //if (!defined('USER_AUTH_POLICY')) define('USER_AUTH_POLICY','old');
            if (defined('USER_AUTH_POLICY')) {
                // policy 1: only pre-define one method for all users
                if (USER_AUTH_POLICY === 'first-only') {
                    $class = $this->nextClass();
                    return new $class($UserName,$this->_prefs);
                }
                // Use the default behaviour from the previous versions:
                elseif (USER_AUTH_POLICY === 'old') {
                    // Default: try to be smart
                    // On php5 we can directly return and upgrade the Object,
                    // before we have to upgrade it manually.
                    if (!empty($GLOBALS['PHP_AUTH_USER']) or !empty($_SERVER['REMOTE_USER'])) {
                        include_once("lib/WikiUser/HttpAuth.php");
                        if (check_php_version(5))
                            return new _HttpAuthPassUser($UserName,$this->_prefs);
                        else {
                            $user = new _HttpAuthPassUser($UserName,$this->_prefs);
                            eval("\$this = \$user;");
                            // /*PHP5 patch*/$this = $user;
                            return $user;
                        }
                    } elseif (in_array('Db', $dbh->getAuthParam('USER_AUTH_ORDER')) and
                              $dbh->getAuthParam('auth_check') and
                              ($dbh->getAuthParam('auth_dsn') or $dbh->getParam('dsn'))) {
                        if (check_php_version(5))
                            return new _DbPassUser($UserName,$this->_prefs);
                        else {
                            $user = new _DbPassUser($UserName,$this->_prefs);
                            eval("\$this = \$user;");
                            // /*PHP5 patch*/$this = $user;
                            return $user;
                        }
                    } elseif (in_array('LDAP', $dbh->getAuthParam('USER_AUTH_ORDER')) and
                              defined('LDAP_AUTH_HOST') and defined('LDAP_BASE_DN') and 
                              function_exists('ldap_connect')) {
                        include_once("lib/WikiUser/LDAP.php");
                        if (check_php_version(5))
                            return new _LDAPPassUser($UserName,$this->_prefs);
                        else {
                            $user = new _LDAPPassUser($UserName,$this->_prefs);
                            eval("\$this = \$user;");
                            // /*PHP5 patch*/$this = $user;
                            return $user;
                        }
                    } elseif (in_array('IMAP', $dbh->getAuthParam('USER_AUTH_ORDER')) and
                              defined('IMAP_AUTH_HOST') and function_exists('imap_open')) {
                        include_once("lib/WikiUser/IMAP.php");
                        if (check_php_version(5))
                            return new _IMAPPassUser($UserName,$this->_prefs);
                        else {
                            $user = new _IMAPPassUser($UserName,$this->_prefs);
                            eval("\$this = \$user;");
                            // /*PHP5 patch*/$this = $user;
                            return $user;
                        }
                    } elseif (in_array('File', $dbh->getAuthParam('USER_AUTH_ORDER')) and
                              defined('AUTH_USER_FILE') and file_exists(AUTH_USER_FILE)) {
                        include_once("lib/WikiUser/File.php");
                        if (check_php_version(5))
                            return new _FilePassUser($UserName, $this->_prefs);
                        else {
                            $user = new _FilePassUser($UserName, $this->_prefs);
                            eval("\$this = \$user;");
                            // /*PHP5 patch*/$this = $user;
                            return $user;
                        }
                    } else {
                        include_once("lib/WikiUser/PersonalPage.php");
                        if (check_php_version(5))
                            return new _PersonalPagePassUser($UserName,$this->_prefs);
                        else {
                            $user = new _PersonalPagePassUser($UserName,$this->_prefs);
                            eval("\$this = \$user;");
                            // /*PHP5 patch*/$this = $user;
                            return $user;
                        }
                    }
                }
                else 
                    // else use the page methods defined in _PassUser.
                    return $this;
            }
        }
    }

    function getAuthDbh () {
        global $request; //, $DBParams, $DBAuthParams;

        $dbh = $request->getDbh();
        // session restauration doesn't re-connect to the database automatically, 
        // so dirty it here, to force a reconnect.
        if (isset($this->_auth_dbi)) {
            if (($dbh->getParam('dbtype') == 'SQL') and empty($this->_auth_dbi->connection))
                unset($this->_auth_dbi);
            if (($dbh->getParam('dbtype') == 'ADODB') and empty($this->_auth_dbi->_connectionID))
                unset($this->_auth_dbi);
        }
        if (empty($this->_auth_dbi)) {
            if ($dbh->getParam('dbtype') != 'SQL' 
                and $dbh->getParam('dbtype') != 'ADODB'
                and $dbh->getParam('dbtype') != 'PDO')
                return false;
            if (empty($GLOBALS['DBAuthParams']))
                return false;
            if (!$dbh->getAuthParam('auth_dsn')) {
                $dbh = $request->getDbh(); // use phpwiki database 
            } elseif ($dbh->getAuthParam('auth_dsn') == $dbh->getParam('dsn')) {
                $dbh = $request->getDbh(); // same phpwiki database 
            } else { // use another external database handle. needs PHP >= 4.1
                $local_params = array_merge($GLOBALS['DBParams'],$GLOBALS['DBAuthParams']);
                $local_params['dsn'] = $local_params['auth_dsn'];
                $dbh = WikiDB::open($local_params);
            }       
            $this->_auth_dbi =& $dbh->_backend->_dbh;    
        }
        return $this->_auth_dbi;
    }

    function _normalize_stmt_var($var, $oldstyle = false) {
        static $valid_variables = array('userid','password','pref_blob','groupname');
        // old-style: "'$userid'"
        // new-style: '"\$userid"' or just "userid"
        $new = str_replace(array("'",'"','\$','$'),'',$var);
        if (!in_array($new, $valid_variables)) {
            trigger_error("Unknown DBAuthParam statement variable: ". $new, E_USER_ERROR);
            return false;
        }
        return !$oldstyle ? "'$".$new."'" : '\$'.$new;
    }

    // TODO: use it again for the auth and member tables
    // sprintfstyle vs prepare style: %s or ?
    //   multiple vars should be executed via prepare(?,?)+execute, 
    //   single vars with execute(sprintf(quote(var)))
    // help with position independency
    function prepare ($stmt, $variables, $oldstyle = false, $sprintfstyle = true) {
        global $request;
        $dbi = $request->getDbh();
        $this->getAuthDbh();
        // "'\$userid"' => %s
        // variables can be old-style: '"\$userid"' or new-style: "'$userid'" or just "userid"
        // old-style strings don't survive pear/Config/IniConfig treatment, that's why we changed it.
        $new = array();
        if (is_array($variables)) {
            //$sprintfstyle = false;
            for ($i=0; $i < count($variables); $i++) { 
                $var = $this->_normalize_stmt_var($variables[$i], $oldstyle);
                if (!$var)
                    trigger_error(sprintf("DbAuthParams: Undefined or empty statement variable %s in %s",
                                          $variables[$i], $stmt), E_USER_WARNING);
                $variables[$i] = $var;
                if (!$var) $new[] = '';
                else {
                    $s = "%" . ($i+1) . "s";	
                    $new[] = $sprintfstyle ? $s : "?";
                }
            }
        } else {
            $var = $this->_normalize_stmt_var($variables, $oldstyle);
            if (!$var)
                trigger_error(sprintf("DbAuthParams: Undefined or empty statement variable %s in %s",
                                      $variables, $stmt), E_USER_WARNING);
            $variables = $var;
            if (!$var) $new = ''; 
            else $new = $sprintfstyle ? '%s' : "?"; 
        }
        $prefix = $dbi->getParam('prefix');
        // probably prefix table names if in same database
        if ($prefix and isset($this->_auth_dbi) and isset($dbi->_backend->_dbh) and 
            ($dbi->getAuthParam('auth_dsn') and $dbi->getParam('dsn') == $dbi->getAuthParam('auth_dsn')))
        {
            if (!stristr($stmt, $prefix)) {
            	$oldstmt = $stmt;
                $stmt = str_replace(array(" user "," pref "," member "),
                                    array(" ".$prefix."user ",
                                          " ".$prefix."pref ",
                                          " ".$prefix."member "), $stmt);
                //Do it automatically for the lazy admin? Esp. on sf.net it's nice to have
                trigger_error("Need to prefix the DBAUTH tablename in config/config.ini:\n  $oldstmt \n=> $stmt",
                              E_USER_WARNING);
            }
        }
        // Preparate the SELECT statement, for ADODB and PearDB (MDB not).
        // Simple sprintf-style.
        $new_stmt = str_replace($variables, $new, $stmt);
        if ($new_stmt == $stmt) {
            if ($oldstyle) {
                trigger_error(sprintf("DbAuthParams: Invalid statement in %s",
                                  $stmt), E_USER_WARNING);
            } else {
                trigger_error(sprintf("DbAuthParams: Old statement quoting style in %s",
                                  $stmt), E_USER_WARNING);
                $new_stmt = $this->prepare($stmt, $variables, 'oldstyle');
            }
        }
        return $new_stmt;
    }

    function getPreferences() {
        if (!empty($this->_prefs->_method)) {
            if ($this->_prefs->_method == 'ADODB') {
                // FIXME: strange why this should be needed...
                include_once("lib/WikiUser/Db.php");
                include_once("lib/WikiUser/AdoDb.php");
                return _AdoDbPassUser::getPreferences();
            } elseif ($this->_prefs->_method == 'SQL') {
                include_once("lib/WikiUser/Db.php");
                include_once("lib/WikiUser/PearDb.php");
                return _PearDbPassUser::getPreferences();
            } elseif ($this->_prefs->_method == 'PDO') {
                include_once("lib/WikiUser/Db.php");
                include_once("lib/WikiUser/PdoDb.php");
                return _PdoDbPassUser::getPreferences();
            }
        }

        // We don't necessarily have to read the cookie first. Since
        // the user has a password, the prefs stored in the homepage
        // cannot be arbitrarily altered by other Bogo users.
        _AnonUser::getPreferences();
        // User may have deleted cookie, retrieve from his
        // PersonalPage if there is one.
        if (!empty($this->_HomePagehandle)) {
            if ($restored_from_page = $this->_prefs->retrieve
                ($this->_HomePagehandle->get('pref'))) {
                $updated = $this->_prefs->updatePrefs($restored_from_page,'init');
                //$this->_prefs = new UserPreferences($restored_from_page);
                return $this->_prefs;
            }
        }
        return $this->_prefs;
    }

    function setPreferences($prefs, $id_only=false) {
        if (!empty($this->_prefs->_method)) {
            if ($this->_prefs->_method == 'ADODB') {
                // FIXME: strange why this should be needed...
                include_once("lib/WikiUser/Db.php");
                include_once("lib/WikiUser/AdoDb.php");
                return _AdoDbPassUser::setPreferences($prefs, $id_only);
            }
            elseif ($this->_prefs->_method == 'SQL') {
                include_once("lib/WikiUser/Db.php");
                include_once("lib/WikiUser/PearDb.php");
                return _PearDbPassUser::setPreferences($prefs, $id_only);
            }
            elseif ($this->_prefs->_method == 'PDO') {
                include_once("lib/WikiUser/Db.php");
                include_once("lib/WikiUser/PdoDb.php");
                return _PdoDbPassUser::setPreferences($prefs, $id_only);
            }
        }
        if ($updated = _AnonUser::setPreferences($prefs, $id_only)) {
            // Encode only the _prefs array of the UserPreference object
	    // If no DB method exists to store the prefs we must store it in the page, not in the cookies.
            if (empty($this->_HomePagehandle)) {
                $this->_HomePagehandle = $GLOBALS['request']->getPage($this->_userid);
	    }
            if (! $this->_HomePagehandle->exists() ) {
                $this->createHomePage();
            }
	    if (!empty($this->_HomePagehandle) and !$id_only) {
                $this->_HomePagehandle->set('pref', $this->_prefs->store());
            }
        }
        return $updated;
    }

    function mayChangePass() {
        return true;
    }

    //The default method is getting the password from prefs. 
    // child methods obtain $stored_password from external auth.
    function userExists() {
        //if ($this->_HomePagehandle) return true;
        if (strtolower(get_class($this)) == "_passuser") {
            $class = $this->nextClass();
            $user = new $class($this->_userid, $this->_prefs);
        } else {
            $user = $this;
        }
        /* new user => false does not return false, but the _userid is empty then */
        while ($user and $user->_userid) {
            if (!check_php_version(5))
                eval("\$this = \$user;");
            $user = UpgradeUser($this, $user);
            if ($user->userExists()) {
                $user = UpgradeUser($this, $user);
                return true;
            }
            // prevent endless loop. does this work on all PHP's?
            // it just has to set the classname, what it correctly does.
            $class = $user->nextClass();
            if ($class == "_ForbiddenPassUser")
                return false;
        }
        return false;
    }

    //The default method is getting the password from prefs. 
    // child methods obtain $stored_password from external auth.
    function checkPass($submitted_password) {
        $stored_password = $this->_prefs->get('passwd');
        if ($this->_checkPass($submitted_password, $stored_password)) {
            $this->_level = WIKIAUTH_USER;
            return $this->_level;
        } else {
            if ((USER_AUTH_POLICY === 'strict') and $this->userExists()) {
                $this->_level = WIKIAUTH_FORBIDDEN;
                return $this->_level;
            }
            return $this->_tryNextPass($submitted_password);
        }
    }


    function _checkPassLength($submitted_password) {
        if (strlen($submitted_password) < PASSWORD_LENGTH_MINIMUM) {
            trigger_error(_("The length of the password is shorter than the system policy allows."));
            return false;
        }
        return true;
    }

    /**
     * The basic password checker for all PassUser objects.
     * Uses global ENCRYPTED_PASSWD and PASSWORD_LENGTH_MINIMUM.
     * Empty passwords are always false!
     * PASSWORD_LENGTH_MINIMUM is enforced here and in the preference set method.
     * @see UserPreferences::set
     *
     * DBPassUser password's have their own crypt definition.
     * That's why DBPassUser::checkPass() doesn't call this method, if 
     * the db password method is 'plain', which means that the DB SQL 
     * statement just returns 1 or 0. To use CRYPT() or PASSWORD() and 
     * don't store plain passwords in the DB.
     * 
     * TODO: remove crypt() function check from config.php:396 ??
     */
    function _checkPass($submitted_password, $stored_password) {
        if (!empty($submitted_password)) {
            // This works only on plaintext passwords.
            if (!ENCRYPTED_PASSWD and (strlen($stored_password) < PASSWORD_LENGTH_MINIMUM)) {
                // With the EditMetaData plugin
                trigger_error(_("The length of the stored password is shorter than the system policy allows. Sorry, you cannot login.\n You have to ask the System Administrator to reset your password."));
                return false;
            }
            if (!$this->_checkPassLength($submitted_password)) {
                return false;
            }
            if (ENCRYPTED_PASSWD) {
                // Verify against encrypted password.
                if (function_exists('crypt')) {
                    if (crypt($submitted_password, $stored_password) == $stored_password )
                        return true; // matches encrypted password
                    else
                        return false;
                }
                else {
                    trigger_error(_("The crypt function is not available in this version of PHP.") . " "
                                  . _("Please set ENCRYPTED_PASSWD to false in config/config.ini and probably change ADMIN_PASSWD."),
                                  E_USER_WARNING);
                    return false;
                }
            }
            else {
                // Verify against cleartext password.
                if ($submitted_password == $stored_password)
                    return true;
                else {
                    // Check whether we forgot to enable ENCRYPTED_PASSWD
                    if (function_exists('crypt')) {
                        if (crypt($submitted_password, $stored_password) == $stored_password) {
                            trigger_error(_("Please set ENCRYPTED_PASSWD to true in config/config.ini."),
                                          E_USER_WARNING);
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /** The default method is storing the password in prefs. 
     *  Child methods (DB, File) may store in external auth also, but this 
     *  must be explicitly enabled.
     *  This may be called by plugin/UserPreferences or by ->SetPreferences()
     */
    function changePass($submitted_password) {
        $stored_password = $this->_prefs->get('passwd');
        // check if authenticated
        if (!$this->isAuthenticated()) return false;
        if (ENCRYPTED_PASSWD) {
            $submitted_password = crypt($submitted_password);
        }
        // check other restrictions, with side-effects only.
        $result = $this->_checkPass($submitted_password, $stored_password);
        if ($stored_password != $submitted_password) {
            $this->_prefs->set('passwd', $submitted_password);
            //update the storage (session, homepage, ...)
            $this->SetPreferences($this->_prefs);
            return true;
        }
        //Todo: return an error msg to the caller what failed? 
        // same password or no privilege
        return ENCRYPTED_PASSWD ? true : false;
    }

    function _tryNextPass($submitted_password) {
        if (DEBUG & _DEBUG_LOGIN) {
            $class = strtolower(get_class($this));
            if (substr($class,-10) == "dbpassuser") $class = "_dbpassuser";
            $GLOBALS['USER_AUTH_ERROR'][$class] = 'wrongpass';
        }
        if (USER_AUTH_POLICY === 'strict') {
            $class = $this->nextClass();
            if ($user = new $class($this->_userid,$this->_prefs)) {
                if ($user->userExists()) {
                    return $user->checkPass($submitted_password);
                }
            }
        }
        if (USER_AUTH_POLICY === 'stacked' or USER_AUTH_POLICY === 'old') {
            $class = $this->nextClass();
            if ($user = new $class($this->_userid,$this->_prefs))
                return $user->checkPass($submitted_password);
        }
        return $this->_level;
    }

    function _tryNextUser() {
        if (DEBUG & _DEBUG_LOGIN) {
            $class = strtolower(get_class($this));
            if (substr($class,-10) == "dbpassuser") $class = "_dbpassuser";
            $GLOBALS['USER_AUTH_ERROR'][$class] = 'nosuchuser';
        }
        if (USER_AUTH_POLICY === 'strict'
	    or USER_AUTH_POLICY === 'stacked') {
            $class = $this->nextClass();
            while ($user = new $class($this->_userid, $this->_prefs)) {
                if (!check_php_version(5))
                    eval("\$this = \$user;");
	        $user = UpgradeUser($this, $user);
                if ($user->userExists()) {
                    $user = UpgradeUser($this, $user);
                    return true;
                }
                if ($class == "_ForbiddenPassUser") return false;
                $class = $this->nextClass();
            }
        }
        return false;
    }

}

/**
 * Insert more auth classes here...
 * For example a customized db class for another db connection 
 * or a socket-based auth server.
 *
 */


/**
 * For security, this class should not be extended. Instead, extend
 * from _PassUser (think of this as unix "root").
 *
 * FIXME: This should be a singleton class. Only ADMIN_USER may be of class AdminUser!
 * Other members of the Administrators group must raise their level otherwise somehow.
 * Currently every member is a AdminUser, which will not work for the various 
 * storage methods.
 */
class _AdminUser
extends _PassUser
{
    function mayChangePass() {
        return false;
    }
    function checkPass($submitted_password) {
    	if ($this->_userid == ADMIN_USER)
            $stored_password = ADMIN_PASSWD;
        else {
            // Should not happen! Only ADMIN_USER should use this class.
            // return $this->_tryNextPass($submitted_password); // ???
            // TODO: safety check if really member of the ADMIN group?
            $stored_password = $this->_pref->get('passwd');
        }
        if ($this->_checkPass($submitted_password, $stored_password)) {
            $this->_level = WIKIAUTH_ADMIN;
            if (!empty($GLOBALS['HTTP_SERVER_VARS']['PHP_AUTH_USER']) and class_exists("_HttpAuthPassUser")) {
                // fake http auth
                _HttpAuthPassUser::_fake_auth($this->_userid, $submitted_password);
            }
            return $this->_level;
        } else {
            return $this->_tryNextPass($submitted_password);
            //$this->_level = WIKIAUTH_ANON;
            //return $this->_level;
        }
    }

    function storePass($submitted_password) {
    	if ($this->_userid == ADMIN_USER)
            return false;
        else {
            // should not happen! only ADMIN_USER should use this class.
            return parent::storePass($submitted_password);
        }
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
/**
 * Various data classes for the preference types, 
 * to support get, set, sanify (range checking, ...)
 * update() will do the neccessary side-effects if a 
 * setting gets changed (theme, language, ...)
*/

class _UserPreference
{
    var $default_value;

    function _UserPreference ($default_value) {
        $this->default_value = $default_value;
    }

    function sanify ($value) {
        return (string)$value;
    }

    function get ($name) {
    	if (isset($this->{$name}))
	    return $this->{$name};
    	else 
            return $this->default_value;
    }

    function getraw ($name) {
    	if (!empty($this->{$name}))
	    return $this->{$name};
    }

    // stores the value as $this->$name, and not as $this->value (clever?)
    function set ($name, $value) {
    	$return = 0;
    	$value = $this->sanify($value);
	if ($this->get($name) != $value) {
	    $this->update($value);
	    $return = 1;
	}
	if ($value != $this->default_value) {
	    $this->{$name} = $value;
        } else {
            unset($this->{$name});
        }
        return $return;
    }

    // default: no side-effects 
    function update ($value) {
    	;
    }
}

class _UserPreference_numeric
extends _UserPreference
{
    function _UserPreference_numeric ($default, $minval = false,
                                      $maxval = false) {
        $this->_UserPreference((double)$default);
        $this->_minval = (double)$minval;
        $this->_maxval = (double)$maxval;
    }

    function sanify ($value) {
        $value = (double)$value;
        if ($this->_minval !== false && $value < $this->_minval)
            $value = $this->_minval;
        if ($this->_maxval !== false && $value > $this->_maxval)
            $value = $this->_maxval;
        return $value;
    }
}

class _UserPreference_int
extends _UserPreference_numeric
{
    function _UserPreference_int ($default, $minval = false, $maxval = false) {
        $this->_UserPreference_numeric((int)$default, (int)$minval, (int)$maxval);
    }

    function sanify ($value) {
        return (int)parent::sanify((int)$value);
    }
}

class _UserPreference_bool
extends _UserPreference
{
    function _UserPreference_bool ($default = false) {
        $this->_UserPreference((bool)$default);
    }

    function sanify ($value) {
        if (is_array($value)) {
            /* This allows for constructs like:
             *
             *   <input type="hidden" name="pref[boolPref][]" value="0" />
             *   <input type="checkbox" name="pref[boolPref][]" value="1" />
             *
             * (If the checkbox is not checked, only the hidden input
             * gets sent. If the checkbox is sent, both inputs get
             * sent.)
             */
            foreach ($value as $val) {
                if ($val)
                    return true;
            }
            return false;
        }
        return (bool) $value;
    }
}

class _UserPreference_language
extends _UserPreference
{
    function _UserPreference_language ($default = DEFAULT_LANGUAGE) {
        $this->_UserPreference($default);
    }

    // FIXME: check for valid locale
    function sanify ($value) {
        // Revert to DEFAULT_LANGUAGE if user does not specify
        // language in UserPreferences or chooses <system language>.
        if ($value == '' or empty($value))
            $value = DEFAULT_LANGUAGE;

        return (string) $value;
    }
    
    function update ($newvalue) {
        if (! $this->_init ) {
            // invalidate etag to force fresh output
            $GLOBALS['request']->setValidators(array('%mtime' => false));
            update_locale($newvalue ? $newvalue : $GLOBALS['LANG']);
        }
    }
}

class _UserPreference_theme
extends _UserPreference
{
    function _UserPreference_theme ($default = THEME) {
        $this->_UserPreference($default);
    }

    function sanify ($value) {
        if (!empty($value) and FindFile($this->_themefile($value)))
            return $value;
        return $this->default_value;
    }

    function update ($newvalue) {
        global $WikiTheme;
        // invalidate etag to force fresh output
        if (! $this->_init )
            $GLOBALS['request']->setValidators(array('%mtime' => false));
        if ($newvalue)
            include_once($this->_themefile($newvalue));
        if (empty($WikiTheme))
            include_once($this->_themefile(THEME));
    }

    function _themefile ($theme) {
        return "themes/$theme/themeinfo.php";
    }
}

class _UserPreference_notify
extends _UserPreference
{
    function sanify ($value) {
    	if (!empty($value))
            return $value;
        else
            return $this->default_value;
    }

    /** update to global user prefs: side-effect on set notify changes
     * use a global_data notify hash:
     * notify = array('pagematch' => array(userid => ('email' => mail, 
     *                                                'verified' => 0|1),
     *                                     ...),
     *                ...);
     */
    function update ($value) {
    	if (!empty($this->_init)) return;
        $dbh = $GLOBALS['request']->getDbh();
        $notify = $dbh->get('notify');
        if (empty($notify))
            $data = array();
        else 
            $data =& $notify;
        // expand to existing pages only or store matches?
        // for now we store (glob-style) matches which is easier for the user
        $pages = $this->_page_split($value);
        // Limitation: only current user.
        $user = $GLOBALS['request']->getUser();
        if (!$user or !method_exists($user,'UserName')) return;
        // This fails with php5 and a WIKI_ID cookie:
        $userid = $user->UserName();
        $email  = $user->_prefs->get('email');
        $verified = $user->_prefs->_prefs['email']->getraw('emailVerified');
        // check existing notify hash and possibly delete pages for email
        if (!empty($data)) {
            foreach ($data as $page => $users) {
                if (isset($data[$page][$userid]) and !in_array($page, $pages)) {
                    unset($data[$page][$userid]);
                }
                if (count($data[$page]) == 0)
                    unset($data[$page]);
            }
        }
        // add the new pages
        if (!empty($pages)) {
            foreach ($pages as $page) {
                if (!isset($data[$page]))
                    $data[$page] = array();
                if (!isset($data[$page][$userid])) {
                    // should we really store the verification notice here or 
                    // check it dynamically at every page->save?
                    if ($verified) {
                        $data[$page][$userid] = array('email' => $email,
                                                      'verified' => $verified);
                    } else {
                        $data[$page][$userid] = array('email' => $email);
                    }
                }
            }
        }
        // store users changes
        $dbh->set('notify',$data);
    }

    /** split the user-given comma or whitespace delimited pagenames
     *  to array
     */
    function _page_split($value) {
        return preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
    }
}

class _UserPreference_email
extends _UserPreference
{
    function get($name) {
        // get e-mail address from FusionForge
        if (FUSIONFORGE && session_loggedin()) {
            $user = session_get_user();
            return $user->getEmail();
        } else {
            parent::get($name);
        }
    }

    function sanify($value) {
        // e-mail address is already checked by FusionForge
        if (FUSIONFORGE) return $value;
        // check for valid email address
        if ($this->get('email') == $value and $this->getraw('emailVerified'))
            return $value;
        // hack!
        if ($value == 1 or $value === true)
            return $value;
        list($ok,$msg) = ValidateMail($value,'noconnect');
        if ($ok) {
            return $value;
        } else {
            trigger_error("E-mail Validation Error: ".$msg, E_USER_WARNING);
            return $this->default_value;
        }
    }
    
    /** Side-effect on email changes:
     * Send a verification mail or for now just a notification email.
     * For true verification (value = 2), we'd need a mailserver hook.
     */
    function update($value) {
        // e-mail address is already checked by FusionForge
        if (FUSIONFORGE) return $value;
    	if (!empty($this->_init)) return;
        $verified = $this->getraw('emailVerified');
        // hack!
        if (($value == 1 or $value === true) and $verified)
            return;
        if (!empty($value) and !$verified) {
            list($ok,$msg) = ValidateMail($value);
            if ($ok and mail($value,"[".WIKI_NAME ."] "._("Email Verification"),
                     sprintf(_("Welcome to %s!\nYour email account is verified and\nwill be used to send page change notifications.\nSee %s"),
                             WIKI_NAME, WikiURL($GLOBALS['request']->getArg('pagename'),'',true)))) {
                $this->set('emailVerified',1);
            } else {
            	trigger_error($msg, E_USER_WARNING);
            }
        }
    }
}

/** Check for valid email address
    fixed version from http://www.zend.com/zend/spotlight/ev12apr.php
    Note: too strict, Bug #1053681
 */
function ValidateMail($email, $noconnect=false) {
    global $EMailHosts;
    $HTTP_HOST = $GLOBALS['request']->get('HTTP_HOST');

    // if this check is too strict (like invalid mail addresses in a local network only)
    // uncomment the following line:
    //return array(true,"not validated");
    // see http://sourceforge.net/tracker/index.php?func=detail&aid=1053681&group_id=6121&atid=106121

    $result = array();

    // This is Paul Warren's (pdw@ex-parrot.com) monster regex for RFC822
    // addresses, from the Perl module Mail::RFC822::Address, reduced to
    // accept single RFC822 addresses without comments only. (The original
    // accepts groups and properly commented addresses also.)
    $lwsp = "(?:(?:\\r\\n)?[ \\t])";

    $specials = '()<>@,;:\\\\".\\[\\]';
    $controls = '\\000-\\031';

    $dtext = "[^\\[\\]\\r\\\\]";
    $domain_literal = "\\[(?:$dtext|\\\\.)*\\]$lwsp*";

    $quoted_string = "\"(?:[^\\\"\\r\\\\]|\\\\.|$lwsp)*\"$lwsp*";

    $atom = "[^$specials $controls]+(?:$lwsp+|\\Z|(?=[\\[\"$specials]))";
    $word = "(?:$atom|$quoted_string)";
    $localpart = "$word(?:\\.$lwsp*$word)*";

    $sub_domain = "(?:$atom|$domain_literal)";
    $domain = "$sub_domain(?:\\.$lwsp*$sub_domain)*";

    $addr_spec = "$localpart\@$lwsp*$domain";

    $phrase = "$word*";
    $route = "(?:\@$domain(?:,\@$lwsp*$domain)*:$lwsp*)";
    $route_addr = "\\<$lwsp*$route?$addr_spec\\>$lwsp*";
    $mailbox = "(?:$addr_spec|$phrase$route_addr)";

    $rfc822re = "/$lwsp*$mailbox/";
    unset($domain, $route_addr, $route, $phrase, $addr_spec, $sub_domain, $localpart, 
          $atom, $word, $quoted_string);
    unset($dtext, $controls, $specials, $lwsp, $domain_literal);

    if (!preg_match($rfc822re, $email)) {
        $result[0] = false;
        $result[1] = sprintf(_("E-mail address '%s' is not properly formatted"), $email);
        return $result;
    }
    if ($noconnect)
      return array(true, sprintf(_("E-mail address '%s' is properly formatted"), $email));

    list ( $Username, $Domain ) = explode("@", $email);
    //Todo: getmxrr workaround on windows or manual input field to verify it manually
    if (!isWindows() and getmxrr($Domain, $MXHost)) { // avoid warning on Windows. 
        $ConnectAddress = $MXHost[0];
    } else {
        $ConnectAddress = $Domain;
	if (isset($EMailHosts[ $Domain ])) {
            $ConnectAddress = $EMailHosts[ $Domain ];
        }
    }
    $Connect = @fsockopen ( $ConnectAddress, 25 );
    if ($Connect) {
        if (ereg("^220", $Out = fgets($Connect, 1024))) {
            fputs ($Connect, "HELO $HTTP_HOST\r\n");
            $Out = fgets ( $Connect, 1024 );
            fputs ($Connect, "MAIL FROM: <".$email.">\r\n");
            $From = fgets ( $Connect, 1024 );
            fputs ($Connect, "RCPT TO: <".$email.">\r\n");
            $To = fgets ($Connect, 1024);
            fputs ($Connect, "QUIT\r\n");
            fclose($Connect);
            if (!ereg ("^250", $From)) {
                $result[0]=false;
                $result[1]="Server rejected address: ". $From;
                return $result;
            }
            if (!ereg ( "^250", $To )) {
                $result[0]=false;
                $result[1]="Server rejected address: ". $To;
                return $result;
            }
        } else {
            $result[0] = false;
            $result[1] = "No response from server";
            return $result;
          }
    }  else {
        $result[0]=false;
        $result[1]="Cannot connect e-mail server.";
        return $result;
    }
    $result[0]=true;
    $result[1]="E-mail address '$email' appears to be valid.";
    return $result;
} // end of function 

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * UserPreferences
 * 
 * This object holds the $request->_prefs subobjects.
 * A simple packed array of non-default values get's stored as cookie,
 * homepage, or database, which are converted to the array of 
 * ->_prefs objects.
 * We don't store the objects, because otherwise we will
 * not be able to upgrade any subobject. And it's a waste of space also.
 *
 */
class UserPreferences
{
    var $notifyPagesAll;
	
    function UserPreferences($saved_prefs = false) {
        // userid stored too, to ensure the prefs are being loaded for
        // the correct (currently signing in) userid if stored in a
        // cookie.
        // Update: for db prefs we disallow passwd. 
        // userid is needed for pref reflexion. current pref must know its username, 
        // if some app needs prefs from different users, different from current user.
        $this->_prefs
            = array(
                    'userid'        => new _UserPreference(''),
                    'passwd'        => new _UserPreference(''),
                    'autologin'     => new _UserPreference_bool(),
                    //'emailVerified' => new _UserPreference_emailVerified(), 
                    //fixed: store emailVerified as email parameter, 1.3.8
                    'email'         => new _UserPreference_email(''),
                    'notifyPages'   => new _UserPreference_notify(''), // 1.3.8
                    'theme'         => new _UserPreference_theme(THEME),
                    'lang'          => new _UserPreference_language(DEFAULT_LANGUAGE),
                    'editWidth'     => new _UserPreference_int(EDITWIDTH_DEFAULT_COLS,
                                                               EDITWIDTH_MIN_COLS,
                                                               EDITWIDTH_MAX_COLS),
                    'noLinkIcons'   => new _UserPreference_bool(),    // 1.3.8 
                    'editHeight'    => new _UserPreference_int(EDITHEIGHT_DEFAULT_ROWS,
                                                               EDITHEIGHT_MIN_ROWS,
                                                               EDITHEIGHT_MAX_ROWS),
                    'timeOffset'    => new _UserPreference_numeric(TIMEOFFSET_DEFAULT_HOURS,
                                                                   TIMEOFFSET_MIN_HOURS,
                                                                   TIMEOFFSET_MAX_HOURS),
                    'ownModifications' => new _UserPreference_bool(),
                    'majorModificationsOnly' => new _UserPreference_bool(),
                    'relativeDates' => new _UserPreference_bool(),
                    'googleLink'    => new _UserPreference_bool(), // 1.3.10
                    'doubleClickEdit' => new _UserPreference_bool(), // 1.3.11
                    );

        // This should be probably be done with $customUserPreferenceColumns
        // For now, we use FUSIONFORGE define
        if (FUSIONFORGE) {
            $fusionforgeprefs = array(
                    'pageTrail'     => new _UserPreference_bool(),
                    'diffMenuItem' => new _UserPreference_bool(),
                    'pageInfoMenuItem' => new _UserPreference_bool(),
                    'pdfMenuItem' => new _UserPreference_bool(),
                    'lockMenuItem' => new _UserPreference_bool(),
                    'chownMenuItem' => new _UserPreference_bool(),
                    'setaclMenuItem' => new _UserPreference_bool(),
                    'removeMenuItem' => new _UserPreference_bool(),
                    'renameMenuItem' => new _UserPreference_bool(),
                    'revertMenuItem' => new _UserPreference_bool(),
                    'backLinksMenuItem' => new _UserPreference_bool(),
                    'watchPageMenuItem' => new _UserPreference_bool(),
                    'recentChangesMenuItem' => new _UserPreference_bool(),
                    'randomPageMenuItem' => new _UserPreference_bool(),
                    'likePagesMenuItem' => new _UserPreference_bool(),
                    'specialPagesMenuItem' => new _UserPreference_bool(),
                    );
            $this->_prefs = array_merge($this->_prefs, $fusionforgeprefs);
        }

        // add custom theme-specific pref types:
        // FIXME: on theme changes the wiki_user session pref object will fail. 
        // We will silently ignore this.
        if (!empty($customUserPreferenceColumns))
            $this->_prefs = array_merge($this->_prefs, $customUserPreferenceColumns);
/*
        if (isset($this->_method) and $this->_method == 'SQL') {
            //unset($this->_prefs['userid']);
            unset($this->_prefs['passwd']);
        }
*/
        if (is_array($saved_prefs)) {
            foreach ($saved_prefs as $name => $value)
                $this->set($name, $value);
        }
    }

    function __clone() {
        foreach ($this as $key => $val) {
            if (is_object($val) || (is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
            }
        }
    }

    function _getPref($name) {
    	if ($name == 'emailVerified')
    	    $name = 'email';
        if (!isset($this->_prefs[$name])) {
            if ($name == 'passwd2') return false;
            if ($name == 'passwd') return false;
            trigger_error("$name: unknown preference", E_USER_NOTICE);
            return false;
        }
        return $this->_prefs[$name];
    }
    
    // get the value or default_value of the subobject
    function get($name) {
    	if ($_pref = $this->_getPref($name))
    	    if ($name == 'emailVerified')
    	        return $_pref->getraw($name);
    	    else
    	        return $_pref->get($name);
    	else
    	    return false;  
    }

    // check and set the new value in the subobject
    function set($name, $value) {
        $pref = $this->_getPref($name);
        if ($pref === false)
            return false;

        /* do it here or outside? */
        if ($name == 'passwd' and 
            defined('PASSWORD_LENGTH_MINIMUM') and 
            strlen($value) <= PASSWORD_LENGTH_MINIMUM ) {
            //TODO: How to notify the user?
            return false;
        }
        /*
        if ($name == 'theme' and $value == '')
           return true;
        */
        // Fix Fatal error for undefined value. Thanks to Jim Ford and Joel Schaubert
        if ((!$value and $pref->default_value)
            or ($value and !isset($pref->{$name})) // bug #1355533
            or ($value and ($pref->{$name} != $pref->default_value)))
        {
            if ($name == 'emailVerified') $newvalue = $value;
            else $newvalue = $pref->sanify($value);
	    $pref->set($name, $newvalue);
        }
        $this->_prefs[$name] =& $pref;
        return true;
    }
    /**
     * use init to avoid update on set
     */
    function updatePrefs($prefs, $init = false) {
        $count = 0;
        if ($init) $this->_init = $init;
        if (is_object($prefs)) {
            $type = 'emailVerified'; $obj =& $this->_prefs['email'];
            $obj->_init = $init;
            if ($obj->get($type) !== $prefs->get($type)) {
                if ($obj->set($type, $prefs->get($type)))
                    $count++;
            }
            foreach (array_keys($this->_prefs) as $type) {
            	$obj =& $this->_prefs[$type];
                $obj->_init = $init;
                if ($prefs->get($type) !== $obj->get($type)) {
                    // special systemdefault prefs: (probably not needed)
                    if ($type == 'theme' and $prefs->get($type) == '' and 
                        $obj->get($type) == THEME) continue;
                    if ($type == 'lang' and $prefs->get($type) == '' and 
                        $obj->get($type) == DEFAULT_LANGUAGE) continue;
                    if ($this->_prefs[$type]->set($type, $prefs->get($type)))
                        $count++;
                }
            }
        } elseif (is_array($prefs)) {
            //unset($this->_prefs['userid']);
            /*
	    if (isset($this->_method) and 
	         ($this->_method == 'SQL' or $this->_method == 'ADODB')) {
                unset($this->_prefs['passwd']);
	    }
	    */
	    // emailVerified at first, the rest later
            $type = 'emailVerified'; $obj =& $this->_prefs['email'];
            $obj->_init = $init;
            if (isset($prefs[$type]) and $obj->get($type) !== $prefs[$type]) {
                if ($obj->set($type,$prefs[$type]))
                    $count++;
            }
            foreach (array_keys($this->_prefs) as $type) {
            	$obj =& $this->_prefs[$type];
                $obj->_init = $init;
                if (!isset($prefs[$type]) and isa($obj,"_UserPreference_bool")) 
                    $prefs[$type] = false;
                if (isset($prefs[$type]) and isa($obj,"_UserPreference_int"))
                    $prefs[$type] = (int) $prefs[$type];
                if (isset($prefs[$type]) and $obj->get($type) != $prefs[$type]) {
                    // special systemdefault prefs:
                    if ($type == 'theme' and $prefs[$type] == '' and 
                        $obj->get($type) == THEME) continue;
                    if ($type == 'lang' and $prefs[$type] == '' and 
                        $obj->get($type) == DEFAULT_LANGUAGE) continue;
                    if ($obj->set($type,$prefs[$type]))
                        $count++;
                }
            }
        }
        return $count;
    }

    // For now convert just array of objects => array of values
    // Todo: the specialized subobjects must override this.
    function store() {
        $prefs = array();
        foreach ($this->_prefs as $name => $object) {
            if ($value = $object->getraw($name))
                $prefs[$name] = $value;
            if ($name == 'email' and ($value = $object->getraw('emailVerified')))
                $prefs['emailVerified'] = $value;
            if ($name == 'passwd' and $value and ENCRYPTED_PASSWD) {
                if (strlen($value) != strlen(crypt('test')))
                    $prefs['passwd'] = crypt($value);
                else // already crypted
                    $prefs['passwd'] = $value;
            }
        }

        if (FUSIONFORGE) {
            // Merge current notifyPages with notifyPagesAll
            // notifyPages are pages to notify in the current project
            // while $notifyPagesAll is used to store all the monitored pages.
            if (isset($prefs['notifyPages'])) {
                $this->notifyPagesAll[PAGE_PREFIX] = $prefs['notifyPages'];
                $prefs['notifyPages'] = @serialize($this->notifyPagesAll);
            }
        }

        return $this->pack($prefs);
    }

    // packed string or array of values => array of values
    // Todo: the specialized subobjects must override this.
    function retrieve($packed) {
        if (is_string($packed) and (substr($packed, 0, 2) == "a:"))
            $packed = unserialize($packed);
        if (!is_array($packed)) return false;
        $prefs = array();
        foreach ($packed as $name => $packed_pref) {
            if (is_string($packed_pref)
                and isSerialized($packed_pref)
                and substr($packed_pref, 0, 2) == "O:")
            {
                //legacy: check if it's an old array of objects
                // Looks like a serialized object. 
                // This might fail if the object definition does not exist anymore.
                // object with ->$name and ->default_value vars.
                $pref =  @unserialize($packed_pref);
                if (is_object($pref))
                    $prefs[$name] = $pref->get($name);
            // fix old-style prefs
            } elseif (is_numeric($name) and is_array($packed_pref)) {
            	if (count($packed_pref) == 1) {
            	    list($name,$value) = each($packed_pref);
            	    $prefs[$name] = $value;
            	}
            } else {
                if (isSerialized($packed_pref))
                    $prefs[$name] = @unserialize($packed_pref);
                if (empty($prefs[$name]) and isSerialized(base64_decode($packed_pref)))
                    $prefs[$name] = @unserialize(base64_decode($packed_pref));
                // patched by frederik@pandora.be
                if (empty($prefs[$name]))
                    $prefs[$name] = $packed_pref;
            }
        }
        
        if (FUSIONFORGE) {
            // Restore notifyPages from notifyPagesAll
            // notifyPages are pages to notify in the current project
            // while $notifyPagesAll is used to store all the monitored pages.
            if (isset($prefs['notifyPages'])) {
                $this->notifyPagesAll = $prefs['notifyPages'];
                if (isset($this->notifyPagesAll[PAGE_PREFIX])) {
                    $prefs['notifyPages'] = $this->notifyPagesAll[PAGE_PREFIX];
                } else {
                    $prefs['notifyPages'] = '';
                }
            }
        }

        return $prefs;
    }

    /**
     * Check if the given prefs object is different from the current prefs object
     */
    function isChanged($other) {
        foreach ($this->_prefs as $type => $obj) {
            if ($obj->get($type) !== $other->get($type))
                return true;
        }
        return false;
    }

    function defaultPreferences() {
    	$prefs = array();
    	foreach ($this->_prefs as $key => $obj) {
    	    $prefs[$key] = $obj->default_value;
    	}
    	return $prefs;
    }
    
    // array of objects
    function getAll() {
        return $this->_prefs;
    }

    function pack($nonpacked) {
        return serialize($nonpacked);
    }

    function unpack($packed) {
        if (!$packed)
            return false;
        //$packed = base64_decode($packed);
        if (substr($packed, 0, 2) == "O:") {
            // Looks like a serialized object
            return unserialize($packed);
        }
        if (substr($packed, 0, 2) == "a:") {
            return unserialize($packed);
        }
        //trigger_error("DEBUG: Can't unpack bad UserPreferences",
        //E_USER_WARNING);
        return false;
    }

    function hash () {
        return wikihash($this->_prefs);
    }
}

/** TODO: new pref storage classes
 *  These are currently user specific and should be rewritten to be pref specific.
 *  i.e. $this == $user->_prefs
 */
/*
class CookieUserPreferences
extends UserPreferences
{
    function CookieUserPreferences ($saved_prefs = false) {
    	//_AnonUser::_AnonUser('',$saved_prefs);
        UserPreferences::UserPreferences($saved_prefs);
    }
}

class PageUserPreferences
extends UserPreferences
{
    function PageUserPreferences ($saved_prefs = false) {
        UserPreferences::UserPreferences($saved_prefs);
    }
}

class PearDbUserPreferences
extends UserPreferences
{
    function PearDbUserPreferences ($saved_prefs = false) {
        UserPreferences::UserPreferences($saved_prefs);
    }
}

class AdoDbUserPreferences
extends UserPreferences
{
    function AdoDbUserPreferences ($saved_prefs = false) {
        UserPreferences::UserPreferences($saved_prefs);
    }
    function getPreferences() {
        // override the generic slow method here for efficiency
        _AnonUser::getPreferences();
        $this->getAuthDbh();
        if (isset($this->_select)) {
            $dbh = & $this->_auth_dbi;
            $rs = $dbh->Execute(sprintf($this->_select,$dbh->qstr($this->_userid)));
            if ($rs->EOF) {
                $rs->Close();
            } else {
                $prefs_blob = $rs->fields['pref_blob'];
                $rs->Close();
                if ($restored_from_db = $this->_prefs->retrieve($prefs_blob)) {
                    $updated = $this->_prefs->updatePrefs($restored_from_db);
                    //$this->_prefs = new UserPreferences($restored_from_db);
                    return $this->_prefs;
                }
            }
        }
        if (empty($this->_prefs->_prefs) and $this->_HomePagehandle) {
            if ($restored_from_page = $this->_prefs->retrieve
                ($this->_HomePagehandle->get('pref'))) {
                $updated = $this->_prefs->updatePrefs($restored_from_page);
                //$this->_prefs = new UserPreferences($restored_from_page);
                return $this->_prefs;
            }
        }
        return $this->_prefs;
    }
}
*/

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
