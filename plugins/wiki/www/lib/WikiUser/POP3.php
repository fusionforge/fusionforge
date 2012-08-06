<?php //-*-php-*-
// $Id: POP3.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
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

require_once("lib/WikiUser/IMAP.php");

class _POP3PassUser
extends _IMAPPassUser {
/**
 * Define the var POP3_AUTH_HOST in config/config.ini
 * Preferences are handled in _PassUser
 */
    function checkPass($submitted_password) {
        if (!$this->isValidName()) {
            trigger_error(_("Invalid username."), E_USER_WARNING);
            if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => failed isValidName", E_USER_WARNING);
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->_checkPassLength($submitted_password)) {
            if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => failed checkPassLength", E_USER_WARNING);
            return WIKIAUTH_FORBIDDEN;
        }
        $userid = $this->_userid;
        $pass = $submitted_password;
        $host = defined('POP3_AUTH_HOST') ? POP3_AUTH_HOST : 'localhost:110';
        if (defined('POP3_AUTH_PORT'))
            $port = POP3_AUTH_PORT;
        elseif (strstr($host,':')) {
            list(,$port) = explode(':', $host);
        } else {
            $port = 110;
        }
        $retval = false;
        $fp = fsockopen($host, $port, $errno, $errstr, 10);
        if ($fp) {
            // Get welcome string
            $line = fgets($fp, 1024);
            if (! strncmp("+OK", $line, 3)) {
                // Send user name
                fputs($fp, "user $userid\n");
                // Get response
                $line = fgets($fp, 1024);
                if (! strncmp("+OK", $line, 3)) {
                    // Send password
                    fputs($fp, "pass $pass\n");
                    // Get response
                    $line = fgets($fp, 1024);
                    if (! strncmp("+OK", $line, 3)) {
                        $retval = true;
                    }
                }
            }
            // quit the connection
            fputs($fp, "quit\n");
            // Get the sayonara message
            $line = fgets($fp, 1024);
            fclose($fp);
        } else {
            trigger_error(_("Couldn't connect to %s","POP3_AUTH_HOST ".$host.':'.$port),
                          E_USER_WARNING);
        }
        $this->_authmethod = 'POP3';
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => $retval", E_USER_WARNING);
        if ($retval) {
            $this->_level = WIKIAUTH_USER;
        } else {
            $this->_level = WIKIAUTH_ANON;
        }
        return $this->_level;
    }

    function __userExists() {
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::userExists => true (dummy)", E_USER_WARNING);
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
