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
 *
 * 
 */

/**
* Manager of system events
*
* Base class to manage system events
*/
class SystemEventManager {
    
   

    // Constructor
    function SystemEventManager() {
       
    }

    protected static $_instance;
    /**
     * SystemEventManager is singleton
     */
    public static function instance() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
 
    /**
     * Create a new event, store it in the db and send notifications
     */
    public function createEvent($type, $parameters, $priority) {
		return db_query_params('INSERT INTO system_event (type, parameters,create_date,log) VALUES ($1,$2,$3,$4)',
			array($type,
				$parameters,
				$_SERVER['REQUEST_TIME'],
				'NEW')
		);
        }
    
    
    
      
             
}

?>

