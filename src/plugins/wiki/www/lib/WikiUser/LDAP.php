<?php //-*-php-*-
rcs_id('$Id: LDAP.php 6184 2008-08-22 10:33:41Z vargenau $');
/* Copyright (C) 2004,2007 $ThePhpWikiProgrammingTeam
 * This file is part of PhpWiki. Terms and Conditions see LICENSE. (GPL2)
 */

class _LDAPPassUser
extends _PassUser
/**
 * Define the vars LDAP_AUTH_HOST and LDAP_BASE_DN in config/config.ini
 *
 * Preferences are handled in _PassUser
 */
{
    /**
     * ->_init()
     * connect and bind to the LDAP host
     */
    function _init() {
        if ($this->_ldap = ldap_connect(LDAP_AUTH_HOST)) { // must be a valid LDAP server!
            global $LDAP_SET_OPTION;
            if (!empty($LDAP_SET_OPTION)) {
                foreach ($LDAP_SET_OPTION as $key => $value) {
                    //if (is_string($key) and defined($key))
                    //    $key = constant($key);
                    ldap_set_option($this->_ldap, $key, $value);
                }
            }
            if (LDAP_AUTH_USER)
                if (LDAP_AUTH_PASSWORD)
                    // Windows Active Directory Server is strict
                    $r = ldap_bind($this->_ldap, LDAP_AUTH_USER, LDAP_AUTH_PASSWORD); 
                else
                    $r = ldap_bind($this->_ldap, LDAP_AUTH_USER); 
            else
                $r = true; // anonymous bind allowed
            if (!$r) {
                $this->_free();
                trigger_error(sprintf(_("Unable to bind LDAP server %s using %s %s"),
				      LDAP_AUTH_HOST, LDAP_AUTH_USER, LDAP_AUTH_PASSWORD), 
                              E_USER_WARNING);
                return false;
            }
            return $this->_ldap;
        } else {
            return false;
        }
    }

    /**    
     * free and close the bound ressources
     */
    function _free() {
        if (isset($this->_sr)   and is_resource($this->_sr))   ldap_free_result($this->_sr);
        if (isset($this->_ldap) and is_resource($this->_ldap)) ldap_close($this->_ldap);
        unset($this->_sr);
        unset($this->_ldap);
    }

    /**
     * LDAP names allow all chars but "*", "(", ")", "\", "NUL". 
     * " should be quoted as \"
     * Quoting is done by \xx (two-digit hexcode). "*" <=> "\2a"
     * Non-ascii chars must be converted to utf-8.
     * Password should NOT be escaped, just converted to utf-8.
     *
     * @see http://www.faqs.org/rfcs/rfc4514.html LDAP String Representation of Distinguished Names
     */
    function _stringEscape($name) {
	$name = strtr(utf8_encode($name), 
		      array("*" => "\\2a",
			    "?" => "\\3f",
			    "(" => "\\28",
			    ")" => "\\29",
			    "\\" => "\\5c",
			    '"'  => '\"',
			    "\0" => "\\00"));
	return $name;
    }

    /**
     * LDAP names may contain every utf-8 character. However we restrict them a bit for convenience.
     * @see _stringEscape()
     */
    function isValidName ($userid = false) {
        if (!$userid) $userid = $this->_userid;
	// We are more restrictive here, but must allow explitly utf-8
        return preg_match("/^[\-\w_\.@ ]+$/u", $userid) and strlen($userid) < 64;
    }

    /**
     * Construct the configured search filter and properly escape the userid.
     * Apply LDAP_SEARCH_FIELD and optionally LDAP_SEARCH_FILTER.
     *
     * @param string $userid username, unquoted in the current charset.
     * @access private
     * @return string The 3rd argument to ldap_search()
     * @see http://www.faqs.org/rfcs/rfc4514.html LDAP String Representation of Distinguished Names
     */
    function _searchparam($userid) {
	$euserid = $this->_stringEscape($userid);
	// Need to set the right root search information. See config/config.ini
	if (LDAP_SEARCH_FILTER) {
	    $st_search = str_replace("\$userid", $euserid, LDAP_SEARCH_FILTER);
	} else {
	    $st_search = LDAP_SEARCH_FIELD
		? LDAP_SEARCH_FIELD."=$euserid"
		: "uid=$euserid";
	}
	return $st_search;
    }

    /**
     * Passwords must not be escaped, but sent as "stringprep"'ed utf-8. 
     *
     * @see http://www.faqs.org/rfcs/rfc4514.html LDAP String Representation of Distinguished Names
     * @see http://www.faqs.org/rfcs/rfc3454.html stringprep
     */
    function checkPass($submitted_password) {

        $this->_authmethod = 'LDAP';
        $userid = $this->_userid;
        if (!$this->isValidName()) {
            trigger_error(_("Invalid username."), E_USER_WARNING);
            $this->_free();
            return $this->_tryNextPass($submitted_password);
        }
        if (!$this->_checkPassLength($submitted_password)) {
            $this->_free();
            return WIKIAUTH_FORBIDDEN;
        }
        // A LDAP speciality: Empty passwords are always true for ldap_bind !!! 
	// So we have to disallow this regardless of PASSWORD_LENGTH_MINIMUM = 0
        if (strlen($submitted_password) == 0) {
            trigger_error(_("Empty password not allowed for LDAP"), E_USER_WARNING);
            $this->_free();
	    return $this->_tryNextPass($submitted_password);
	    //return WIKIAUTH_FORBIDDEN;
        }
        /*if (strstr($userid,'*')) { // should be safely escaped now
            trigger_error(fmt("Invalid username '%s' for LDAP Auth", $userid), 
                          E_USER_WARNING);
            return WIKIAUTH_FORBIDDEN;
	}*/

        if ($ldap = $this->_init()) {
	    $st_search = $this->_searchparam($userid);
            if (!$this->_sr = ldap_search($ldap, LDAP_BASE_DN, $st_search)) {
		trigger_error(_("Could not search in LDAP"), E_USER_WARNING);
 		$this->_free();
                return $this->_tryNextPass($submitted_password);
            }
            $info = ldap_get_entries($ldap, $this->_sr); 
            if (empty($info["count"])) {
		if (DEBUG)
		    trigger_error(_("User not found in LDAP"), E_USER_WARNING);
            	$this->_free();
                return $this->_tryNextPass($submitted_password);
            }
            // There may be more hits with this userid.
            // Of course it would be better to narrow down the BASE_DN
            for ($i = 0; $i < $info["count"]; $i++) {
                $dn = $info[$i]["dn"];
                // The password must be converted to utf-8, but unescaped.
                // On wrong password the ldap server will return: 
                // "Unable to bind to server: Server is unwilling to perform"
                // The @ catches this error message.
		// If CHARSET=utf-8 the form should have already converted it to utf-8.
                if ($r = @ldap_bind($ldap, $dn, $submitted_password)) {
                    // ldap_bind will return TRUE if everything matches
		    // Optionally get the mail from LDAP
		    if (!empty($info[$i]["mail"][0])) {
			$this->_prefs->_prefs['email']->default_value = $info[$i]["mail"][0];
		    }
            	    $this->_free();
                    $this->_level = WIKIAUTH_USER;
                    return $this->_level;
                } else {
		    // Try again, this time explicitly
		    if ($r = @ldap_bind($ldap, $dn, utf8_encode($submitted_password))) {
			if (!empty($info[$i]["mail"][0])) {
			    $this->_prefs->_prefs['email']->default_value = $info[$i]["mail"][0];
			}
			$this->_free();
			$this->_level = WIKIAUTH_USER;
			return $this->_level;
		    }
		}
            }
	    if (DEBUG)
		trigger_error(_("Wrong password: ") . 
			      str_repeat("*", strlen($submitted_password)), 
			      E_USER_WARNING);
            $this->_free();
        } else {
            $this->_free();
	    trigger_error(fmt("Could not connect to LDAP host %s", LDAP_AUTH_HOST), E_USER_WARNING);
	}

        return $this->_tryNextPass($submitted_password);
    }


    function userExists() {
        $userid = $this->_userid;
        /*if (strstr($userid, '*')) {
            trigger_error(fmt("Invalid username '%s' for LDAP Auth", $userid),
                          E_USER_WARNING);
            return false;
	}*/
        if ($ldap = $this->_init()) {
            // Need to set the right root search information. see ../index.php
	    $st_search = $this->_searchparam($userid);
            if (!$this->_sr = ldap_search($ldap, LDAP_BASE_DN, $st_search)) {
 		$this->_free();
        	return $this->_tryNextUser();
            }
            $info = ldap_get_entries($ldap, $this->_sr); 

            if ($info["count"] > 0) {
         	$this->_free();
		UpgradeUser($GLOBALS['ForbiddenUser'], $this);
                return true;
            }
        }
 	$this->_free();
        return $this->_tryNextUser();
    }

    function mayChangePass() {
        return false;
    }

}

// $Log: not supported by cvs2svn $
// Revision 1.9  2007/06/13 12:48:14  rurban
// fix wrong fix from 1.3.13p1
//
// Revision 1.8  2007/06/07 16:31:33  rurban
// Important! Fixes bug #1732882 ldap_bind with empty password
// Adds diagnostics on other ldap failures
// Fix password quoting
//
// Revision 1.7  2007/05/30 21:56:17  rurban
// Back to default uid for LDAP
//
// Revision 1.6  2007/05/29 16:56:15  rurban
// Allow more password und userid chars. uid => cn: default for certain testusers
//
// Revision 1.5  2005/10/10 19:43:49  rurban
// add DBAUTH_PREF_INSERT: self-creating users. by John Stevens
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
