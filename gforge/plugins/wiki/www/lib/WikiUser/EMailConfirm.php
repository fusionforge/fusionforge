<?php //-*-php-*-
rcs_id('$Id: EMailConfirm.php 6184 2008-08-22 10:33:41Z vargenau $');
/* Copyright (C) 2006 ReiniUrban
 * This file is part of PhpWiki. Terms and Conditions see LICENSE. (GPL2)
 */

class _EMailConfirmPassUser
extends _PassUser
/**
 * Unconfirmed users have ANON access, 
 * confirmed users are equal to passusers WIKIAUTH_USER.
 *
 * Users give their email at registration, phpwiki sends a link per email,
 * user clicks on url link to verify, user is confirmed.
 *
 * Preferences are handled in _PassUser
 */
{
    // This can only be called from _PassUser, because the parent class 
    // sets the pref methods, before this class is initialized.
    function _EMailConfirmPassUser($UserName='', $prefs=false, $file='') {
        if (!$this->_prefs and isa($this, "_EMailPassUser")) {
            if ($prefs) $this->_prefs = $prefs;
            if (!isset($this->_prefs->_method))
              _PassUser::_PassUser($UserName);
        }
        $this->_userid = $UserName;
        return $this;
    }

    function userExists() {
        if (!$this->isValidName($this->_userid)) {
            return $this->_tryNextUser();
        }
        $this->_authmethod = 'EMailConfirm';
        // check the prefs for emailVerified
        if ($this->_prefs->get('emailVerified'))
            return true;
        return $this->_tryNextUser();
    }
}

// $Log: not supported by cvs2svn $

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>