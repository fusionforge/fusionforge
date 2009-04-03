<?php //-*-php-*-
rcs_id('$Id: IMAP.php 6184 2008-08-22 10:33:41Z vargenau $');
/* Copyright (C) 2004 $ThePhpWikiProgrammingTeam
 * This file is part of PhpWiki. Terms and Conditions see LICENSE. (GPL2)
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

// $Log: not supported by cvs2svn $
// Revision 1.6  2006/08/25 22:35:50  rurban
// fix checkPass call in userExists
//
// Revision 1.5  2005/04/25 19:46:08  rurban
// trivial tuning by michael pruitt. Patch #1120185
//
// Revision 1.4  2004/12/26 17:11:17  rurban
// just copyright
//
// Revision 1.3  2004/12/20 16:05:01  rurban
// gettext msg unification
//
// Revision 1.2  2004/12/19 00:58:02  rurban
// Enforce PASSWORD_LENGTH_MINIMUM in almost all PassUser checks,
// Provide an errormessage if so. Just PersonalPage and BogoLogin not.
// Simplify httpauth logout handling and set sessions for all methods.
// fix main.php unknown index "x" getLevelDescription() warning.
//
// Revision 1.1  2004/11/01 10:43:58  rurban
// seperate PassUser methods into seperate dir (memory usage)
// fix WikiUser (old) overlarge data session
// remove wikidb arg from various page class methods, use global ->_dbi instead
// ...
//

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>