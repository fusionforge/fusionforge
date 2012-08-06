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

   /**
     * Table to display the status of the last n events
     *
     * @param int     $offset        the offset of the pagination
     * @param int     $limit         the number of event to includ in the table
     * @param boolean $full          display a full table or only a summary
     * @param array   $filter_status the filter on status
     * @param array   $filter_type   the filter on type
     *
     * @return array events
     */
    public function fetchEvents($offset = 0, $limit = 10, $full = false, $filter_status = false, $filter_type = false, $filter_params = false) {
	    $results = db_query_params('SELECT * FROM system_event WHERE type IN ($1) AND status IN($2) AND parameters=$3;',array($filter_type, $filter_status,$filter_params));
	    while($row = db_fetch_array($results))
	    {
		    $events[]=$row;
	    }
	    if (isset($events)) {
		    return $events;
	    }
	    else {
		    return null;
	    }

    }
}

?>
