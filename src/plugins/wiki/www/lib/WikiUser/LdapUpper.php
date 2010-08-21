<?php //-*-php-*-
// rcs_id('$Id: LdapUpper.php 7640 2010-08-11 12:33:25Z vargenau $');
/*
 * Copyright (C) 2007,2009 Reini Urban
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
include_once("lib/WikiUser/LDAP.php");

/**
 * Overrides for LDAP (Windows domain) usernames:
 *   Search lowercase, but convert it then to uppercase to match the WINDOWS name.
 * Define the vars LDAP_AUTH_HOST, LDAP_BASE_DN, LDAP_SEARCH_FILTER in config/config.ini
 * Preferences are handled in _PassUser
 */
class _LdapUpperPassUser
extends _LDAPPassUser
{

    function checkPass($submitted_password) {
        return parent::checkPass($submitted_password);
    }

    function UserName() {
        if (!empty($this->_userid)) {
            $this->_userid = trim(strtoupper($this->_userid));
            if (!empty($this->_HomePagehandle) and is_object($this->_HomePagehandle))
                $this->_HomePagehandle->_pagename = $this->_userid;
            return strtoupper($this->_userid);
        }
    }

    function userExists() {
        // lowercase check and uppercase visibility
        $this->_userid = trim(strtoupper($this->_userid));
        return parent::userExists();
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
