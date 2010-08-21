<?php //-*-php-*-
// rcs_id('$Id: Session.php 7640 2010-08-11 12:33:25Z vargenau $');
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
 * Support reuse of existing user session from another application.
 * You have to define which session variable holds the userid, and
 * at what level is that user then. 1: BogoUser, 2: PassUser
 *   define('AUTH_SESS_USER','userid');
 *   define('AUTH_SESS_LEVEL',2);
 */
class _SessionPassUser
extends _PassUser
{
    function _SessionPassUser($UserName='',$prefs=false) {
        if ($prefs) $this->_prefs = $prefs;
        if (!defined("AUTH_SESS_USER") or !defined("AUTH_SESS_LEVEL")) {
            trigger_error(
                "AUTH_SESS_USER or AUTH_SESS_LEVEL is not defined for the SessionPassUser method",
                E_USER_ERROR);
            exit;
        }
        $sess =& $GLOBALS['HTTP_SESSION_VARS'];
        // user hash: "[user][userid]" or object "user->id"
        if (strstr(AUTH_SESS_USER,"][")) {
            $sess = $GLOBALS['HTTP_SESSION_VARS'];
            // recurse into hashes: "[user][userid]", sess = sess[user] => sess = sess[userid]
            foreach (explode("][", AUTH_SESS_USER) as $v) {
                $v = str_replace(array("[","]"),'',$v);
                $sess = $sess[$v];
            }
            $this->_userid = $sess;
        } elseif (strstr(AUTH_SESS_USER,"->")) {
            // object "user->id" (no objects inside hashes supported!)
            list($obj,$key) = explode("->", AUTH_SESS_USER);
            $this->_userid = $sess[$obj]->$key;
        } else {
            $this->_userid = $sess[AUTH_SESS_USER];
        }
        if (!isset($this->_prefs->_method))
           _PassUser::_PassUser($this->_userid);
        $this->_level = AUTH_SESS_LEVEL;
        $this->_authmethod = 'Session';
    }
    function userExists() {
        return !empty($this->_userid);
    }
    function checkPass($submitted_password) {
        return $this->userExists() and $this->_level > -1;
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
