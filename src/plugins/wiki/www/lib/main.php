<?php //-*-php-*-
// rcs_id('$Id: main.php 7737 2010-11-10 08:47:34Z rurban $');
/*
 * Copyright 1999-2008 $ThePhpWikiProgrammingTeam
 * Copyright (C) 2008-2010 Marc-Etienne Vargenau, Alcatel-Lucent
 * Copyright (C) 2009 Roger Guignard, Alcatel-Lucent
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

define ('USE_PREFS_IN_PAGE', true);

//include "lib/config.php";
require_once(dirname(__FILE__)."/stdlib.php");
require_once('lib/Request.php');
require_once('lib/WikiDB.php');
if (ENABLE_USER_NEW)
    require_once("lib/WikiUserNew.php");
else
    require_once("lib/WikiUser.php");
require_once("lib/WikiGroup.php");
if (ENABLE_PAGEPERM)
    require_once("lib/PagePerm.php");

/**
 * Check permission per page.
 * Returns true or false.
 */
function mayAccessPage ($access, $pagename) {
    if (ENABLE_PAGEPERM)
        return _requiredAuthorityForPagename($access, $pagename); // typically [10-20ms per page]
    else
        return true;
}

class WikiRequest extends Request {
    // var $_dbi;

    function WikiRequest () {
        $this->_dbi = WikiDB::open($GLOBALS['DBParams']);
         // first mysql request costs [958ms]! [670ms] is mysql_connect()

        if (in_array('File', $this->_dbi->getAuthParam('USER_AUTH_ORDER'))) {
            // force our local copy, until the pear version is fixed.
            include_once(dirname(__FILE__)."/pear/File_Passwd.php");
        }
        if (ENABLE_USER_NEW) {
            // Preload all necessary userclasses. Otherwise session => __PHP_Incomplete_Class_Name
            // There's no way to demand-load it later. This way it's much slower, but needs slightly
            // less memory than loading all.
            if (ALLOW_BOGO_LOGIN)
                include_once("lib/WikiUser/BogoLogin.php");
            // UserPreferences POST Update doesn't reach this.
            foreach ($GLOBALS['USER_AUTH_ORDER'] as $method) {
                include_once("lib/WikiUser/$method.php");
            	if ($method == 'Db')
            	    switch( DATABASE_TYPE ) {
            	    	case 'SQL'  : include_once("lib/WikiUser/PearDb.php"); break;
            	    	case 'ADODB': include_once("lib/WikiUser/AdoDb.php"); break;
                        case 'PDO'  : include_once("lib/WikiUser/PdoDb.php"); break;
            	    }
            }
            unset($method);
        }
        if (USE_DB_SESSION) {
            include_once('lib/DbSession.php');
            $dbi =& $this->_dbi;
            if (defined('READONLY') and !READONLY) // READONLY might be set later
                $this->_dbsession = new DbSession($dbi, $dbi->getParam('prefix')
                                                  . $dbi->getParam('db_session_table'));
        }

// Fixme: Does pear reset the error mask to 1? We have to find the culprit
//$x = error_reporting();

        $this->version = phpwiki_version();
        $this->Request(); // [90ms]

        // Normalize args...
        $this->setArg('pagename', $this->_deducePagename());
        $this->setArg('action', $this->_deduceAction());

        if ((DEBUG & _DEBUG_SQL)
	    or (DATABASE_OPTIMISE_FREQUENCY > 0 and
                (time() % DATABASE_OPTIMISE_FREQUENCY == 0))) {
            if ($this->_dbi->_backend->optimize())
                trigger_error(_("Optimizing database"), E_USER_NOTICE);
        }

        // Restore auth state. This doesn't check for proper authorization!
        $userid = $this->_deduceUsername();
        if (ENABLE_USER_NEW) {
            if (isset($this->_user) and
                !empty($this->_user->_authhow) and
                $this->_user->_authhow == 'session')
            {
                // users might switch in a session between the two objects.
                // restore old auth level here or in updateAuthAndPrefs?
                //$user = $this->getSessionVar('wiki_user');
                // revive db handle, because these don't survive sessions
                if (isset($this->_user) and
                     ( ! isa($this->_user, WikiUserClassname())
                       or (strtolower(get_class($this->_user)) == '_passuser')
                       or (strtolower(get_class($this->_user)) == '_fusionforgepassuser')))
                {
                    $this->_user = WikiUser($userid, $this->_user->_prefs);
                }
	        // revive other db handle
	        if (isset($this->_user->_prefs->_method)
                    and ($this->_user->_prefs->_method == 'SQL'
                         or $this->_user->_prefs->_method == 'ADODB'
                         or $this->_user->_prefs->_method == 'PDO'
                         or $this->_user->_prefs->_method == 'HomePage')) {
	            $this->_user->_HomePagehandle = $this->getPage($userid);
	        }
	        // need to update the lockfile filehandle
	        if ( isa($this->_user, '_FilePassUser')
                     and $this->_user->_file->lockfile
                     and !$this->_user->_file->fplock )
	        {
	            //$level = $this->_user->_level;
	            $this->_user = UpgradeUser($this->_user,
	                                       new _FilePassUser($userid,
                                                                 $this->_user->_prefs,
                                                                 $this->_user->_file->filename));
                    //$this->_user->_level = $level;
                }
            	$this->_prefs = & $this->_user->_prefs;
            } else {
                $user = WikiUser($userid);
                $this->_user = & $user;
                $this->_prefs = & $this->_user->_prefs;
            }
        } else {
            $this->_user = new WikiUser($this, $userid);
            $this->_prefs = $this->_user->getPreferences();
        }
    }

    function initializeLang () {
        // check non-default pref lang
        if (empty($this->_prefs->_prefs['lang']))
            return;
        $_lang = $this->_prefs->_prefs['lang'];
        if (isset($_lang->lang) and $_lang->lang != $GLOBALS['LANG']) {
            $user_lang = $_lang->lang;
            //check changed LANG and THEME inside a session.
            // (e.g. by using another baseurl)
            if (isset($this->_user->_authhow) and $this->_user->_authhow == 'session')
                $user_lang = $GLOBALS['LANG'];
            update_locale($user_lang);
            FindLocalizedButtonFile(".",'missing_ok','reinit');
        }
        //if (empty($_lang->lang) and $GLOBALS['LANG'] != $_lang->default_value) ;
    }

    function initializeTheme ($when = 'default') {
        global $WikiTheme;
	// if when = 'default', then first time init (default theme, ...)
	// if when = 'login', then check some callbacks
	//                    and maybe the theme changed (other theme defined in pref)
	// if when = 'logout', then check other callbacks
	//                    and maybe the theme changed (back to default theme)

        // Load non-default theme (when = login)
        if (!empty($this->_prefs->_prefs['theme'])) {
            $_theme = $this->_prefs->_prefs['theme'];
            if (isset($_theme) and isset($_theme->theme))
                $user_theme = $_theme->theme;
            elseif (isset($_theme) and isset($_theme->default_value))
                $user_theme = $_theme->default_value;
            else
                $user_theme = '';
        }
        else
            $user_theme = $this->getPref('theme');

        //check changed LANG and THEME inside a session.
        // (e.g. by using another baseurl)
        if (isset($this->_user->_authhow)
            and $this->_user->_authhow == 'session'
            and !isset($_theme->theme)
            and defined('THEME')
            and $user_theme != THEME)
        {
            include_once("themes/" . THEME . "/themeinfo.php");
        }
        if (empty($WikiTheme) and $user_theme) {
            if (strcspn($user_theme,"./\x00]") != strlen($user_theme)) {
            	trigger_error(sprintf("invalid theme '%s': Invalid characters detected",
                                      $user_theme),
            	              E_USER_WARNING);
                $user_theme = "default";
            }
	    if (!$user_theme) $user_theme = "default";
            include_once("themes/$user_theme/themeinfo.php");
        }
        if (empty($WikiTheme) and defined('THEME'))
            include_once("themes/" . THEME . "/themeinfo.php");
        if (empty($WikiTheme))
            include_once("themes/default/themeinfo.php");
        assert(!empty($WikiTheme));

	// Do not execute global init code anymore

	// WikiTheme callbacks
	if ($when == 'login') {
	    $WikiTheme->CbUserLogin($this, $this->_user->_userid);
	    if (!$this->_user->hasHomePage()) { // NewUser
		$WikiTheme->CbNewUserLogin($this, $this->_user->_userid);
		if (in_array($this->getArg('action'), array('edit','create')))
		    $WikiTheme->CbNewUserEdit($this, $this->_user->_userid);
	    }
	}
	elseif ($when == 'logout') {
	    $WikiTheme->CbUserLogout($this, $this->_user->_userid);
	}
	elseif ($when == 'default') {
	    $WikiTheme->load();
	    if ($this->_user->_level > 0 and !$this->_user->hasHomePage()) { // NewUser
		if (in_array($this->getArg('action'), array('edit','create')))
		    $WikiTheme->CbNewUserEdit($this, $this->_user->_userid);
	    }
	}
    }

    // This really maybe should be part of the constructor, but since it
    // may involve HTML/template output, the global $request really needs
    // to be initialized before we do this stuff.
    // [50ms]: 36ms if wikidb_page::exists
    function updateAuthAndPrefs () {

        if (isset($this->_user) and (!isa($this->_user, WikiUserClassname()))) {
            $this->_user = false;
        }
        // Handle authentication request, if any.
        if ($auth_args = $this->getArg('auth')) {
            $this->setArg('auth', false);
            $this->_handleAuthRequest($auth_args); // possible NORETURN
        }
        elseif ( ! $this->_user
                 or (isa($this->_user, WikiUserClassname())
                     and ! $this->_user->isSignedIn())) {
            // If not auth request, try to sign in as saved user.
            if (($saved_user = $this->getPref('userid')) != false) {
                $this->_signIn($saved_user);
            }
        }

        $action = $this->getArg('action');

        // Save preferences in session and cookie
        if ((defined('WIKI_XMLRPC') and !WIKI_XMLRPC) or $action != 'xmlrpc') {
            if (isset($this->_user) and $this->_user->_userid) {
            	if (!isset($this->_user->_authhow) or $this->_user->_authhow != 'session') {
                    $this->_user->setPreferences($this->_prefs, true);
            	}
            }
            $tmpuser = $this->_user; // clone it
            $this->setSessionVar('wiki_user', $tmpuser);
            unset($tmpuser);
        }

        // Ensure user has permissions for action
        // HACK ALERT: We may not set the request arg to create,
        // since the pageeditor has an ugly logic for action == create.
  	if ($action == 'edit' or $action == 'create') {
            $page = $this->getPage();
            if (! $page->exists() )
                $action = 'create';
            else
                $action = 'edit';
  	}
        if (! ENABLE_PAGEPERM) { // Bug #1438392 by Matt Brown
            $require_level = $this->requiredAuthority($action);
            if (! $this->_user->hasAuthority($require_level))
                $this->_notAuthorized($require_level); // NORETURN
        } else {
            // novatrope patch to let only _AUTHENTICATED view pages.
            // If there's not enough authority or forbidden, ask for a password,
            // unless it's explicitly unobtainable. Some bad magic though.
            if ($this->requiredAuthorityForAction($action) == WIKIAUTH_UNOBTAINABLE) {
                $require_level = $this->requiredAuthority($action);
                $this->_notAuthorized($require_level); // NORETURN
            }
        }
    }

    function & getUser () {
        if (isset($this->_user))
            return $this->_user;
        else
            return $GLOBALS['ForbiddenUser'];
    }

    function & getGroup () {
        if (isset($this->_user) and isset($this->_user->_group))
            return $this->_user->_group;
        else {
	    // Debug Strict: Only variable references should be returned by reference
            $this->_user->_group = WikiGroup::getGroup();
            return $this->_user->_group;
        }
    }

    function & getPrefs () {
        return $this->_prefs;
    }

    // Convenience function:
    function getPref ($key) {
        if (isset($this->_prefs)) {
            return $this->_prefs->get($key);
        }
    }
    function & getDbh () {
        return $this->_dbi;
    }

    /**
     * Get requested page from the page database.
     * By default it will grab the page requested via the URL
     *
     * This is a convenience function.
     * @param string $pagename Name of page to get.
     * @return WikiDB_Page Object with methods to pull data from
     * database for the page requested.
     */
    function getPage ($pagename = false) {
        //if (!isset($this->_dbi)) $this->getDbh();
        if (!$pagename)
            $pagename = $this->getArg('pagename');
        return $this->_dbi->getPage($pagename);
    }

    /** Get URL for POST actions.
     *
     * Officially, we should just use SCRIPT_NAME (or some such),
     * but that causes problems when we try to issue a redirect, e.g.
     * after saving a page.
     *
     * Some browsers (at least NS4 and Mozilla 0.97 won't accept
     * a redirect from a page to itself.)
     *
     * So, as a HACK, we include pagename and action as query args in
     * the URL.  (These should be ignored when we receive the POST
     * request.)
     */
    function getPostURL ($pagename = false) {
        global $HTTP_GET_VARS;

        if ($pagename === false)
            $pagename = $this->getArg('pagename');
        $action = $this->getArg('action');
        if (!empty($HTTP_GET_VARS['start_debug'])) // zend ide support
            return WikiURL($pagename, array('action' => $action, 'start_debug' => 1));
        elseif ($action == 'edit')
            return WikiURL($pagename);
        else
            return WikiURL($pagename, array('action' => $action));
    }

    function _handleAuthRequest ($auth_args) {
        if (!is_array($auth_args))
            return;

        // Ignore password unless POST'ed.
        if (!$this->isPost())
            unset($auth_args['passwd']);

        $olduser = $this->_user;
        $user = $this->_user->AuthCheck($auth_args);
        if (is_string($user)) {
            // Login attempt failed.
            $fail_message = $user;
            $auth_args['pass_required'] = true;
            // if clicked just on to the "sign in as:" button dont print invalid username.
            if (!empty($auth_args['login']) and empty($auth_args['userid']))
                $fail_message = '';
            // If no password was submitted, it's not really
            // a failure --- just need to prompt for password...
            if (!ALLOW_USER_PASSWORDS
                and ALLOW_BOGO_LOGIN
                and !isset($auth_args['passwd']))
            {
                $fail_message = false;
            }
            $olduser->PrintLoginForm($this, $auth_args, $fail_message, 'newpage');
            $this->finish();    //NORETURN
        }
        elseif (isa($user, WikiUserClassname())) {
            // Successful login (or logout.)
            $this->_setUser($user);
        }
        else {
            // Login request cancelled.
        }
    }

    /**
     * Attempt to sign in (bogo-login).
     *
     * Fails silently.
     *
     * @param $userid string Userid to attempt to sign in as.
     * @access private
     */
    function _signIn ($userid) {
        if (ENABLE_USER_NEW) {
            if (! $this->_user )
                $this->_user = new _BogoUser($userid);
            // FIXME: is this always false? shouldn't we try passuser first?
            if (! $this->_user )
                $this->_user = new _PassUser($userid);
        } else {
            if (! $this->_user )
                $this->_user = new WikiUser($this, $userid);
        }
        $user = $this->_user->AuthCheck(array('userid' => $userid));
        if (isa($user, WikiUserClassname())) {
            $this->_setUser($user); // success!
        }
    }

    // login or logout or restore state
    function _setUser (&$user) {
        $this->_user =& $user;
        if (defined('MAIN_setUser')) return; // don't set cookies twice
        $this->setCookieVar(getCookieName(), $user->getAuthenticatedId(),
                            COOKIE_EXPIRATION_DAYS, COOKIE_DOMAIN);
	$isSignedIn = $user->isSignedIn();
        if ($isSignedIn) {
            $user->_authhow = 'signin';
	}

        // Save userid to prefs..
        if ( empty($this->_user->_prefs)) {
            $this->_user->_prefs = $this->_user->getPreferences();
            $this->_prefs =& $this->_user->_prefs;
        }
        $this->_user->_group = $this->getGroup();
        $this->setSessionVar('wiki_user', $user);
        $this->_prefs->set('userid',
                           $isSignedIn ? $user->getId() : '');
        if (!ENABLE_USER_NEW) {
            if (empty($this->_user->_request))
                $this->_user->_request =& $this;
            if (empty($this->_user->_dbi))
                $this->_user->_dbi =& $this->_dbi;
        }
        $this->initializeTheme($isSignedIn ? 'login' : 'logout');
        define('MAIN_setUser', true);
    }

    /* Permission system */
    function getLevelDescription($level) {
    	static $levels = false;
    	if (!$levels) // This looks like a Visual Basic hack. For the very same reason. "0"
    	    $levels = array('x-1' => _("FORBIDDEN"),
                            'x0'  => _("ANON"),
                            'x1'  => _("BOGO"),
                            'x2'  => _("USER"),
                            'x10' => _("ADMIN"),
                            'x100'=> _("UNOBTAINABLE"));
        if (!empty($level))
            $level = '0';
        if (!empty($levels["x".$level]))
            return $levels["x".$level];
        else
            return _("ANON");
    }

    function _notAuthorized ($require_level) {
        // Display the authority message in the Wiki's default
        // language, in case it is not english.
        //
        // Note that normally a user will not see such an error once
        // logged in, unless the admin has altered the default
        // disallowed wikiactions. In that case we should probably
        // check the user's language prefs too at this point; this
        // would be a situation which is not really handled with the
        // current code.
        if (empty($GLOBALS['LANG']))
            update_locale(DEFAULT_LANGUAGE);

        // User does not have required authority.  Prompt for login.
        $what = $this->getActionDescription($this->getArg('action'));
        $pass_required = ($require_level >= WIKIAUTH_USER);
        if ($require_level == WIKIAUTH_UNOBTAINABLE) {
            global $DisabledActions;
	    if ($DisabledActions and in_array($action, $DisabledActions)) {
            	$msg = fmt("%s is disallowed on this wiki.",
                           $this->getDisallowedActionDescription($this->getArg('action')));
		$this->finish();
		return;
	    }
	    // Is the reason a missing ACL or just wrong user or password?
            if (class_exists('PagePermission')) {
                $user =& $this->_user;
            	$status = $user->isAuthenticated() ? _("authenticated") : _("not authenticated");
            	$msg = fmt("%s %s %s is disallowed on this wiki for %s user '%s' (level: %s).",
                           _("Missing PagePermission:"),
                           action2access($this->getArg('action')),
                           $this->getArg('pagename'),
                           $status, $user->getId(), $this->getLevelDescription($user->_level));
                // TODO: add link to action=setacl
                $user->PrintLoginForm($this, compact('pass_required'), $msg);
                $this->finish();
		return;
            } else {
            	$msg = fmt("%s is disallowed on this wiki.",
                           $this->getDisallowedActionDescription($this->getArg('action')));
                $this->_user->PrintLoginForm($this, compact('require_level','pass_required'), $msg);
		$this->finish();
		return;
            }
        }
        elseif ($require_level == WIKIAUTH_BOGO)
            $msg = fmt("You must sign in to %s.", $what);
        elseif ($require_level == WIKIAUTH_USER) {
	    // LoginForm should display the relevant messages...
	    $msg = "";
	    /*if (!ALLOW_ANON_USER)
		$msg = fmt("You must log in first to %s", $what);
	    else
                $msg = fmt("You must log in to %s.", $what);
	    */
        } elseif ($require_level == WIKIAUTH_ANON)
            $msg = fmt("Access for you is forbidden to %s.", $what);
        else
            $msg = fmt("You must be an administrator to %s.", $what);

        $this->_user->PrintLoginForm($this, compact('require_level','pass_required'),
				     $msg);
	if (!$GLOBALS['WikiTheme']->DUMP_MODE)
	    $this->finish();    // NORETURN
    }

    // Fixme: for PagePermissions we'll need other strings,
    // relevant to the requested page, not just for the action on the whole wiki.
    function getActionDescription($action) {
        static $actionDescriptions;
        if (! $actionDescriptions) {
            $actionDescriptions
            = array('browse'     => _("view this page"),
                    'diff'       => _("diff this page"),
                    'dumphtml'   => _("dump html pages"),
                    'dumpserial' => _("dump serial pages"),
                    'edit'       => _("edit this page"),
                    'rename'     => _("rename this page"),
                    'revert'     => _("revert to a previous version of this page"),
                    'create'     => _("create this page"),
                    'loadfile'   => _("load files into this wiki"),
                    'lock'       => _("lock this page"),
                    'purge'      => _("purge this page"),
                    'remove'     => _("remove this page"),
                    'unlock'     => _("unlock this page"),
                    'upload'     => _("upload a zip dump"),
                    'verify'     => _("verify the current action"),
                    'viewsource' => _("view the source of this page"),
                    'xmlrpc'     => _("access this wiki via XML-RPC"),
                    'soap'       => _("access this wiki via SOAP"),
                    'zip'        => _("download a zip dump from this wiki"),
                    'ziphtml'    => _("download a html zip dump from this wiki")
                    );
        }
        if (in_array($action, array_keys($actionDescriptions)))
            return $actionDescriptions[$action];
        else
            return _("use")." ".$action;
    }

    /**
     TODO: check against these cases:
        if ($DisabledActions and in_array($action, $DisabledActions))
            return WIKIAUTH_UNOBTAINABLE;

    	if (ENABLE_PAGEPERM and class_exists("PagePermission")) {
    	   return requiredAuthorityForPage($action);
 
=> Browsing pages is disallowed on this wiki for authenticated user 'rurban' (level: BOGO).
    */
    function getDisallowedActionDescription($action) {
        static $disallowedActionDescriptions;

        if (! $disallowedActionDescriptions) {
            $disallowedActionDescriptions
            = array('browse'     => _("Browsing pages"),
                    'diff'       => _("Diffing pages"),
                    'dumphtml'   => _("Dumping html pages"),
                    'dumpserial' => _("Dumping serial pages"),
                    'edit'       => _("Editing pages"),
                    'revert'     => _("Reverting to a previous version of pages"),
                    'create'     => _("Creating pages"),
                    'loadfile'   => _("Loading files"),
                    'lock'       => _("Locking pages"),
                    'purge'      => _("Purging pages"),
                    'remove'     => _("Removing pages"),
                    'unlock'     => _("Unlocking pages"),
                    'upload'     => _("Uploading zip dumps"),
                    'verify'     => _("Verify the current action"),
                    'viewsource' => _("Viewing the source of pages"),
                    'xmlrpc'     => _("XML-RPC access"),
                    'soap'       => _("SOAP access"),
                    'zip'        => _("Downloading zip dumps"),
                    'ziphtml'    => _("Downloading html zip dumps")
                    );
        }
        if (in_array($action, array_keys($disallowedActionDescriptions)))
            return $disallowedActionDescriptions[$action];
        else
            return $action;
    }

    function requiredAuthority ($action) {
        $auth = $this->requiredAuthorityForAction($action);
        if (!ALLOW_ANON_USER) return WIKIAUTH_USER;

        /*
         * This is a hook for plugins to require authority
         * for posting to them.
         *
         * IMPORTANT: This is not a secure check, so the plugin
         * may not assume that any POSTs to it are authorized.
         * All this does is cause PhpWiki to prompt for login
         * if the user doesn't have the required authority.
         */
        if ($this->isPost()) {
            $post_auth = $this->getArg('require_authority_for_post');
            if ($post_auth !== false)
                $auth = max($auth, $post_auth);
        }
        return $auth;
    }

    function requiredAuthorityForAction ($action) {
        global $DisabledActions;

        if ($DisabledActions and in_array($action, $DisabledActions))
            return WIKIAUTH_UNOBTAINABLE;

    	if (ENABLE_PAGEPERM and class_exists("PagePermission")) {
    	   return requiredAuthorityForPage($action);
    	} else {
          // FIXME: clean up.
          switch ($action) {
            case 'browse':
            case 'viewsource':
            case 'diff':
            case 'select':
            case 'search':
            case 'pdf':
            case 'captcha':
            case 'wikitohtml':
            case 'setpref':
                return WIKIAUTH_ANON;

            case 'xmlrpc':
            case 'soap':
            case 'dumphtml':
                if (INSECURE_ACTIONS_LOCALHOST_ONLY and !is_localhost())
		    return WIKIAUTH_ADMIN;
		return WIKIAUTH_ANON;

            case 'ziphtml':
                if (ZIPDUMP_AUTH)
                    return WIKIAUTH_ADMIN;
                if (INSECURE_ACTIONS_LOCALHOST_ONLY and !is_localhost())
		    return WIKIAUTH_ADMIN;
		return WIKIAUTH_ANON;

            case 'dumpserial':
                if (INSECURE_ACTIONS_LOCALHOST_ONLY and is_localhost())
		    return WIKIAUTH_ANON;
		return WIKIAUTH_ADMIN;

            case 'zip':
                if (ZIPDUMP_AUTH)
                    return WIKIAUTH_ADMIN;
                return WIKIAUTH_ANON;

            case 'edit':
            case 'revert':
            case 'rename':
                if (defined('REQUIRE_SIGNIN_BEFORE_EDIT') && REQUIRE_SIGNIN_BEFORE_EDIT)
                    return WIKIAUTH_BOGO;
                return WIKIAUTH_ANON;
                // return WIKIAUTH_BOGO;

            case 'create':
                $page = $this->getPage();
                $current = $page->getCurrentRevision();
                if ($current->hasDefaultContents())
                    return $this->requiredAuthorityForAction('edit');
                return $this->requiredAuthorityForAction('browse');

            case 'upload':
            case 'loadfile':
            case 'purge':
            case 'remove':
            case 'lock':
            case 'unlock':
            case 'upgrade':
            case 'chown':
            case 'setacl':
            case 'setaclsimple':
                return WIKIAUTH_ADMIN;

            /* authcheck occurs only in the plugin.
               required actionpage RateIt */
            /*
            case 'rate':
            case 'delete_rating':
                // Perhaps this should be WIKIAUTH_USER
                return WIKIAUTH_BOGO;
            */

            default:
                global $WikiNameRegexp;
                if (preg_match("/$WikiNameRegexp\Z/A", $action))
                    return WIKIAUTH_ANON; // ActionPage.
                else
                    return WIKIAUTH_ADMIN;
          }
        }
    }
    /* End of Permission system */

    function possiblyDeflowerVirginWiki () {
        if ($this->getArg('action') != 'browse')
            return;
        if ($this->getArg('pagename') != HOME_PAGE)
            return;

        $page = $this->getPage();
        $current = $page->getCurrentRevision();
        if ($current->getVersion() > 0)
            return;             // Homepage exists.

        include_once('lib/loadsave.php');
        $this->setArg('action', 'loadfile');
        SetupWiki($this);
        $this->finish();        // NORETURN
    }

    // [574ms] mainly template:printexpansion: 393ms and template::expandsubtemplate [100+70+60ms]
    function handleAction () {
        // Check illegal characters in page names: <>[]{}|"
        require_once("lib/Template.php");
        $page = $this->getPage();
        $pagename = $page->getName();
        if (preg_match("/[<\[\{\|\"\}\]>]/", $pagename, $matches) > 0) {
            $CONTENT = HTML::div(
                         array('class' => 'error'),
                         _("Illegal character '"). $matches[0] . _("' in page name."));
            GeneratePage($CONTENT, $pagename);
            $this->finish();
        }
        $action = $this->getArg('action');
        if ($this->isPost()
            and !$this->_user->isAdmin()
            and $action != 'browse'
            and $action != 'wikitohtml'
            )
        {
            if ( $page->get('moderation') ) {
                require_once("lib/WikiPlugin.php");
                $loader = new WikiPluginLoader();
                $plugin = $loader->getPlugin("ModeratedPage");
            	if ($plugin->handler($this, $page)) {
            	    $CONTENT = HTML::div
                        (
                         array('class' => 'wiki-edithelp'),
                         fmt("%s: action forwarded to a moderator.",
                             $action),
                         HTML::br(),
                         _("This action requires moderator approval. Please be patient."));
                    if (!empty($plugin->_tokens['CONTENT']))
                        $plugin->_tokens['CONTENT']->pushContent
                            (
                             HTML::br(),
                             _("You must wait for moderator approval."));
                    else
                        $plugin->_tokens['CONTENT'] = $CONTENT;
            	    $title = WikiLink($page->getName());
            	    $title->pushContent(' : ', WikiLink(_("ModeratedPage")));
	            GeneratePage(Template('browse', $plugin->_tokens),
	                         $title,
	                         $page->getCurrentRevision());
                    $this->finish();
                }
            }
        }
        $method = "action_$action";
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
        elseif ($page = $this->findActionPage($action)) {
            $this->actionpage($page);
        }
        else {
            $this->finish(fmt("%s: Bad action", $action));
        }
    }

    function finish ($errormsg = false) {
        static $in_exit = 0;

        if ($in_exit)
            exit();        // just in case CloseDataBase calls us
        $in_exit = true;

        global $ErrorManager;
        $ErrorManager->flushPostponedErrors();

        if (!empty($errormsg)) {
            PrintXML(HTML::br(),
                     HTML::hr(),
                     HTML::h2(_("Fatal PhpWiki Error")),
                     $errormsg);
            // HACK:
            echo "\n</body></html>";
        }
        if (is_object($this->_user)) {
            $this->_user->page   = $this->getArg('pagename');
            $this->_user->action = $this->getArg('action');
            unset($this->_user->_HomePagehandle);
            unset($this->_user->_auth_dbi);
            unset($this->_user->_dbi);
            unset($this->_user->_request);
	}
        Request::finish();
        exit;
    }

    /**
     * Generally pagename is rawurlencoded for older browsers or mozilla.
     * Typing a pagename into the IE bar will utf-8 encode it, so we have to
     * fix that with fixTitleEncoding().
     * If USE_PATH_INFO = true, the pagename is stripped from the "/DATA_PATH/PageName&arg=value" line.
     * If false, we support either "/index.php?pagename=PageName&arg=value",
     * or the first arg (1.2.x style): "/index.php?PageName&arg=value"
     */
    function _deducePagename () {
        if (trim(rawurldecode($this->getArg('pagename'))))
            return fixTitleEncoding(rawurldecode($this->getArg('pagename')));

        if (USE_PATH_INFO) {
            $pathinfo = $this->get('PATH_INFO');
            if (empty($pathinfo)) { // fix for CGI
                $path = $this->get('REQUEST_URI');
                $script = $this->get('SCRIPT_NAME');
                $pathinfo = substr($path,strlen($script));
                $pathinfo = preg_replace('/\?.+$/','',$pathinfo);
            }
            $tail = substr($pathinfo, strlen(PATH_INFO_PREFIX));

            if (trim($tail) != '' and $pathinfo == PATH_INFO_PREFIX . $tail) {
                return fixTitleEncoding($tail);
            }
        }
        elseif ($this->isPost()) {
            /*
             * In general, for security reasons, HTTP_GET_VARS should be ignored
             * on POST requests, but we make an exception here (only for pagename).
             *
             * The justification for this hack is the following
             * asymmetry: When POSTing with USE_PATH_INFO set, the
             * pagename can (and should) be communicated through the
             * request URL via PATH_INFO.  When POSTing with
             * USE_PATH_INFO off, this cannot be done --- the only way
             * to communicate the pagename through the URL is via
             * QUERY_ARGS (HTTP_GET_VARS).
             */
            global $HTTP_GET_VARS;
            if (isset($HTTP_GET_VARS['pagename']) and trim($HTTP_GET_VARS['pagename'])) {
                return fixTitleEncoding(rawurldecode($HTTP_GET_VARS['pagename']));
            }
        }

        /*
         * Support for PhpWiki 1.2 style requests.
         * Strip off "&" args (?PageName&action=...&start_debug,...)
         */
        $query_string = $this->get('QUERY_STRING');
        if (trim(rawurldecode($query_string)) and preg_match('/^([^&=]+)(&.+)?$/', $query_string, $m)) {
            return fixTitleEncoding(rawurldecode($m[1]));
        }

        return fixTitleEncoding(HOME_PAGE);
    }

    function _deduceAction () {
        if (!($action = $this->getArg('action'))) {
            // TODO: improve this SOAP.php hack by letting SOAP use index.php
            // or any other virtual url as with xmlrpc
            if (defined('WIKI_SOAP') and WIKI_SOAP)
                return 'soap';
            // Detect XML-RPC requests.
            if ($this->isPost()
                && ((defined("WIKI_XMLRPC") and WIKI_XMLRPC)
                    or ($this->get('CONTENT_TYPE') == 'text/xml'
                        or $this->get('CONTENT_TYPE') == 'application/xml')
                    && strstr($GLOBALS['HTTP_RAW_POST_DATA'], '<methodCall>'))
               )
            {
                return 'xmlrpc';
            }
            return 'browse';    // Default if no action specified.
        }

        if (method_exists($this, "action_$action"))
            return $action;

        // Allow for, e.g. action=LikePages
        if (isActionPage($action))
            return $action;

        // Handle untranslated actionpages in non-english
        // (people playing with switching languages)
        if (0 and $GLOBALS['LANG'] != 'en') {
            require_once("lib/plugin/_WikiTranslation.php");
            $trans = new WikiPlugin__WikiTranslation();
            $en_action = $trans->translate($action,'en',$GLOBALS['LANG']);
            if (isActionPage($en_action))
                return $en_action;
        }

        trigger_error("$action: Unknown action", E_USER_NOTICE);
        return 'browse';
    }

    function _deduceUsername() {
        global $HTTP_SERVER_VARS, $HTTP_ENV_VARS;

        if (!empty($this->args['auth']) and !empty($this->args['auth']['userid']))
            return $this->args['auth']['userid'];

        if ($user = $this->getSessionVar('wiki_user')) {
            // Switched auth between sessions.
            // Note: There's no way to demandload a missing class-definition
            // afterwards! Stupid php.
            if (defined('FUSIONFORGE') and FUSIONFORGE) {
                if (empty($HTTP_SERVER_VARS['PHP_AUTH_USER'])) {
                    return false;
                }
            } else if (isa($user, WikiUserClassname())) {
                $this->_user = $user;
                $this->_user->_authhow = 'session';
                return ENABLE_USER_NEW ? $user->UserName() : $this->_user;
            }
        }

	// Sessions override http auth
        if (!empty($HTTP_SERVER_VARS['PHP_AUTH_USER']))
            return $HTTP_SERVER_VARS['PHP_AUTH_USER'];
        // pubcookie et al
        if (!empty($HTTP_SERVER_VARS['REMOTE_USER']))
            return $HTTP_SERVER_VARS['REMOTE_USER'];
        if (!empty($HTTP_ENV_VARS['REMOTE_USER']))
            return $HTTP_ENV_VARS['REMOTE_USER'];

        if ($userid = $this->getCookieVar(getCookieName())) {
            if (!empty($userid) and substr($userid,0,2) != 's:') {
                $this->_user->authhow = 'cookie';
                return $userid;
            }
        }

        if ($this->getArg('action') == 'xmlrpc') { // how about SOAP?
	    if (empty($GLOBALS['HTTP_RAW_POST_DATA']))
		trigger_error("Wrong always_populate_raw_post_data = Off setting in your php.ini\nCannot use xmlrpc!", E_USER_ERROR);
            // wiki.putPage has special otional userid/passwd arguments. check that later.
            $userid = '';
            if (isset($HTTP_SERVER_VARS['REMOTE_USER']))
                $userid = $HTTP_SERVER_VARS['REMOTE_USER'];
            elseif (isset($HTTP_SERVER_VARS['REMOTE_ADDR']))
                $userid = $HTTP_SERVER_VARS['REMOTE_ADDR'];
            elseif (isset($HTTP_ENV_VARS['REMOTE_ADDR']))
                $userid = $HTTP_ENV_VARS['REMOTE_ADDR'];
            elseif (isset($GLOBALS['REMOTE_ADDR']))
                $userid = $GLOBALS['REMOTE_ADDR'];
            return $userid;
        }

        return false;
    }

    function findActionPage ($action) {
        static $cache;
        if (!$action) return false;

        // check for translated version, as per users preferred language
        // (or system default in case it is not en)
        $translation = gettext($action);

        if (isset($cache) and isset($cache[$translation]))
            return $cache[$translation];

        // check for cached translated version
        if ($translation and isActionPage($translation))
            return $cache[$action] = $translation;

        // Allow for, e.g. action=LikePages
        if (!isWikiWord($action))
            return $cache[$action] = false;

        // check for translated version (default language)
        global $LANG;
        if ($LANG != "en") {
            require_once("lib/WikiPlugin.php");
            require_once("lib/plugin/_WikiTranslation.php");
            $trans = new WikiPlugin__WikiTranslation();
            $trans->lang = $LANG;
	    $default = $trans->translate_to_en($action, $LANG);
            if ($default and isActionPage($default))
                return $cache[$action] = $default;
        } else {
            $default = $translation;
        }

        // check for english version
        if ($action != $translation and $action != $default) {
            if (isActionPage($action))
                return $cache[$action] = $action;
        }

        trigger_error("$action: Cannot find action page", E_USER_NOTICE);
        return $cache[$action] = false;
    }

    function action_browse () {
        $this->buffer_output();
        include_once("lib/display.php");
        displayPage($this);
    }

    function action_verify () {
        $this->action_browse();
    }

    function actionpage ($action) {
        $this->buffer_output();
        include_once("lib/display.php");
        actionPage($this, $action);
    }

    function adminActionSubpage ($subpage) {
        $page = _("PhpWikiAdministration")."/".$subpage;
        $action = $this->findActionPage($page);
        if ($action) {
            if (!$this->getArg('s'))
                $this->setArg('s', $this->getArg('pagename'));
            $this->setArg('verify', 1); // only for POST
            if ($this->getArg('action') != 'rename')
                $this->setArg('action',  $action);
            elseif($this->getArg('to') && empty($this->args['admin_rename'])) {
                $this->args['admin_rename']
                  = array('from'   => $this->getArg('s'),
                          'to'     => $this->getArg('to'),
                          'action' => 'select');
            }
            $this->actionpage($action);
        } else {
            trigger_error($page.": Cannot find action page", E_USER_WARNING);
        }
    }

    function action_chown () {
        $this->adminActionSubpage(_("Chown"));
    }

    function action_setacl () {
        $this->adminActionSubpage(_("SetAcl"));
    }

    function action_setaclsimple () {
        $this->adminActionSubpage(_("SetAclSimple"));
    }

    function action_rename () {
        $this->adminActionSubpage(_("Rename"));
    }

    function action_dump () {
        $action = $this->findActionPage(_("PageDump"));
        if ($action) {
            $this->actionpage($action);
        } else {
            // redirect to action=upgrade if admin?
            trigger_error(_("PageDump").": Cannot find action page", E_USER_WARNING);
        }
    }

    function action_diff () {
        $this->buffer_output();
        include_once "lib/diff.php";
        showDiff($this);
    }

    function action_search () {
    	// Decide between title or fulltextsearch (e.g. both buttons available).
        // Reformulate URL and redirect.
	$searchtype = $this->getArg('searchtype');
	$args = array('s' => $this->getArg('searchterm')
	                       ? $this->getArg('searchterm')
	                       : $this->getArg('s'));
        if ($searchtype == 'full' or $searchtype == 'fulltext') {
            $search_page = _("FullTextSearch");
        }
        elseif ($searchtype == 'external') {
            $s = $args['s'];
	    $link = new WikiPageName("Search:$s"); // Expand interwiki url. I use xapian-omega
            $this->redirect($link->url);
        }
        else {
            $search_page = _("TitleSearch");
	    $args['auto_redirect'] = 1;
        }
        $this->redirect(WikiURL($search_page, $args, 'absolute_url'));
    }

    function action_edit () {
        $this->buffer_output();
        include "lib/editpage.php";
        $e = new PageEditor ($this);
        $e->editPage();
    }

    function action_create () {
        $this->action_edit();
    }

    function action_viewsource () {
        $this->buffer_output();
        include "lib/editpage.php";
        $e = new PageEditor ($this);
        $e->viewSource();
    }

    function action_lock () {
        $page = $this->getPage();
        $page->set('locked', true);
        $this->_dbi->touch();
        // check ModeratedPage hook
        if ($moderated = $page->get('moderation')) {
            require_once("lib/WikiPlugin.php");
            $plugin = WikiPluginLoader::getPlugin("ModeratedPage");
            if ($retval = $plugin->lock_check($this, $page, $moderated))
                $this->setArg('errormsg', $retval);
        }
        // check if a link to ModeratedPage exists
        elseif ($action_page = $page->existLink(_("ModeratedPage"))) {
            require_once("lib/WikiPlugin.php");
            $plugin = WikiPluginLoader::getPlugin("ModeratedPage");
            if ($retval = $plugin->lock_add($this, $page, $action_page))
                $this->setArg('errormsg', $retval);
        }
        $this->action_browse();
    }

    function action_unlock () {
        $page = $this->getPage();
        $page->set('locked', false);
        $this->_dbi->touch();
        $this->action_browse();
    }

    function action_purge () {
        $pagename = $this->getArg('pagename');
        if (strstr($pagename, _("PhpWikiAdministration"))) {
            $this->action_browse();
        } else {
            include('lib/purgepage.php');
            PurgePage($this);
        }
    }

    function action_remove () {
        // This check is now redundant.
        //$user->requireAuth(WIKIAUTH_ADMIN);
        $pagename = $this->getArg('pagename');
        if (strstr($pagename, _("PhpWikiAdministration"))) {
            $this->action_browse();
        } else {
            include('lib/removepage.php');
            RemovePage($this);
        }
    }

    function action_xmlrpc () {
        include_once("lib/XmlRpcServer.php");
        $xmlrpc = new XmlRpcServer($this);
        $xmlrpc->service();
    }

    function action_soap () {
	if (defined("WIKI_SOAP") and WIKI_SOAP) // already loaded
	    return;
	/*
	  allow VIRTUAL_PATH or action=soap SOAP access
	 */
	include_once("SOAP.php");
    }

    function action_revert () {
        include_once "lib/loadsave.php";
        RevertPage($this);
    }

    function action_zip () {
        include_once("lib/loadsave.php");
        MakeWikiZip($this);
    }

    function action_ziphtml () {
        include_once("lib/loadsave.php");
        MakeWikiZipHtml($this);
        // I don't think it hurts to add cruft at the end of the zip file.
        echo "\n========================================================\n";
        echo "PhpWiki " . PHPWIKI_VERSION . "\n";
    }

    function action_dumpserial () {
        include_once("lib/loadsave.php");
        DumpToDir($this);
    }

    function action_dumphtml () {
        include_once("lib/loadsave.php");
        DumpHtmlToDir($this);
    }

    function action_upload () {
        include_once("lib/loadsave.php");
        LoadPostFile($this);
    }

    function action_upgrade () {
        include_once("lib/loadsave.php");
        include_once("lib/upgrade.php");
        DoUpgrade($this);
    }

    function action_loadfile () {
        include_once("lib/loadsave.php");
        LoadFileOrDir($this);
    }

    function action_pdf () {
    	include_once("lib/pdf.php");
    	ConvertAndDisplayPdf($this);
    }

    function action_captcha () {
        include_once "lib/Captcha.php";
        $captcha = new Captcha();
        $captcha->image ( $captcha->captchaword() );
    }

    function action_wikitohtml () {
       include_once("lib/WysiwygEdit/Wikiwyg.php");
       $wikitohtml = new WikiToHtml( $this->getArg("content") , $this);
       $wikitohtml->send();
    }

    function action_setpref () {
	$what = $this->getArg('pref');
	$value = $this->getArg('value');
	$prefs =& $this->_user->_prefs;
	$prefs->set($what, $value);
	$num = $this->_user->setPreferences($prefs);
    }
}

//FIXME: deprecated with ENABLE_PAGEPERM (?)
function is_safe_action ($action) {
    global $request;
    return $request->requiredAuthorityForAction($action) < WIKIAUTH_ADMIN;
}

function validateSessionPath() {
    // Try to defer any session.save_path PHP errors before any html
    // is output, which causes some versions of IE to display a blank
    // page (due to its strict mode while parsing a page?).
    if (! is_writeable(ini_get('session.save_path'))) {
        $tmpdir = (defined('SESSION_SAVE_PATH') and SESSION_SAVE_PATH) ? SESSION_SAVE_PATH : '/tmp';
        if (!is_writeable($tmpdir))
            $tmpdir = '/tmp';
        trigger_error
            (sprintf(_("%s is not writable."),
                     _("The session.save_path directory"))
             . "\n"
             . sprintf(_("Please ensure that %s is writable, or redefine %s in config/config.ini."),
                       sprintf(_("the session.save_path directory '%s'"),
                               ini_get('session.save_path')),
                       'SESSION_SAVE_PATH')
             . "\n"
             . sprintf(_("Attempting to use the directory '%s' instead."),
                       $tmpdir)
             , E_USER_NOTICE);
        if (! is_writeable($tmpdir)) {
            trigger_error
                (sprintf(_("%s is not writable."), $tmpdir)
                 . "\n"
                 . _("Users will not be able to sign in.")
                 , E_USER_NOTICE);
        }
        else
            @ini_set('session.save_path', $tmpdir);
    }
}

function main () {

    // latest supported: Red Hat Enterprise Linux ES release 4
    if (version_compare(PHP_VERSION, '4.3.9', '<')) {
        exit(_("Your PHP version is too old. You must have at least PHP 4.3.9"));
    }

    if ( !USE_DB_SESSION )
        validateSessionPath();

    global $request;
    if ((DEBUG & _DEBUG_APD) and extension_loaded("apd")) {
        //apd_set_session_trace(9);
        apd_set_pprof_trace();
    }

    // Postpone warnings
    global $ErrorManager;
    if (defined('E_STRICT')) // and (E_ALL & E_STRICT)) // strict php5?
        $ErrorManager->setPostponedErrorMask(E_NOTICE|E_USER_NOTICE|E_USER_WARNING|E_WARNING|E_STRICT|((check_php_version(5,3)) ? E_DEPRECATED : 0));
    else
        $ErrorManager->setPostponedErrorMask(E_NOTICE|E_USER_NOTICE|E_USER_WARNING|E_WARNING);
    $request = new WikiRequest();

    $action = $request->getArg('action');
    if (substr($action, 0, 3) != 'zip') {
    	if ($action == 'pdf')
    	    $ErrorManager->setPostponedErrorMask(-1); // everything
    	//else // reject postponing of warnings
        //    $ErrorManager->setPostponedErrorMask(E_NOTICE|E_USER_NOTICE);
    }

    /*
     * Allow for disabling of markup cache.
     * (Mostly for debugging ... hopefully.)
     *
     * See also <<WikiAdminUtils action=purge-cache>>
     */
    if (!defined('WIKIDB_NOCACHE_MARKUP')) {
        if ($request->getArg('nocache')) // 1 or purge
            define('WIKIDB_NOCACHE_MARKUP', $request->getArg('nocache'));
        else
            define('WIKIDB_NOCACHE_MARKUP', false); // redundant, but explicit
    }

    // Initialize with system defaults in case user not logged in.
    // Should this go into the constructor?
    $request->initializeTheme('default');
    $request->updateAuthAndPrefs();
    $request->initializeLang();

    //FIXME:
    //if ($user->is_authenticated())
    //  $LogEntry->user = $user->getId();

    // Memory optimization:
    // http://www.procata.com/blog/archives/2004/05/27/rephlux-and-php-memory-usage/
    // kill the global PEAR _PEAR_destructor_object_list
    if (!empty($_PEAR_destructor_object_list))
        $_PEAR_destructor_object_list = array();
    $request->possiblyDeflowerVirginWiki();

    $validators = array('wikiname' => WIKI_NAME,
                        'args'     => wikihash($request->getArgs()),
                        'prefs'    => wikihash($request->getPrefs()));
    if (CACHE_CONTROL == 'STRICT') {
        $dbi = $request->getDbh();
        $timestamp = $dbi->getTimestamp();
        $validators['mtime'] = $timestamp;
        $validators['%mtime'] = (int)$timestamp;
    }
    // FIXME: we should try to generate strong validators when possible,
    // but for now, our validator is weak, since equal validators do not
    // indicate byte-level equality of content.  (Due to DEBUG timing output, etc...)
    //
    // (If DEBUG if off, this may be a strong validator, but I'm going
    // to go the paranoid route here pending further study and testing.)
    // access hits and edit stats in the footer violate strong ETags also.
    if (1 or DEBUG) {
	$validators['%weak'] = true;
    }
    $request->setValidators($validators);

    $request->handleAction();

    if (DEBUG and DEBUG & _DEBUG_INFO) phpinfo(INFO_VARIABLES | INFO_MODULES);
    $request->finish();
}

if ((!FUSIONFORGE) || (forge_get_config('installation_environment') != 'production')) {
    if (defined('E_STRICT') and (E_ALL & E_STRICT)) // strict php5?
        error_reporting(E_ALL & ~E_STRICT);         // exclude E_STRICT
    else
        error_reporting(E_ALL); // php4
} else {
    error_reporting(E_ERROR);
}

// don't run the main loop for special requests (test, getimg, xmlrpc, soap, ...)
if (!defined('PHPWIKI_NOMAIN') or !PHPWIKI_NOMAIN)
    main();

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
