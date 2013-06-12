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

/** Without stored password. A _BogoLoginPassUser with password
 *  is automatically upgraded to a PersonalPagePassUser.
 */
class _BogoLoginPassUser extends _PassUser
{

    public $_authmethod = 'BogoLogin';

    function userExists()
    {
        if (isWikiWord($this->_userid)) {
            $this->_level = WIKIAUTH_BOGO;
            return true;
        } else {
            $this->_level = WIKIAUTH_ANON;
            return false;
        }
    }

    /** A BogoLoginUser requires no password at all
     *  But if there's one stored, we override it with the PersonalPagePassUser instead
     */
    function checkPass($submitted_password)
    {
        if ($this->_prefs->get('passwd')) {
            if (isset($this->_prefs->_method) and $this->_prefs->_method == 'HomePage') {
                $user = new _PersonalPagePassUser($this->_userid, $this->_prefs);
                if ($user->checkPass($submitted_password)) {
                    UpgradeUser($this, $user);
                    $this->_level = WIKIAUTH_USER;
                    return $this->_level;
                } else {
                    $this->_level = WIKIAUTH_ANON;
                    return $this->_level;
                }
            } else {
                $stored_password = $this->_prefs->get('passwd');
                if ($this->_checkPass($submitted_password, $stored_password)) {
                    $this->_level = WIKIAUTH_USER;
                    return $this->_level;
                } elseif (USER_AUTH_POLICY === 'strict') {
                    $this->_level = WIKIAUTH_FORBIDDEN;
                    return $this->_level;
                } else {
                    return $this->_tryNextPass($submitted_password);
                }
            }
        }
        if (isWikiWord($this->_userid)) {
            $this->_level = WIKIAUTH_BOGO;
        } else {
            $this->_level = WIKIAUTH_ANON;
        }
        return $this->_level;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
