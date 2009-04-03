<?php //-*-php-*-
rcs_id('$Id: LDAP.php,v 1.5 2005/10/10 19:43:49 rurban Exp $');
/* Copyright (C) 2007,2009 Reini Urban
 * This file is part of PhpWiki. Terms and Conditions see LICENSE. (GPL2)
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
        $userid = strtolower($this->_userid);
	return parent::checkPass($submitted_password);
    }

    function UserName() {
        if (!empty($this->_userid)) {
	    $this->_userid = strtoupper($this->_userid);
	    if (!empty($this->_HomePagehandle) and is_object($this->_HomePagehandle))
	        $this->_HomePagehandle->_pagename = $this->_userid;
            return strtoupper($this->_userid);
	}
    }

    function userExists() {
	// lowercase check and uppercase visibility 
        $userid = strtolower($this->_userid);
	$this->_userid = strtoupper($this->_userid);
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
