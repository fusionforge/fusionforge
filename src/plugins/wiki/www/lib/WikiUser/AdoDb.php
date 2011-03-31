<?php //-*-php-*-
// $Id: AdoDb.php 7956 2011-03-03 17:08:31Z vargenau $
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

include_once("lib/WikiUser/Db.php");

class _AdoDbPassUser
extends _DbPassUser
/**
 * ADODB methods
 * Simple sprintf, no prepare.
 *
 * Warning: Since we use FETCH_MODE_ASSOC (string hash) and not the also faster
 * FETCH_MODE_ROW (numeric), we have to use the correct aliases in auth_* sql statements!
 *
 * TODO: Change FETCH_MODE in adodb WikiDB sublasses.
 *
 * @tables: user
 */
{
    var $_authmethod = 'AdoDb';
    function _AdoDbPassUser($UserName='',$prefs=false) {
        if (!$this->_prefs and isa($this,"_AdoDbPassUser")) {
            if ($prefs) $this->_prefs = $prefs;
            if (!isset($this->_prefs->_method))
              _PassUser::_PassUser($UserName);
        }
        if (!$this->isValidName($UserName)) {
            trigger_error(_("Invalid username."),E_USER_WARNING);
            return false;
        }
        $this->_userid = $UserName;
        $this->getAuthDbh();
        $this->_auth_crypt_method = $GLOBALS['request']->_dbi->getAuthParam('auth_crypt_method');
        // Don't prepare the configured auth statements anymore
        return $this;
    }

    function getPreferences() {
        // override the generic slow method here for efficiency
        _AnonUser::getPreferences();
        $this->getAuthDbh();
        if (isset($this->_prefs->_select)) {
            $dbh = & $this->_auth_dbi;
            $rs = $dbh->Execute(sprintf($this->_prefs->_select, $dbh->qstr($this->_userid)));
            if ($rs->EOF) {
                $rs->Close();
            } else {
                $prefs_blob = @$rs->fields['prefs'];
                $rs->Close();
                if ($restored_from_db = $this->_prefs->retrieve($prefs_blob)) {
                    $updated = $this->_prefs->updatePrefs($restored_from_db);
                    //$this->_prefs = new UserPreferences($restored_from_db);
                    return $this->_prefs;
                }
            }
        }
        if (!empty($this->_HomePagehandle)) {
            if ($restored_from_page = $this->_prefs->retrieve
                ($this->_HomePagehandle->get('pref'))) {
                $updated = $this->_prefs->updatePrefs($restored_from_page);
                //$this->_prefs = new UserPreferences($restored_from_page);
                return $this->_prefs;
            }
        }
        return $this->_prefs;
    }

    function setPreferences($prefs, $id_only=false) {
        // if the prefs are changed
        if (_AnonUser::setPreferences($prefs, 1)) {
            global $request;
            $packed = $this->_prefs->store();
            //$user = $request->_user;
            //unset($user->_auth_dbi);
            if (!$id_only and isset($this->_prefs->_update)) {
                $this->getAuthDbh();
                $dbh = &$this->_auth_dbi;
                // check if the user already exists (not needed with mysql REPLACE)
                $rs = $dbh->Execute(sprintf($this->_prefs->_select, $dbh->qstr($this->_userid)));
                if ($rs->EOF) {
                    $rs->Close();
                    $prefs_blob = false;
                } else {
                    $prefs_blob = @$rs->fields['prefs'];
                    $rs->Close();
                }
                if ($prefs_blob) {
                    $db_result = $dbh->Execute(sprintf($this->_prefs->_update,
                                                       $dbh->qstr($packed),
                                                       $dbh->qstr($this->_userid)));
                } else {
                    // Otherwise, insert a record for them and set it to the defaults.
                    $dbi = $request->getDbh();
                    $this->_prefs->_insert = $this->prepare($dbi->getAuthParam('pref_insert'),
                                                            array("pref_blob", "userid"));
                    $db_result = $dbh->Execute(sprintf($this->_prefs->_insert,
                                                       $dbh->qstr($packed),
                                                       $dbh->qstr($this->_userid)));
                }
                $db_result->Close();
                // delete pageprefs:
                if ($this->_HomePagehandle and $this->_HomePagehandle->get('pref'))
                    $this->_HomePagehandle->set('pref', '');
            } else {
                //store prefs in homepage, not in cookie
                if ($this->_HomePagehandle and !$id_only)
                    $this->_HomePagehandle->set('pref', $packed);
            }
            return count($this->_prefs->unpack($packed));
        }
        return 0;
    }

    function userExists() {
        $this->getAuthDbh();
        $dbh = &$this->_auth_dbi;
        if (!$dbh) { // needed?
            return $this->_tryNextUser();
        }
        if (!$this->isValidName()) {
            return $this->_tryNextUser();
        }
        $dbi =& $GLOBALS['request']->_dbi;
        // Prepare the configured auth statements
        if ($dbi->getAuthParam('auth_check') and empty($this->_authselect)) {
            $this->_authselect = $this->prepare($dbi->getAuthParam('auth_check'),
                                                array("password", "userid"));
        }
        //NOTE: for auth_crypt_method='crypt' no special auth_user_exists is needed
        if ( !$dbi->getAuthParam('auth_user_exists')
             and $this->_auth_crypt_method == 'crypt'
             and $this->_authselect)
        {
            $rs = $dbh->Execute(sprintf($this->_authselect, $dbh->qstr($this->_userid)));
            if (!$rs->EOF) {
                $rs->Close();
                return true;
            } else {
                $rs->Close();
            }
        }
        else {
            if (! $dbi->getAuthParam('auth_user_exists'))
                trigger_error(fmt("%s is missing", 'DBAUTH_AUTH_USER_EXISTS'),
                              E_USER_WARNING);
            $this->_authcheck = $this->prepare($dbi->getAuthParam('auth_user_exists'),
                                               'userid');
            $rs = $dbh->Execute(sprintf($this->_authcheck, $dbh->qstr($this->_userid)));
            if (!$rs->EOF) {
                $rs->Close();
                return true;
            } else {
                $rs->Close();
            }
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
            isset($GLOBALS['HTTP_POST_VARS']['auth']['passwd']))
        {
            $passwd = $GLOBALS['HTTP_POST_VARS']['auth']['passwd'];
            $dbh->Execute(sprintf($this->_authcreate,
                                  $dbh->qstr($passwd),
                                  $dbh->qstr($this->_userid)));
            return true;
        }

        return $this->_tryNextUser();
    }

    function checkPass($submitted_password) {
        //global $DBAuthParams;
        $this->getAuthDbh();
        if (!$this->_auth_dbi) {  // needed?
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->isValidName()) {
            trigger_error(_("Invalid username."),E_USER_WARNING);
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->_checkPassLength($submitted_password)) {
            return WIKIAUTH_FORBIDDEN;
        }
        $dbh =& $this->_auth_dbi;
        $dbi =& $GLOBALS['request']->_dbi;
        if (empty($this->_authselect) and $dbi->getAuthParam('auth_check')) {
            $this->_authselect = $this->prepare($dbi->getAuthParam('auth_check'),
                                                array("password", "userid"));
        }
        if (!isset($this->_authselect))
            $this->userExists();
        if (!isset($this->_authselect))
            trigger_error(fmt("Either %s is missing or DATABASE_TYPE != '%s'",
                              'DBAUTH_AUTH_CHECK', 'ADODB'),
                          E_USER_WARNING);
        //NOTE: for auth_crypt_method='crypt'  defined('ENCRYPTED_PASSWD',true) must be set
        if ($this->_auth_crypt_method == 'crypt') {
            $rs = $dbh->Execute(sprintf($this->_authselect,
                                            $dbh->qstr($this->_userid)));
            if (!$rs->EOF) {
                $stored_password = $rs->fields['password'];
                $rs->Close();
                $result = $this->_checkPass($submitted_password, $stored_password);
            } else {
                $rs->Close();
                $result = false;
            }
        } else {
            $rs = $dbh->Execute(sprintf($this->_authselect,
                                        $dbh->qstr($submitted_password),
                                        $dbh->qstr($this->_userid)));
            if (isset($rs->fields['ok']))
                $okay = $rs->fields['ok'];
            elseif (isset($rs->fields[0]))
                $okay = $rs->fields[0];
            else {
                if (is_array($rs->fields))
                    $okay = reset($rs->fields);
                else
                    $okay = false;
            }
            $rs->Close();
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

    function mayChangePass() {
        return $GLOBALS['request']->_dbi->getAuthParam('auth_update');
    }

    function storePass($submitted_password) {
        $this->getAuthDbh();
        $dbh = &$this->_auth_dbi;
        $dbi =& $GLOBALS['request']->_dbi;
        if ($dbi->getAuthParam('auth_update') and empty($this->_authupdate)) {
            $this->_authupdate = $this->prepare($dbi->getAuthParam('auth_update'),
                                                array("password", "userid"));
        }
        if (!isset($this->_authupdate)) {
            trigger_error(fmt("Either %s is missing or DATABASE_TYPE != '%s'",
                              'DBAUTH_AUTH_UPDATE', 'ADODB'),
                          E_USER_WARNING);
            return false;
        }

        if ($this->_auth_crypt_method == 'crypt') {
            if (function_exists('crypt'))
                $submitted_password = crypt($submitted_password);
        }
        $rs = $dbh->Execute(sprintf($this->_authupdate,
                                    $dbh->qstr($submitted_password),
                                    $dbh->qstr($this->_userid)
                                    ));
        $rs->Close();
        return $rs;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
