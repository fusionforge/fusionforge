<?php //-*-php-*-
// $Id: EMailConfirm.php 7956 2011-03-03 17:08:31Z vargenau $
/*
 * Copyright (C) 2006 ReiniUrban
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

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
