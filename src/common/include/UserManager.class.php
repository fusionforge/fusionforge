<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('User.class.php');


class UserManager {


    protected function __construct() {
    }

    protected static $_instance;
    public static function instance() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }


    /**
     * @param $session_hash string Optional parameter. If given, this will force
     *                             the load of the user with the given session_hash.
     *                             else it will check from the user cookies & ip
     * @return User the user currently logged in (who made the request)
     */
    function getCurrentUser($session_hash = false) {
    	if (!session_get_user()) {
    		return new GFUser();
    	}
    	return session_get_user();
    }

    function getUserById($user_id) {
    	return user_get_object($user_id);
    }

    function getUserByEmail($user_id) {
	    return user_get_object_by_email($user_id);
    }
    function existEmail ($email) {
	    if (!validate_email($email)) {
		    return false;
	    }
	    $res = db_query_params('SELECT * FROM users WHERE email=$1', array($email));
	    if (!$res || db_numrows($res)<1) {
		    return false;
	    }
	    else {
		    return $email;
	    }
    }
}

?>
