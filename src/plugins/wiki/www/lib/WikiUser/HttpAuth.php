<?php //-*-php-*-
// $Id: HttpAuth.php 7956 2011-03-03 17:08:31Z vargenau $
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

/**
 * We have two possibilities here.
 * 1) The webserver location is already HTTP protected. Usually Basic, but also
 *    NTLM or Digest. Then just use this username and do nothing.
 * 2) The webserver location is not protected, so we enforce basic HTTP Protection
 *    by sending a 401 error and let the client display the login dialog.
 *    This makes only sense if HttpAuth is the last method in USER_AUTH_ORDER,
 *    since the other methods cannot be transparently called after this enforced
 *    external dialog.
 *    Try the available auth methods (most likely Bogo) and sent this header back.
 *    header('Authorization: Basic '.base64_encode("$userid:$passwd")."\r\n";
 */
class _HttpAuthPassUser
extends _PassUser
{
    function _HttpAuthPassUser($UserName='', $prefs=false) {
        if ($prefs) $this->_prefs = $prefs;
        if (!isset($this->_prefs->_method))
           _PassUser::_PassUser($UserName);
        if ($UserName) $this->_userid = $UserName;
        $this->_authmethod = 'HttpAuth';

        // Is this double check really needed?
        // It is not expensive so we keep it for now.
        if ($this->userExists()) {
            return $this;
        } else {
            return $GLOBALS['ForbiddenUser'];
        }
    }

    // FIXME! This doesn't work yet!
    // Allow httpauth by other method: Admin for now only
    function _fake_auth($userid, $passwd) {
            return false;

        header('WWW-Authenticate: Basic realm="'.WIKI_NAME.'"');
        header("Authorization: Basic ".base64_encode($userid.":".$passwd));
        if (!isset($_SERVER))
            $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
        $GLOBALS['REMOTE_USER'] = $userid;
        $_SERVER['PHP_AUTH_USER'] = $userid;
        $_SERVER['PHP_AUTH_PW'] = $passwd;
        //$GLOBALS['request']->setStatus(200);
    }

    function logout() {
        if (!isset($_SERVER))
            $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
        // Maybe we should random the realm to really force a logout.
        // But the next login will fail.
        // better_srand(); $realm = microtime().rand();
        // TODO: On AUTH_TYPE=NTLM this will fail. Only Basic supported so far.
        header('WWW-Authenticate: Basic realm="'.WIKI_NAME.'"');
        if (strstr(php_sapi_name(), 'apache'))
            header('HTTP/1.0 401 Unauthorized');
        else
            header("Status: 401 Access Denied"); //IIS and CGI need that
        unset($GLOBALS['REMOTE_USER']);
        unset($_SERVER['PHP_AUTH_USER']);
        unset($_SERVER['PHP_AUTH_PW']);
    }

    function _http_username() {
        if (!isset($_SERVER))
            $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
        if (!empty($_SERVER['PHP_AUTH_USER']))
            return $_SERVER['PHP_AUTH_USER'];
        if (!empty($_SERVER['REMOTE_USER']))
            return $_SERVER['REMOTE_USER'];
        if (!empty($GLOBALS['HTTP_ENV_VARS']['REMOTE_USER']))
            return $GLOBALS['HTTP_ENV_VARS']['REMOTE_USER'];
        if (!empty($GLOBALS['REMOTE_USER']))
            return $GLOBALS['REMOTE_USER'];
        // IIS + Basic
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            list($userid, $passwd) = explode(':',
                base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            return $userid;
        }
        return '';
    }

    // force http auth authorization
    function userExists() {
        if (!isset($_SERVER))
            $_SERVER =& $GLOBALS['HTTP_SERVER_VARS'];
        $username = $this->_http_username();
        if (strstr($username, "\\")
            and isset($_SERVER['AUTH_TYPE'])
            and $_SERVER['AUTH_TYPE'] == 'NTLM')
        {
            // allow domain\user, change userid to domain/user
            $username = str_ireplace("\\\\", "\\", $username); // php bug with _SERVER
            $username = str_ireplace("\\", SUBPAGE_SEPARATOR, $username);
            $this->_userid = str_ireplace("\\", SUBPAGE_SEPARATOR, $this->_userid);
        }
        // FIXME: if AUTH_TYPE = NTLM there's a domain\\name <> domain\name mismatch
        if (empty($username)
            or strtolower($username) != strtolower($this->_userid))
        {
            $this->logout();
            $user = $GLOBALS['ForbiddenUser'];
            $user->_userid = $this->_userid =  "";
            $this->_level = WIKIAUTH_FORBIDDEN;
            return $user;
            //exit;
        }
        $this->_userid = $username;
        // we should check if he is a member of admin,
        // because HttpAuth has its own logic.
        $this->_level = WIKIAUTH_USER;
        if ($this->isAdmin())
            $this->_level = WIKIAUTH_ADMIN;
        return $this;
    }

    // ignore password, this is checked by the webservers http auth.
    function checkPass($submitted_password) {
        return $this->userExists()
            ? ($this->isAdmin() ? WIKIAUTH_ADMIN : WIKIAUTH_USER)
            : WIKIAUTH_ANON;
    }

    function mayChangePass() {
        return false;
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
