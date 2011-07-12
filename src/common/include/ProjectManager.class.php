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


/**
 * Provide access to projects
 */
class ProjectManager {

    /**


    /**
     * Hold an instance of the class
     */
    private static $_instance;

    /**
     * A private constructor; prevents direct creation of object
     */
    private function __construct() {

    }

    /**
     * ProjectManager is a singleton
     * @return ProjectManager
     */
    public static function instance() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }



    /**
     * @param $group_id int The id of the project to look for
     * @return Project
     */
    public function & getProject($group_id) {

        return  group_get_object($group_id);
    }


}
?>
