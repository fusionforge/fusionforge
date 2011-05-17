<?php //-*-php-*-
// rcs_id('$Id: Facebook.php 7640 2010-08-11 12:33:25Z vargenau $');
/*
 * Copyright (C) 2009 Reini Urban
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
 *
 * From http://developeronline.blogspot.com/2008/10/using-perl-against-facebook-part-i.html:
 * GET 'http://www.facebook.com/login.php', and rest our virtual browser there to collect the cookies
 * POST to 'https://login.facebook.com/login.php' with the proper parameters
 */

// requires the openssl extension
require_once("lib/HttpClient.php");

class _FacebookPassUser
extends _PassUser {
    /**
     * Preferences are handled in _PassUser
     */
    function checkPass($password) {
        $userid = $this->_userid;
        if (!loadPhpExtension('openssl')) {
            trigger_error(
                sprintf(_("The PECL %s extension cannot be loaded."), "openssl")
                 . sprintf(_(" %s AUTH ignored."), 'Facebook'),
                 E_USER_WARNING);
            return $this->_tryNextUser();
        }
        $web = new HttpClient("www.facebook.com", 80);
        if (DEBUG & _DEBUG_LOGIN) $web->setDebug(true);
        // collect cookies from http://www.facebook.com/login.php
        $web->persist_cookies = true;
        $web->cookie_host = 'www.facebook.com';
        $firstlogin = $web->get("/login.php");
        if (!$firstlogin) {
            if (DEBUG & (_DEBUG_LOGIN | _DEBUG_VERBOSE))
                trigger_error(sprintf(_("Facebook connect failed with %d %s"),
                                      $web->status, $web->errormsg),
                              E_USER_WARNING);
        }
        // Switch from http to https://login.facebook.com/login.php
        $web->port = 443;
        $web->host = 'login.facebook.com';
        if (!($retval = $web->post("/login.php", array('user'=>$userid, 'pass'=>$password)))) {
            if (DEBUG & (_DEBUG_LOGIN | _DEBUG_VERBOSE))
                trigger_error(sprintf(_("Facebook login failed with %d %s"),
                                      $web->status, $web->errormsg),
                              E_USER_WARNING);
        }
        $this->_authmethod = 'Facebook';
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => $retval",
                                                E_USER_WARNING);
        if ($retval) {
            $this->_level = WIKIAUTH_USER;
        } else {
            $this->_level = WIKIAUTH_ANON;
        }
        return $this->_level;
    }

    // TODO: msearch facebook for the username
    function userExists() {
        if (!loadPhpExtension('openssl')) {
            trigger_error(
                sprintf(_("The PECL %s extension cannot be loaded."), "openssl")
                 . sprintf(_(" %s AUTH ignored."), 'Facebook'),
                 E_USER_WARNING);
            return $this->_tryNextUser();
        }
        if (DEBUG & _DEBUG_LOGIN)
            trigger_error(get_class($this)."::userExists => true (dummy)", E_USER_WARNING);
        return true;
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
