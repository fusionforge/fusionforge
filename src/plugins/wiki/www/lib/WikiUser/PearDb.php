<?php
/**
 * Copyright © 2004 Reini Urban
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

include_once 'lib/WikiUser/Db.php';

class _PearDbPassUser
    extends _DbPassUser
    /**
     * Pear DB methods
     * Now optimized not to use prepare, ...query(sprintf($sql,quote())) instead.
     * We use FETCH_MODE_ROW, so we don't need aliases in the auth_* SQL statements.
     *
     * @tables: pref
     */
{
    public $_authmethod = 'PearDb';

    function __construct($UserName = '', $prefs = false)
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        if (!$this->_prefs and is_a($this, "_PearDbPassUser")) {
            if ($prefs) {
                $this->_prefs = $prefs;
            }
        }
        if (!isset($this->_prefs->_method)) {
            _PassUser::__construct($UserName);
        } elseif (!$this->isValidName($UserName)) {
            trigger_error(_("Invalid username."), E_USER_WARNING);
            return false;
        }
        $this->_userid = $UserName;
        // make use of session data. generally we only initialize this every time,
        // but do auth checks only once
        $this->_auth_crypt_method = $request->_dbi->getAuthParam('auth_crypt_method');
        return $this;
    }

    function userExists()
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        $this->getAuthDbh();
        $dbh = &$this->_auth_dbi;
        if (!$dbh) { // needed?
            return $this->_tryNextUser();
        }
        if (!$this->isValidName()) {
            trigger_error(_("Invalid username."), E_USER_WARNING);
            return $this->_tryNextUser();
        }
        $dbi =& $request->_dbi;
        // Prepare the configured auth statements
        if ($dbi->getAuthParam('auth_check') and empty($this->_authselect)) {
            $this->_authselect = $this->prepare($dbi->getAuthParam('auth_check'),
                array("password", "userid"));
        }
        //NOTE: for auth_crypt_method='crypt' no special auth_user_exists is needed
        if (!$dbi->getAuthParam('auth_user_exists')
            and $this->_auth_crypt_method == 'crypt'
                and $this->_authselect
        ) {
            $rs = $dbh->query(sprintf($this->_authselect, $dbh->quote($this->_userid)));
            if ($rs->numRows())
                return true;
        } else {
            if (!$dbi->getAuthParam('auth_user_exists'))
                trigger_error(fmt("%s is missing", 'DBAUTH_AUTH_USER_EXISTS'),
                    E_USER_WARNING);
            $this->_authcheck = $this->prepare($dbi->getAuthParam('auth_user_exists'), "userid");
            $rs = $dbh->query(sprintf($this->_authcheck, $dbh->quote($this->_userid)));
            if ($rs->numRows())
                return true;
        }
        // User does not exist yet.
        // Maybe the user is allowed to create himself. Generally not wanted in
        // external databases, but maybe wanted for the wiki database, for performance
        // reasons
        if (empty($this->_authcreate) and $dbi->getAuthParam('auth_create')) {
            $this->_authcreate = $this->prepare($dbi->getAuthParam('auth_create'),
                array("password", "userid"));
        }
        if (!empty($this->_authcreate) and
            isset($GLOBALS['HTTP_POST_VARS']['auth']) and
                isset($GLOBALS['HTTP_POST_VARS']['auth']['passwd'])
        ) {
            $passwd = $GLOBALS['HTTP_POST_VARS']['auth']['passwd'];
            $dbh->simpleQuery(sprintf($this->_authcreate,
                $dbh->quote($passwd),
                $dbh->quote($this->_userid)));
            return true;
        }
        return $this->_tryNextUser();
    }

    function checkPass($submitted_password)
    {
        //global $DBAuthParams;
        $this->getAuthDbh();
        if (!$this->_auth_dbi) { // needed?
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->isValidName()) {
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->_checkPassLength($submitted_password)) {
            return WIKIAUTH_FORBIDDEN;
        }
        if (!isset($this->_authselect))
            $this->userExists();
        if (!isset($this->_authselect))
            trigger_error(fmt("Either %s is missing or DATABASE_TYPE != “%s”",
                    'DBAUTH_AUTH_CHECK', 'SQL'),
                E_USER_WARNING);

        //NOTE: for auth_crypt_method='crypt'  defined('ENCRYPTED_PASSWD',true) must be set
        $dbh = &$this->_auth_dbi;
        if ($this->_auth_crypt_method == 'crypt') {
            $stored_password = $dbh->getOne(sprintf($this->_authselect, $dbh->quote($this->_userid)));
            $result = $this->_checkPass($submitted_password, $stored_password);
        } else {
            // be position independent
            $okay = $dbh->getOne(sprintf($this->_authselect,
                $dbh->quote($submitted_password),
                $dbh->quote($this->_userid)));
            $result = !empty($okay);
        }

        if ($result) {
            $this->_level = WIKIAUTH_USER;
            return $this->_level;
        } elseif (USER_AUTH_POLICY === 'strict') {
            $this->_level = WIKIAUTH_FORBIDDEN;
            return $this->_level;
        } else {
            return $this->_tryNextPass($submitted_password);
        }
    }

    function mayChangePass()
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        return $request->_dbi->getAuthParam('auth_update');
    }

    function storePass($submitted_password)
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        if (!$this->isValidName()) {
            return false;
        }
        $this->getAuthDbh();
        $dbh = &$this->_auth_dbi;
        $dbi =& $request->_dbi;
        if ($dbi->getAuthParam('auth_update') and empty($this->_authupdate)) {
            $this->_authupdate = $this->prepare($dbi->getAuthParam('auth_update'),
                array("password", "userid"));
        }
        if (empty($this->_authupdate)) {
            trigger_error(fmt("Either %s is missing or DATABASE_TYPE != “%s”",
                    'DBAUTH_AUTH_UPDATE', 'SQL'),
                E_USER_WARNING);
            return false;
        }

        if ($this->_auth_crypt_method == 'crypt') {
            $submitted_password = crypt($submitted_password);
        }
        $dbh->simpleQuery(sprintf($this->_authupdate,
            $dbh->quote($submitted_password), $dbh->quote($this->_userid)));
        return true;
    }
}
