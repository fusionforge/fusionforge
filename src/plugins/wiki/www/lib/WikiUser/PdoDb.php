<?php
/**
 * Copyright © 2004, 2005 Reini Urban
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

class _PdoDbPassUser
    extends _DbPassUser
    /**
     * PDO DB methods (PHP5)
     *   prepare, bind, execute.
     * We use numerical FETCH_MODE_ROW, so we don't need aliases in the auth_* SQL statements.
     *
     * @tables: user
     * @tables: pref
     */
{
    public $_authmethod = 'PDODb';

    function __construct($UserName = '', $prefs = false)
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        if (!$this->_prefs and is_a($this, "_PdoDbPassUser")) {
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
        if ($dbi->getAuthParam('auth_check') and empty($this->_authselect)) {
            try {
                $this->_authselect = $dbh->prepare($dbi->getAuthParam('auth_check'));
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
        }
        //NOTE: for auth_crypt_method='crypt' no special auth_user_exists is needed
        if (!$dbi->getAuthParam('auth_user_exists')
            and $this->_auth_crypt_method == 'crypt'
                and $this->_authselect
        ) {
            try {
                $this->_authselect->bindParam("userid", $this->_userid, PDO::PARAM_STR, 48);
                $this->_authselect->execute();
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
            if ($this->_authselect->fetchColumn())
                return true;
        } else {
            if (!$dbi->getAuthParam('auth_user_exists'))
                trigger_error(fmt("%s is missing", 'DBAUTH_AUTH_USER_EXISTS'),
                    E_USER_WARNING);
            $this->_authcheck = $dbh->prepare($dbi->getAuthParam('auth_check'));
            $this->_authcheck->bindParam("userid", $this->_userid, PDO::PARAM_STR, 48);
            $this->_authcheck->execute();
            if ($this->_authcheck->fetchColumn())
                return true;
        }
        // User does not exist yet.
        // Maybe the user is allowed to create himself. Generally not wanted in
        // external databases, but maybe wanted for the wiki database, for performance
        // reasons
        if (empty($this->_authcreate) and $dbi->getAuthParam('auth_create')) {
            try {
                $this->_authcreate = $dbh->prepare($dbi->getAuthParam('auth_create'));
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
        }
        if (!empty($this->_authcreate) and
            isset($GLOBALS['HTTP_POST_VARS']['auth']) and
                isset($GLOBALS['HTTP_POST_VARS']['auth']['passwd'])
        ) {
            $passwd = $GLOBALS['HTTP_POST_VARS']['auth']['passwd'];
            try {
                $this->_authcreate->bindParam("userid", $this->_userid, PDO::PARAM_STR, 48);
                $this->_authcreate->bindParam("password", $passwd, PDO::PARAM_STR, 48);
                $rs = $this->_authselect->execute();
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
            if ($rs)
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
        if ($this->_auth_crypt_method == 'crypt') {
            try {
                $this->_authselect->bindParam("userid", $this->_userid, PDO::PARAM_STR, 48);
                $this->_authselect->execute();
                $rs = $this->_authselect->fetch(PDO::FETCH_BOTH);
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
            $stored_password = @$rs[0];
            $result = $this->_checkPass($submitted_password, $stored_password);
        } else {
            try {
                $this->_authselect->bindParam("password", $submitted_password, PDO::PARAM_STR, 48);
                $this->_authselect->bindParam("userid", $this->_userid, PDO::PARAM_STR, 48);
                $this->_authselect->execute();
                $rs = $this->_authselect->fetch(PDO::FETCH_BOTH);
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
            $okay = @$rs[0];
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
            try {
                $this->_authupdate = $dbh->prepare($dbi->getAuthParam('auth_update'));
            } catch (PDOException $e) {
                trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
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
        try {
            $this->_authupdate->bindParam("password", $submitted_password, PDO::PARAM_STR, 48);
            $this->_authupdate->bindParam("userid", $this->_userid, PDO::PARAM_STR, 48);
            $this->_authupdate->execute();
        } catch (PDOException $e) {
            trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
        return true;
    }
}
