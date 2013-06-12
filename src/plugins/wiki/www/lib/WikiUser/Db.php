<?php

/*
 * Copyright (C) 2004 ReiniUrban
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
 */

/**
 * Baseclass for PearDB and ADODB PassUser's
 * Authenticate against a database, to be able to use shared users.
 *   internal: no different $DbAuthParams['dsn'] defined, or
 *   external: different $DbAuthParams['dsn']
 * The magic is done in the symbolic SQL statements in config/config.ini, similar to
 * libnss-mysql.
 *
 * We support only the SQL and ADODB backends.
 * The other WikiDB backends (flat, cvs, dba, ...) should be used for pages,
 * not for auth stuff. If one would like to use e.g. dba for auth, he should
 * use PearDB (SQL) with the right $DBAuthParam['auth_dsn'].
 * (Not supported yet, since we require SQL. SQLite would make since when
 * it will come to PHP)
 *
 * @tables: user, pref
 *
 * Preferences are handled in the parent class _PassUser, because the
 * previous classes may also use DB pref_select and pref_update.
 *
 * Flat files auth is handled by the auth method "File".
 */
class _DbPassUser
    extends _PassUser
{
    public $_authselect, $_authupdate, $_authcreate;

    // This can only be called from _PassUser, because the parent class
    // sets the auth_dbi and pref methods, before this class is initialized.
    function _DbPassUser($UserName = '', $prefs = false)
    {
        if (!$this->_prefs) {
            if ($prefs) $this->_prefs = $prefs;
        }
        if (!isset($this->_prefs->_method))
            _PassUser::_PassUser($UserName);
        elseif (!$this->isValidName($UserName)) {
            trigger_error(_("Invalid username."), E_USER_WARNING);
            return false;
        }
        $this->_authmethod = 'Db';
        //$this->getAuthDbh();
        //$this->_auth_crypt_method = @$GLOBALS['DBAuthParams']['auth_crypt_method'];
        $dbi =& $GLOBALS['request']->_dbi;
        $dbtype = $dbi->getParam('dbtype');
        if ($dbtype == 'ADODB') {
            include_once 'lib/WikiUser/AdoDb.php';
            return new _AdoDbPassUser($UserName, $this->_prefs);
        } elseif ($dbtype == 'SQL') {
            include_once 'lib/WikiUser/PearDb.php';
            return new _PearDbPassUser($UserName, $this->_prefs);
        } elseif ($dbtype == 'PDO') {
            include_once 'lib/WikiUser/PdoDb.php';
            return new _PdoDbPassUser($UserName, $this->_prefs);
        }
        return false;
    }

    /* Since we properly quote the username, we allow most chars here.
       Just " ; and ' is forbidden, max length: 48 as defined in the schema.
    */
    function isValidName($userid = false)
    {
        if (!$userid) $userid = $this->_userid;
        if (strcspn($userid, ";'\"") != strlen($userid)) return false;
        if (strlen($userid) > 48) return false;
        return true;
    }

    function mayChangePass()
    {
        return !isset($this->_authupdate);
    }

}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
