<?php //-*-php-*-
// $Id: IMAP.php 7956 2011-03-03 17:08:31Z vargenau $
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

class _IMAPPassUser
extends _PassUser
/**
 * Define the var IMAP_AUTH_HOST in config/config.ini (with port probably)
 *
 * Preferences are handled in _PassUser
 */
{
    function checkPass($submitted_password) {
        if (!$this->isValidName()) {
            if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => failed isValidName", E_USER_WARNING);
            trigger_error(_("Invalid username."),E_USER_WARNING);
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->_checkPassLength($submitted_password)) {
            if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => failed checkPassLength", E_USER_WARNING);
            return WIKIAUTH_FORBIDDEN;
        }
        $userid = $this->_userid;
        $mbox = @imap_open( "{" . IMAP_AUTH_HOST . "}",
                            $userid, $submitted_password, OP_HALFOPEN );
        if ($mbox) {
            imap_close($mbox);
            $this->_authmethod = 'IMAP';
            if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => ok", E_USER_WARNING);
            $this->_level = WIKIAUTH_USER;
            return $this->_level;
        } else {
            if ($submitted_password != "") { // if LENGTH 0 is allowed
                trigger_error(_("Unable to connect to IMAP server "). IMAP_AUTH_HOST,
                              E_USER_WARNING);
            }
        }
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::checkPass => wrong", E_USER_WARNING);

        return $this->_tryNextPass($submitted_password);
    }

    //CHECKME: this will not be okay for the auth policy strict
    function userExists() {
        return true;

        if ($this->checkPass($this->_prefs->get('passwd'))) {
            if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::userExists => true (pass ok)", E_USER_WARNING);
            return true;
        }
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::userExists => false (pass wrong)", E_USER_WARNING);
        return $this->_tryNextUser();
    }

    function mayChangePass() {
        if (DEBUG & _DEBUG_LOGIN) trigger_error(get_class($this)."::mayChangePass => false", E_USER_WARNING);
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
